<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/ai_client.php';
require_admin();

$pdo = db();

// ---- helpers ----
$slugify = function (string $s): string {
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-');
};
$unique_course_slug = function (string $base) use ($pdo): string {
    if ($base === '') $base = 'course';
    $slug = $base; $i = 2;
    $q = $pdo->prepare('SELECT 1 FROM courses WHERE slug = ?');
    while (true) { $q->execute([$slug]); if (!$q->fetch()) return $slug; $slug = $base . '-' . $i++; }
};

// Validate + normalise an AI-generated outline into a safe structure.
function normalize_outline($d): ?array {
    if (!is_array($d) || empty($d['title']) || !is_array($d['modules'] ?? null)) return null;
    $allowed = ['doc', 'video', 'assessment', 'scorm', 'file'];
    $out = [
        'title'   => mb_substr(trim((string)$d['title']), 0, 120),
        'summary' => mb_substr(trim((string)($d['summary'] ?? '')), 0, 200),
        'icon'    => mb_substr(trim((string)($d['icon'] ?? '📚')), 0, 4) ?: '📚',
        'modules' => [],
    ];
    foreach (array_slice($d['modules'], 0, 12) as $m) {
        if (!is_array($m) || empty($m['title'])) continue;
        $mod = [
            'title'    => mb_substr(trim((string)$m['title']), 0, 140),
            'summary'  => mb_substr(trim((string)($m['summary'] ?? '')), 0, 200),
            'read_min' => max(1, min(60, (int)($m['read_min'] ?? 6))),
            'topics'   => [],
        ];
        foreach (array_slice($m['topics'] ?? [], 0, 10) as $t) {
            if (!is_array($t) || empty($t['title'])) continue;
            $type = strtolower(trim((string)($t['type'] ?? 'doc')));
            if (!in_array($type, $allowed, true)) $type = 'doc';
            $mod['topics'][] = ['title' => mb_substr(trim((string)$t['title']), 0, 140), 'type' => $type];
        }
        if ($mod['topics']) $out['modules'][] = $mod;
    }
    return $out['modules'] ? $out : null;
}

$COURSE_PROMPT = 'You are a curriculum designer for StorefrontZA, a South African learning platform that teaches people to build online stores and run their own businesses. Design a practical, honest course from the user\'s request. Respond with ONLY valid JSON — no prose, no markdown fences — in exactly this shape: {"title": string, "summary": string, "icon": one emoji, "modules": [ {"title": string, "summary": string, "read_min": integer, "topics": [ {"title": string, "type": "doc"|"video"|"assessment"|"scorm"|"file"} ] } ] }. Rules: 4 to 7 modules; 2 to 4 topics per module; most topics are "doc"; include at least one "assessment" and one "video" where it makes sense; use South African context (Rand, PayFast, Yoco, local couriers) when relevant; keep it practical with no hype.';

$proposal = null;   // AI-proposed course awaiting confirmation
$aiError  = '';

// ---- actions ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'new_course') {
        $title = trim($_POST['title'] ?? '');
        if ($title !== '') {
            $slug = $unique_course_slug($slugify($title));
            $ins = $pdo->prepare('INSERT INTO courses (slug,title,summary,icon,status,sort)
                VALUES (?,?,?,?,?, (SELECT COALESCE(MAX(sort),0)+1 FROM courses))');
            $ins->execute([$slug, $title, trim($_POST['summary'] ?? ''), '📚', 'draft']);
            header('Location: /admin-courses.php?created=course'); exit;
        }
        header('Location: /admin-courses.php?err=title'); exit;
    }

    if ($action === 'new_module') {
        $cid = (int)($_POST['course_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $chk = $pdo->prepare('SELECT id FROM courses WHERE id = ?'); $chk->execute([$cid]);
        if ($title !== '' && $chk->fetch()) {
            $slug = $slugify($title) ?: ('module-' . time());
            $ins = $pdo->prepare('INSERT INTO modules (course_id,slug,title,summary,read_min,sort)
                VALUES (?,?,?,?,0, (SELECT COALESCE(MAX(sort),0)+1 FROM modules WHERE course_id = ?))');
            $ins->execute([$cid, $slug, $title, trim($_POST['summary'] ?? ''), $cid]);
            header('Location: /admin-courses.php?created=module#course-' . $cid); exit;
        }
        header('Location: /admin-courses.php?err=module'); exit;
    }

    if ($action === 'ai_outline') {
        $brief = trim($_POST['brief'] ?? '');
        $aud   = trim($_POST['audience'] ?? '');
        if ($brief === '') { header('Location: /admin-courses.php?err=brief'); exit; }
        $userMsg = 'Course request: ' . mb_substr($brief, 0, 800) . ($aud !== '' ? "\nAudience: " . mb_substr($aud, 0, 200) : '');
        $r = ai_chat($COURSE_PROMPT, $userMsg, 1800, 0.7);
        if (isset($r['error'])) { $aiError = $r['error']; }
        else {
            $proposal = normalize_outline(ai_extract_json($r['text']));
            if (!$proposal) $aiError = 'The AI reply could not be read as a course. Try again, or rephrase your brief.';
        }
        // fall through to render the preview (no redirect)
    }

    if ($action === 'ai_save') {
        $p = normalize_outline(json_decode($_POST['outline'] ?? '', true));
        if ($p) {
            $slug = $unique_course_slug($slugify($p['title']));
            $ins = $pdo->prepare('INSERT INTO courses (slug,title,summary,icon,status,sort)
                VALUES (?,?,?,?,?, (SELECT COALESCE(MAX(sort),0)+1 FROM courses))');
            $ins->execute([$slug, $p['title'], $p['summary'], $p['icon'], 'draft']);
            $cid = (int)$pdo->lastInsertId();
            $mStmt = $pdo->prepare('INSERT INTO modules (course_id,slug,title,summary,read_min,sort) VALUES (?,?,?,?,?,?)');
            $tStmt = $pdo->prepare('INSERT INTO topics (module_id,title,type,body,sort) VALUES (?,?,?,\'\',?)');
            $ms = 0;
            foreach ($p['modules'] as $m) {
                $mStmt->execute([$cid, ($slug . '-m' . ($ms + 1)), $m['title'], $m['summary'], $m['read_min'], $ms++]);
                $mid = (int)$pdo->lastInsertId(); $ts = 0;
                foreach ($m['topics'] as $t) { $tStmt->execute([$mid, $t['title'], $t['type'], $ts++]); }
            }
            header('Location: /admin-courses.php?created=ai#course-' . $cid); exit;
        }
        header('Location: /admin-courses.php?err=save'); exit;
    }
}

// ---- load ----
$courses = $pdo->query(
    'SELECT c.*,
        (SELECT COUNT(*) FROM modules m WHERE m.course_id = c.id) AS module_count,
        (SELECT COUNT(*) FROM topics t JOIN modules m ON t.module_id = m.id WHERE m.course_id = c.id) AS topic_count
     FROM courses c ORDER BY c.sort, c.id'
)->fetchAll(PDO::FETCH_ASSOC);

$modulesByCourse = [];
foreach ($pdo->query(
    'SELECT m.*, (SELECT COUNT(*) FROM topics t WHERE t.module_id = m.id) AS topic_count
     FROM modules m ORDER BY m.course_id, m.sort, m.id'
)->fetchAll(PDO::FETCH_ASSOC) as $m) { $modulesByCourse[(int)$m['course_id']][] = $m; }

$flash = '';
if (($_GET['created'] ?? '') === 'course') $flash = 'New course created as a draft. Add modules to it below.';
if (($_GET['created'] ?? '') === 'module') $flash = 'Module added.';
if (($_GET['created'] ?? '') === 'ai')     $flash = 'AI course created as a draft — scroll down to find it, then refine.';
if (($_GET['err'] ?? '') === 'save')       $aiError = 'Could not save that course. Please generate it again.';

$type_emoji = ['doc' => '📖', 'video' => '🎬', 'assessment' => '✅', 'scorm' => '📦', 'file' => '📎'];

layout_header('Course builder', 'builder', true);
?>
<style>
.cb{display:flex;flex-direction:column;gap:24px}
.cb .flash{background:var(--good-bg);color:var(--good);border-radius:12px;padding:11px 15px;font-size:14px;font-weight:600}
.cb .aierr{background:#fdecec;color:#b3261e;border-radius:12px;padding:11px 15px;font-size:14px;font-weight:600}
.cb-card{background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);padding:20px 22px;box-shadow:var(--sh)}
.cb-card h2{font-size:17px;margin:0 0 4px;display:flex;align-items:center;gap:8px}
.cb-card p.sub{margin:0 0 14px;color:var(--ink-3);font-size:13.5px}
.ai-card{border:1px solid var(--violet-2);background:linear-gradient(180deg,var(--violet-bg),var(--surface))}
.ai-tag{font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#fff;background:var(--violet);padding:3px 9px;border-radius:20px}
.cb-row{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end}
.cb-field{flex:1;min-width:200px;display:flex;flex-direction:column;gap:5px}
.cb-field label{font-size:12.5px;font-weight:600;color:var(--ink-2)}
.cb-field input,.cb-field textarea{padding:10px 12px;border:1px solid var(--line-2);border-radius:10px;font:inherit;background:var(--surface);color:var(--ink);width:100%}
.cb-field textarea{min-height:70px;resize:vertical}
.btn-violet{background:var(--violet);color:#fff;border:none}
.btn-violet:hover{background:var(--violet-700)}
.propose{border:2px solid var(--violet);background:var(--surface);border-radius:var(--r-lg);padding:22px;box-shadow:var(--sh-lg)}
.propose .ph{display:flex;align-items:center;gap:12px;margin-bottom:4px}
.propose .ph .ic{width:46px;height:46px;border-radius:13px;background:var(--violet-bg);display:grid;place-items:center;font-size:23px;flex:none}
.propose .ph h3{font-size:19px;margin:0}
.propose .psum{color:var(--ink-2);font-size:14px;margin:2px 0 16px}
.pmod{border:1px solid var(--line);border-radius:12px;padding:13px 16px;margin-bottom:10px}
.pmod h4{font-size:15px;margin:0}
.pmod .pms{font-size:12.5px;color:var(--ink-3);margin:3px 0 9px}
.tpill{display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;background:var(--surface-2);border-radius:20px;padding:4px 10px;margin:0 6px 6px 0}
.course-card{background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);box-shadow:var(--sh);overflow:hidden}
.course-head{display:flex;align-items:center;gap:14px;padding:18px 22px;border-bottom:1px solid var(--line)}
.course-ic{width:46px;height:46px;border-radius:13px;background:var(--gold-bg);display:grid;place-items:center;font-size:23px;flex:none}
.course-head h3{font-size:18px;margin:0}
.course-head .cmeta{font-size:13px;color:var(--ink-3);margin-top:2px}
.badge{font-size:11px;font-weight:700;letter-spacing:.03em;padding:3px 10px;border-radius:20px;text-transform:uppercase}
.badge.published{background:var(--good-bg);color:var(--good)}
.badge.draft{background:var(--surface-2);color:var(--ink-3)}
.mod-list{list-style:none;margin:0;padding:8px 22px 4px}
.mod-list li{display:flex;align-items:center;gap:12px;padding:11px 0;border-bottom:1px solid var(--line)}
.mod-list li:last-child{border-bottom:0}
.mod-n{width:26px;height:26px;border-radius:8px;background:var(--surface-2);display:grid;place-items:center;font-size:12.5px;font-weight:700;color:var(--ink-2);flex:none}
.mod-t{font-weight:600;font-size:14.5px}
.mod-meta{margin-left:auto;font-size:12px;color:var(--ink-3);display:flex;gap:10px;align-items:center}
.chip-t{background:var(--violet-bg);color:var(--violet-700);border-radius:20px;padding:2px 9px;font-weight:600}
.add-mod{padding:14px 22px 20px;background:var(--surface-2);border-top:1px solid var(--line)}
.empty{padding:16px 22px;color:var(--ink-3);font-size:14px}
</style>

<header class="page-head">
  <p class="page-ey">Admin · Course builder</p>
  <h1>Courses</h1>
  <p class="page-sub"><?= count($courses) ?> course<?= count($courses) === 1 ? '' : 's' ?> · describe a course and let AI draft it, or build by hand</p>
</header>

<p style="margin:-6px 0 0"><a href="/admin-unlock.php" style="font-family:var(--disp);font-weight:700;font-size:12px;letter-spacing:.04em;text-transform:uppercase">Unlock a module for a specific user →</a></p>

<div class="cb">
  <?php if ($flash): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if ($aiError): ?><div class="aierr">⚠ <?= e($aiError) ?></div><?php endif; ?>

  <?php if ($proposal): ?>
  <div class="propose" id="proposal">
    <div class="ph"><div class="ic"><?= e($proposal['icon']) ?></div>
      <div><span class="ai-tag">AI draft</span><h3><?= e($proposal['title']) ?></h3></div>
    </div>
    <p class="psum"><?= e($proposal['summary']) ?></p>
    <?php foreach ($proposal['modules'] as $i => $m): ?>
      <div class="pmod">
        <h4><?= ($i + 1) . '. ' . e($m['title']) ?></h4>
        <div class="pms"><?= e($m['summary']) ?> · ≈<?= (int)$m['read_min'] ?> min</div>
        <?php foreach ($m['topics'] as $t): ?>
          <span class="tpill"><?= $type_emoji[$t['type']] ?? '📄' ?> <?= e($t['title']) ?> <span style="color:var(--ink-3)">· <?= e($t['type']) ?></span></span>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
    <div class="cb-row" style="margin-top:6px">
      <form method="post"><input type="hidden" name="action" value="ai_save">
        <input type="hidden" name="outline" value='<?= e(json_encode($proposal, JSON_UNESCAPED_UNICODE)) ?>'>
        <button class="btn btn-violet" type="submit">✨ Create this course</button>
      </form>
      <a class="btn btn-outline" href="/admin-courses.php">Discard</a>
    </div>
  </div>
  <?php endif; ?>

  <div class="cb-card ai-card">
    <h2><span class="ai-tag">AI</span> Build a course with AI</h2>
    <p class="sub">Describe what you want to teach. AI drafts the modules and topics; you review before anything is saved.</p>
    <?php if (ai_available()): ?>
    <form method="post">
      <input type="hidden" name="action" value="ai_outline">
      <div class="cb-field" style="margin-bottom:10px">
        <label>What should this course teach?</label>
        <textarea name="brief" placeholder="e.g. How to run profitable Facebook &amp; Instagram ads for a small South African online store — from setting up the pixel to reading the numbers." required></textarea>
      </div>
      <div class="cb-row">
        <div class="cb-field"><label>Who is it for? (optional)</label><input name="audience" placeholder="e.g. Complete beginners with a new store"></div>
        <button class="btn btn-violet" type="submit">✨ Generate course</button>
      </div>
    </form>
    <?php else: ?>
      <p class="sub" style="margin:0">AI is off until an <code>AI_API_KEY</code> is set on the server (Render → Environment). Once it's set, this drafts a full course for you. You can still build by hand below.</p>
    <?php endif; ?>
  </div>

  <div class="cb-card">
    <h2>New course by hand</h2>
    <p class="sub">Creates a draft. Drafts stay hidden from learners until you publish them.</p>
    <form method="post" class="cb-row">
      <input type="hidden" name="action" value="new_course">
      <div class="cb-field"><label>Course title</label><input name="title" placeholder="e.g. Start &amp; Run Your Business" required></div>
      <div class="cb-field"><label>Short summary (optional)</label><input name="summary" placeholder="One line learners will see on the card"></div>
      <button class="btn btn-primary" type="submit">Create course</button>
    </form>
  </div>

  <?php foreach ($courses as $c): $cid = (int)$c['id']; $mods = $modulesByCourse[$cid] ?? []; ?>
  <div class="course-card" id="course-<?= $cid ?>">
    <div class="course-head">
      <div class="course-ic"><?= e($c['icon'] ?: '📚') ?></div>
      <div style="flex:1">
        <h3><?= e($c['title']) ?></h3>
        <div class="cmeta"><?= (int)$c['module_count'] ?> modules · <?= (int)$c['topic_count'] ?> topics · <code><?= e($c['slug']) ?></code></div>
      </div>
      <span class="badge <?= $c['status'] === 'published' ? 'published' : 'draft' ?>"><?= e($c['status']) ?></span>
    </div>
    <?php if ($mods): ?>
    <ul class="mod-list">
      <?php foreach ($mods as $i => $m): ?>
      <li>
        <span class="mod-n"><?= $i + 1 ?></span>
        <span class="mod-t"><?= e($m['title']) ?></span>
        <span class="mod-meta">
          <span class="chip-t"><?= (int)$m['topic_count'] ?> topic<?= (int)$m['topic_count'] === 1 ? '' : 's' ?></span>
          <?php if ((int)$m['read_min']): ?><span>≈<?= (int)$m['read_min'] ?> min</span><?php endif; ?>
          <a href="/admin-quiz.php?module=<?= (int)$m['id'] ?>" style="font-family:var(--disp);font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:var(--ink);border:1px solid var(--line-2);padding:3px 9px;border-radius:4px;text-decoration:none">Quiz →</a>
        </span>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php else: ?>
      <div class="empty">No modules yet — add the first one below.</div>
    <?php endif; ?>
    <div class="add-mod">
      <form method="post" class="cb-row">
        <input type="hidden" name="action" value="new_module">
        <input type="hidden" name="course_id" value="<?= $cid ?>">
        <div class="cb-field"><label>Add a module</label><input name="title" placeholder="Module title" required></div>
        <button class="btn btn-outline" type="submit">Add module</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php layout_footer(); ?>

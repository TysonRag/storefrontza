<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
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

// ---- actions (Post/Redirect/Get) ----
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
}

// ---- load ----
$courses = $pdo->query(
    'SELECT c.*,
        (SELECT COUNT(*) FROM modules m WHERE m.course_id = c.id) AS module_count,
        (SELECT COUNT(*) FROM topics t JOIN modules m ON t.module_id = m.id WHERE m.course_id = c.id) AS topic_count
     FROM courses c ORDER BY c.sort, c.id'
)->fetchAll(PDO::FETCH_ASSOC);

$modulesByCourse = [];
$mrows = $pdo->query(
    'SELECT m.*, (SELECT COUNT(*) FROM topics t WHERE t.module_id = m.id) AS topic_count
     FROM modules m ORDER BY m.course_id, m.sort, m.id'
)->fetchAll(PDO::FETCH_ASSOC);
foreach ($mrows as $m) { $modulesByCourse[(int)$m['course_id']][] = $m; }

$flash = '';
if (($_GET['created'] ?? '') === 'course') $flash = 'New course created as a draft. Add modules to it below.';
if (($_GET['created'] ?? '') === 'module') $flash = 'Module added.';

layout_header('Course builder', 'admin', true);
?>
<style>
.cb{display:flex;flex-direction:column;gap:26px}
.cb .flash{background:var(--good-bg);color:var(--good);border-radius:12px;padding:11px 15px;font-size:14px;font-weight:600}
.cb-new{background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);padding:20px 22px;box-shadow:var(--sh)}
.cb-new h2{font-size:17px;margin:0 0 4px}
.cb-new p{margin:0 0 14px;color:var(--ink-3);font-size:13.5px}
.cb-row{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end}
.cb-field{flex:1;min-width:200px;display:flex;flex-direction:column;gap:5px}
.cb-field label{font-size:12.5px;font-weight:600;color:var(--ink-2)}
.cb-field input{padding:10px 12px;border:1px solid var(--line-2);border-radius:10px;font:inherit;background:var(--surface);color:var(--ink)}
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
.mod-meta{margin-left:auto;font-size:12px;color:var(--ink-3);display:flex;gap:10px}
.chip-t{background:var(--violet-bg);color:var(--violet-700);border-radius:20px;padding:2px 9px;font-weight:600}
.add-mod{padding:14px 22px 20px;background:var(--surface-2);border-top:1px solid var(--line)}
.empty{padding:16px 22px;color:var(--ink-3);font-size:14px}
</style>

<header class="page-head">
  <p class="page-ey">Admin · Course builder</p>
  <h1>Courses</h1>
  <p class="page-sub"><?= count($courses) ?> course<?= count($courses) === 1 ? '' : 's' ?> · this is where you set up what learners see</p>
</header>

<div class="cb">
  <?php if ($flash): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>

  <div class="cb-new">
    <h2>New course</h2>
    <p>Creates a draft. Drafts stay hidden from learners until you publish them.</p>
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

  <?php if (!$courses): ?><div class="empty">No courses yet. Create your first one above.</div><?php endif; ?>
</div>
<?php layout_footer(); ?>

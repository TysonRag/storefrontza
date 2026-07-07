<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/paths.php';
require_login();
$pdo = db();
$uid = current_user_id();
$isAdmin = is_admin();

$slug = $_GET['slug'] ?? 'online-store';
$course = path_course($pdo, $slug);
if (!$course) { layout_header('Path','course',false); echo '<div class="page-head"><h1>No course yet</h1><p class="page-sub">Your path is still being built.</p></div><a class="btn" href="/dashboard.php">Back</a>'; layout_footer(); exit; }

$modules = path_modules($pdo, (int)$course['id']);
$type_icon = ['doc'=>'📖','lesson'=>'📖','video'=>'🎬','assessment'=>'✅','quiz'=>'✅','scorm'=>'📦','file'=>'📎','interactive'=>'🧩'];

layout_header('Your path · ' . $course['title'], 'course', false);
?>
<style>
.pv{max-width:820px;margin:0 auto}
.pv-head{margin-bottom:8px}
.trk{position:relative;margin-top:10px;padding-left:6px}
.trk::before{content:"";position:absolute;left:23px;top:20px;bottom:20px;width:2px;background:var(--line)}
.mrow{position:relative;display:flex;gap:20px;padding:12px 0}
.mnode{width:40px;height:40px;flex:none;border-radius:50%;display:grid;place-items:center;font-family:var(--disp);font-weight:800;font-size:15px;z-index:1;border:2px solid var(--line-2);background:var(--surface);color:var(--ink-3)}
.mrow.open .mnode{border-color:var(--ink);color:var(--ink)}
.mrow.done .mnode{background:var(--ink);border-color:var(--ink);color:#fff}
.mcard{flex:1;background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);padding:16px 20px;box-shadow:var(--sh)}
.mrow.locked .mcard{background:var(--surface-2);box-shadow:none}
.mcard h3{font-size:18px;margin:0}
.mcard .msum{color:var(--ink-2);font-size:14px;margin:5px 0 0}
.mstate{font-family:var(--disp);font-weight:700;font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-3)}
.mstate.done{color:var(--ink)}
.tlist{list-style:none;margin:14px 0 0;padding:0;display:flex;flex-direction:column;gap:7px}
.tlist a,.tlist span{display:flex;align-items:center;gap:11px;padding:10px 13px;border:1px solid var(--line);border-radius:var(--r);font-size:14.5px;color:var(--ink);text-decoration:none;transition:border-color .12s}
.tlist a:hover{border-color:var(--ink)}
.tlist .tq{font-weight:600}
.tlist .pill{margin-left:auto;font-family:var(--disp);font-weight:700;font-size:10.5px;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-3)}
.tlist .pill.ok{color:var(--ink)}
.lockmsg{margin-top:8px;font-size:13.5px;color:var(--ink-3);display:flex;align-items:center;gap:8px}
</style>

<div class="pv">
  <header class="page-head pv-head">
    <p class="page-ey"><?= $isAdmin ? 'Admin preview · ' : '' ?>Your path</p>
    <h1><?= e($course['title']) ?></h1>
    <p class="page-sub"><?= e($course['summary']) ?></p>
  </header>

  <div class="trk">
    <?php foreach ($modules as $idx => $m):
      $complete = module_complete($pdo, $uid, (int)$m['id']);
      $unlocked = module_unlocked($pdo, $uid, $modules, $idx, $isAdmin);
      $topics = path_topics($pdo, (int)$m['id']);
      $state = $complete ? 'done' : ($unlocked ? 'open' : 'locked');
    ?>
    <div class="mrow <?= $state ?>">
      <div class="mnode"><?= $complete ? '✓' : ($idx + 1) ?></div>
      <div class="mcard">
        <div style="display:flex;justify-content:space-between;gap:12px;align-items:baseline">
          <h3><?= e($m['title']) ?></h3>
          <span class="mstate <?= $complete?'done':'' ?>"><?= $complete ? 'Complete' : ($unlocked ? 'Open' : '🔒 Locked') ?></span>
        </div>
        <?php if ($m['summary']): ?><p class="msum"><?= e($m['summary']) ?></p><?php endif; ?>

        <?php if ($unlocked): ?>
          <ul class="tlist">
            <?php foreach ($topics as $t):
              $icon = $type_icon[$t['type']] ?? '•';
              if ($t['type'] === 'quiz'):
                $passed = quiz_passed($pdo, $uid, (int)$t['id']); ?>
                <li><a class="tq" href="/quiz.php?topic=<?= (int)$t['id'] ?>"><?= $icon ?> Module quiz <span class="pill <?= $passed?'ok':'' ?>"><?= $passed ? '✓ passed' : 'take →' ?></span></a></li>
              <?php else: ?>
                <li><a href="/learn.php?topic=<?= (int)$t['id'] ?>"><?= $icon ?> <?= e($t['title']) ?></a></li>
              <?php endif; endforeach; ?>
            <?php if (!$topics): ?><li><span>Content coming soon</span></li><?php endif; ?>
          </ul>
        <?php else: ?>
          <div class="lockmsg">🔒 Pass Module <?= $idx ?>'s quiz to unlock this module.</div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$modules): ?><p style="color:var(--ink-3)">This course has no modules yet.</p><?php endif; ?>
  </div>

  <p style="margin-top:26px"><a class="btn btn-ghost" href="/dashboard.php">← Back to dashboard</a></p>
</div>
<?php layout_footer(); ?>

<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_login();

$uid = current_user_id();
$modules = course_modules();
$key = $_GET['m'] ?? '';
if (!isset($modules[$key])) { header('Location: /dashboard.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'complete') {
        $already = in_array($key, get_progress($uid), true);
        $newBadges = mark_activity($uid, $key);
        if (!$already) { $_SESSION['flash_xp'] = XP_MODULE; $_SESSION['flash_badges'] = $newBadges; }
        [, $next] = module_neighbours($key);
        header('Location: ' . ($next ? '/module.php?m=' . urlencode($next) : '/dashboard.php'));
        exit;
    }
    if ($action === 'incomplete') { unmark_activity($uid, $key); header('Location: /module.php?m=' . urlencode($key)); exit; }
}

$m = $modules[$key];
$doneSet = array_flip(get_progress($uid));
$isDone = isset($doneSet[$key]);
[$prev, $next] = module_neighbours($key);
$tools = course_tools();

// celebration flash (arriving from a just-completed previous module)
$flashBadges = $_SESSION['flash_badges'] ?? [];
$flashXp = $_SESSION['flash_xp'] ?? 0;
unset($_SESSION['flash_badges'], $_SESSION['flash_xp']);
$badgeDefs = badges();

layout_header('Module ' . $m['num'] . ' · ' . $m['title'], 'course');
?>
<article class="lesson">
  <nav class="crumb"><a href="/dashboard.php">Course</a> <span>/</span> Module <?= $m['num'] ?> of <?= count($modules) ?></nav>

  <header class="lesson-head">
    <p class="l-eyebrow">Module <?= $m['num'] ?> · <?= (int)$m['read_min'] ?> min · <?= XP_MODULE ?> XP</p>
    <h1><?= e($m['title']) ?></h1>
    <?php if ($isDone): ?><span class="done-pill">✓ Completed</span><?php endif; ?>
  </header>

  <div class="lesson-body">
    <?php render_blocks($m['blocks']); ?>
    <?php if (!empty($m['tools'])): ?>
      <aside class="l-tools">
        <span class="l-tools-lbl">Use these here</span>
        <div class="l-tools-row">
          <?php foreach ($m['tools'] as $tk): if (!isset($tools[$tk])) continue; ?>
            <a href="/tools.php#<?= e($tk) ?>" class="l-tool"><?= e($tools[$tk]['title']) ?> →</a>
          <?php endforeach; ?>
        </div>
      </aside>
    <?php endif; ?>
  </div>

  <footer class="lesson-nav">
    <div><?php if ($prev): ?><a href="/module.php?m=<?= e($prev) ?>" class="btn btn-outline">← Module <?= $modules[$prev]['num'] ?></a><?php endif; ?></div>
    <div class="ln-right">
      <?php if ($isDone): ?>
        <form method="post" class="inline"><input type="hidden" name="action" value="incomplete"><button class="btn btn-ghost sm">Mark not done</button></form>
        <?php if ($next): ?><a href="/module.php?m=<?= e($next) ?>" class="btn btn-primary">Next module →</a>
        <?php else: ?><a href="/dashboard.php" class="btn btn-primary">Back to course →</a><?php endif; ?>
      <?php else: ?>
        <form method="post" class="inline"><input type="hidden" name="action" value="complete">
          <button class="btn btn-primary btn-complete"><?= $next ? 'Complete &amp; continue →' : 'Complete course →' ?></button></form>
      <?php endif; ?>
    </div>
  </footer>
</article>

<?php if ($flashBadges || $flashXp): ?>
<script>window.__celebrate = { xp: <?= (int)$flashXp ?>, badges: <?= json_encode(array_map(fn($b) => $badgeDefs[$b] ?? null, $flashBadges)) ?> };</script>
<?php endif; ?>
<?php layout_footer(); ?>

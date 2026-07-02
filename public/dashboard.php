<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_login();

$uid = current_user_id();
$st = user_stats($uid);
$doneSet = array_flip($st['progress']);
$modules = course_modules();
$total = count($modules);
$completed = 0;
foreach ($modules as $k => $m) if (isset($doneSet[$k])) $completed++;

$nextKey = null;
foreach ($modules as $k => $m) if (!isset($doneSet[$k])) { $nextKey = $k; break; }

$badgeDefs = badges();
$earned = user_badges($uid);

// celebration flash from a just-completed module
$newBadges = $_SESSION['flash_badges'] ?? [];
$gainedXp = $_SESSION['flash_xp'] ?? 0;
unset($_SESSION['flash_badges'], $_SESSION['flash_xp']);

layout_header('Your course', 'course', true);
?>
<section class="dash-hero">
  <div class="dh-left">
    <div class="level-medallion">
      <span class="lm-num"><?= $st['level']['index'] ?></span>
      <span class="lm-lbl">LEVEL</span>
    </div>
    <div class="dh-meta">
      <p class="dh-title-lbl"><?= e($st['level']['title']) ?></p>
      <h1>Welcome back</h1>
      <div class="xp-bar-wrap">
        <div class="xp-bar"><div class="xp-fill" style="width:<?= $st['level']['pct'] ?>%"></div></div>
        <p class="xp-text"><strong><?= $st['xp'] ?> XP</strong><?php if ($st['level']['next_title']): ?> · <?= $st['level']['xp_to_next'] ?> XP to <?= e($st['level']['next_title']) ?><?php else: ?> · max level<?php endif; ?></p>
      </div>
    </div>
  </div>
  <div class="dh-stats">
    <div class="stat"><span class="stat-num">🔥 <?= $st['streak'] ?></span><span class="stat-lbl">day streak</span></div>
    <div class="stat"><span class="stat-num"><?= $completed ?>/<?= $total ?></span><span class="stat-lbl">modules</span></div>
    <div class="stat"><span class="stat-num"><?= count($earned) ?>/<?= count($badgeDefs) ?></span><span class="stat-lbl">badges</span></div>
  </div>
</section>

<?php if ($nextKey): $nm = $modules[$nextKey]; ?>
<a class="continue-card" href="/module.php?m=<?= e($nextKey) ?>">
  <div class="cc-body">
    <p class="cc-lbl"><?= $completed === 0 ? 'Start here' : 'Continue where you left off' ?></p>
    <h2>Module <?= $nm['num'] ?> — <?= e($nm['title']) ?></h2>
    <p class="cc-sum"><?= e($nm['summary']) ?></p>
  </div>
  <div class="cc-go"><span><?= $completed === 0 ? 'Begin' : 'Resume' ?></span><span class="cc-arrow">→</span></div>
</a>
<?php else: ?>
<div class="continue-card complete">
  <div class="cc-body"><p class="cc-lbl">🎓 Course complete</p><h2>You've finished every module</h2>
    <p class="cc-sum">The process is yours now — run it again on your next product. Revisit any module or tool anytime.</p></div>
</div>
<?php endif; ?>

<div class="dash-grid">
  <section class="track">
    <div class="section-head"><h3>Curriculum</h3><span class="section-sub">9 modules · earn <?= XP_MODULE ?> XP each</span></div>
    <div class="track-list">
      <?php foreach ($modules as $k => $m):
        $isDone = isset($doneSet[$k]); $isNext = ($k === $nextKey);
        $state = $isDone ? 'done' : ($isNext ? 'next' : 'todo'); ?>
      <a class="tcard <?= $state ?>" href="/module.php?m=<?= e($k) ?>">
        <div class="tc-node"><?= $isDone ? '✓' : $m['num'] ?></div>
        <div class="tc-body">
          <div class="tc-top"><h4><?= e($m['title']) ?></h4>
            <span class="tc-meta"><?= (int)$m['read_min'] ?> min<?php if ($isNext): ?> · <b>up next</b><?php elseif ($isDone): ?> · <b>+<?= XP_MODULE ?> XP</b><?php endif; ?></span></div>
          <p><?= e($m['summary']) ?></p>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </section>

  <aside class="side">
    <div class="side-card ai-card">
      <span class="side-tag ai">AI</span>
      <h3>AI Studio</h3>
      <p>Generate product ideas, descriptions, ad hooks and store names with AI built into the course.</p>
      <a class="btn btn-violet btn-block" href="/ai-studio.php">Open AI Studio</a>
    </div>
    <div class="side-card">
      <h3>Tools</h3>
      <p>Profit calculator, product scorecard, ad budget planner and store readiness checker.</p>
      <a class="btn btn-outline btn-block" href="/tools.php">Open tools</a>
    </div>
    <div class="side-card">
      <div class="section-head sm"><h3>Badges</h3><a class="see-all" href="#badges">all</a></div>
      <div class="badge-row" id="badges">
        <?php foreach ($badgeDefs as $bk => $bd): $has = in_array($bk, $earned, true); ?>
          <div class="badge <?= $has ? 'earned' : 'locked' ?>" title="<?= e($bd['name']) ?> — <?= e($bd['desc']) ?>">
            <span class="badge-ic"><?= $bd['icon'] ?></span>
            <span class="badge-nm"><?= e($bd['name']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </aside>
</div>

<?php if ($newBadges || $gainedXp): ?>
<script>
window.__celebrate = {
  xp: <?= (int)$gainedXp ?>,
  badges: <?= json_encode(array_map(fn($b) => $badgeDefs[$b] ?? null, $newBadges)) ?>
};
</script>
<?php endif; ?>
<?php layout_footer(); ?>

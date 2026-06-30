<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$userId = current_user_id();
$allSteps = require __DIR__ . '/../includes/modules.php';
$completed = get_progress($userId);

// Handle toggling a step's completion state.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle'])) {
    $key = $_POST['toggle'];
    $validKeys = array_column($allSteps, 'key');
    if (in_array($key, $validKeys, true)) {
        if (in_array($key, $completed, true)) {
            mark_incomplete($userId, $key);
        } else {
            mark_complete($userId, $key);
        }
    }
    header('Location: /dashboard.php');
    exit;
}

$totalSteps = count($allSteps);
$doneCount = count($completed);
$pct = $totalSteps > 0 ? round(($doneCount / $totalSteps) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your Progress — StorefrontZA</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
  <div class="topbar">
    <div class="brand">Storefront<span>ZA</span></div>
    <div><a href="/logout.php">Log out</a></div>
  </div>

  <div class="wrap wide">
    <div class="progress-wrap">
      <div class="progress-label">
        <span>Your progress</span>
        <span><?= $doneCount ?> of <?= $totalSteps ?> steps — <?= $pct ?>%</span>
      </div>
      <div class="progress-bar-bg">
        <div class="progress-bar-fill" style="width: <?= $pct ?>%;"></div>
      </div>
    </div>

    <div class="card">
      <h1>Your Step-by-Step Plan</h1>
      <p class="sub">Tick off each step as you complete it. Your progress saves automatically and is here whenever you come back.</p>

      <ul class="step-list">
        <?php foreach ($allSteps as $step): ?>
          <?php $isDone = in_array($step['key'], $completed, true); ?>
          <li class="step <?= $isDone ? 'done' : '' ?>">
            <form method="post" style="margin:0;">
              <input type="hidden" name="toggle" value="<?= htmlspecialchars($step['key']) ?>">
              <button type="submit" class="check" style="all:unset; cursor:pointer;">
                <span class="check"><?= $isDone ? '✓' : '' ?></span>
              </button>
            </form>
            <span class="title"><?= htmlspecialchars($step['title']) ?></span>
            <?php if ($step['type'] === 'tool'): ?>
              <span class="badge">Tool</span>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</body>
</html>

<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$allSteps = require __DIR__ . '/../includes/modules.php';
$totalSteps = count($allSteps);

$pdo = db();
$users = $pdo->query('SELECT id, email, created_at FROM users ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);

$progressStmt = $pdo->prepare('SELECT COUNT(*) FROM progress WHERE user_id = ?');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — StorefrontZA</title>
<link rel="stylesheet" href="/assets/css/style.css">
<style>
  table { width: 100%; border-collapse: collapse; font-family: -apple-system, sans-serif; font-size: 14px; }
  th, td { text-align: left; padding: 10px 12px; border-bottom: 1px solid #EFEBE0; }
  th { color: var(--navy); font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; }
  .stat { display: inline-block; background: rgba(200,150,44,0.1); color: var(--gold); padding: 3px 10px; border-radius: 12px; font-weight: 600; font-size: 12px; }
</style>
</head>
<body>
  <div class="topbar">
    <div class="brand">Storefront<span>ZA</span> <span style="font-size:12px; color:var(--gold-soft); margin-left:8px;">ADMIN</span></div>
    <div><a href="/dashboard.php">My Dashboard</a> <a href="/logout.php">Log out</a></div>
  </div>

  <div class="wrap wide">
    <div class="card">
      <h1>Registered Users</h1>
      <p class="sub"><?= count($users) ?> total — sorted newest first</p>

      <table>
        <thead>
          <tr><th>Email</th><th>Joined</th><th>Progress</th></tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <?php
              $progressStmt->execute([$u['id']]);
              $done = (int)$progressStmt->fetchColumn();
              $pct = $totalSteps > 0 ? round(($done / $totalSteps) * 100) : 0;
            ?>
            <tr>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td><?= htmlspecialchars($u['created_at']) ?></td>
              <td><span class="stat"><?= $done ?>/<?= $totalSteps ?> — <?= $pct ?>%</span></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($users)): ?>
            <tr><td colspan="3">No users registered yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>

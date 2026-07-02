<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_admin();

$pdo = db();
$total = count(course_modules());
$users = $pdo->query('SELECT id, email, streak_current, streak_longest, created_at FROM users ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);

// progress + xp per user
$mods = []; $toolsCount = [];
foreach ($pdo->query('SELECT user_id, item_key FROM progress') as $row) {
    $uid = (int)$row['user_id'];
    if (str_starts_with($row['item_key'], 'tool_')) $toolsCount[$uid] = ($toolsCount[$uid] ?? 0) + 1;
    else $mods[$uid] = ($mods[$uid] ?? 0) + 1;
}
layout_header('Admin', 'admin', true);
?>
<header class="page-head">
  <p class="page-ey">Admin</p>
  <h1>Members</h1>
  <p class="page-sub"><?= count($users) ?> account<?= count($users) === 1 ? '' : 's' ?> · <?= $total ?> modules · <?= XP_MODULE ?> XP/module, <?= XP_TOOL ?> XP/tool</p>
</header>

<div class="admin-table-wrap">
<table class="admin-table">
  <thead><tr><th>Email</th><th>Modules</th><th>XP</th><th>Streak</th><th>Joined</th></tr></thead>
  <tbody>
    <?php foreach ($users as $u):
      $uid = (int)$u['id']; $m = $mods[$uid] ?? 0; $t = $toolsCount[$uid] ?? 0;
      $xp = $m * XP_MODULE + $t * XP_TOOL; $pct = $total ? round($m/$total*100) : 0; ?>
    <tr>
      <td><?= e($u['email']) ?></td>
      <td><div class="abar"><div style="width:<?= $pct ?>%"></div></div><span class="amuted"><?= $m ?>/<?= $total ?></span></td>
      <td><?= $xp ?></td>
      <td>🔥 <?= (int)$u['streak_current'] ?> <span class="amuted">(best <?= (int)$u['streak_longest'] ?>)</span></td>
      <td class="amuted"><?= e($u['created_at']) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$users): ?><tr><td colspan="5" class="amuted">No accounts yet.</td></tr><?php endif; ?>
  </tbody>
</table>
</div>
<?php layout_footer(); ?>

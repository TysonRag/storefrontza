<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_admin();
$pdo = db();

$flash = ''; $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'unlock') {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $moduleId = (int)($_POST['module_id'] ?? 0);
        $u = $pdo->prepare('SELECT id FROM users WHERE email = ?'); $u->execute([$email]); $u = $u->fetch();
        $mchk = $pdo->prepare('SELECT 1 FROM modules WHERE id = ?'); $mchk->execute([$moduleId]);
        if (!$u) { header('Location: /admin-unlock.php?err=nouser'); exit; }
        if (!$mchk->fetch()) { header('Location: /admin-unlock.php?err=nomod'); exit; }
        $pdo->prepare('INSERT OR IGNORE INTO unlocks (user_id,module_id,granted_by) VALUES (?,?,?)')
            ->execute([(int)$u['id'], $moduleId, current_user_id()]);
        header('Location: /admin-unlock.php?ok=1'); exit;
    }
    if ($action === 'revoke') {
        $pdo->prepare('DELETE FROM unlocks WHERE user_id = ? AND module_id = ?')
            ->execute([(int)($_POST['user_id'] ?? 0), (int)($_POST['module_id'] ?? 0)]);
        header('Location: /admin-unlock.php?ok=2'); exit;
    }
}
if (($_GET['ok'] ?? '') === '1') $flash = 'Module unlocked for that user.';
if (($_GET['ok'] ?? '') === '2') $flash = 'Unlock removed.';
if (($_GET['err'] ?? '') === 'nouser') $err = "No user found with that email.";
if (($_GET['err'] ?? '') === 'nomod') $err = 'That module no longer exists.';

$mods = $pdo->query('SELECT m.id, m.title, m.sort, c.title AS course_title FROM modules m JOIN courses c ON c.id = m.course_id ORDER BY c.sort, m.sort, m.id')->fetchAll(PDO::FETCH_ASSOC);
$unlocks = $pdo->query('SELECT u.user_id, u.module_id, us.email, m.title AS module_title, c.title AS course_title
                        FROM unlocks u JOIN users us ON us.id = u.user_id JOIN modules m ON m.id = u.module_id JOIN courses c ON c.id = m.course_id
                        ORDER BY u.created_at DESC')->fetchAll(PDO::FETCH_ASSOC);

layout_header('Unlock modules', 'builder', false);
?>
<style>
.ul-card{background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);padding:20px 22px;box-shadow:var(--sh);max-width:620px;margin-bottom:22px}
.ul-card h2{font-size:16px;margin:0 0 4px}
.ul-card p.sub{color:var(--ink-3);font-size:13.5px;margin:0 0 14px}
.ul-row{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end}
.fld{display:flex;flex-direction:column;gap:5px;flex:1;min-width:200px}
.fld label{font-size:12.5px;font-weight:600;color:var(--ink-2)}
.fld input,.fld select{padding:10px 12px;border:1px solid var(--line-2);border-radius:var(--r);font:inherit;width:100%}
.flash{background:var(--good-bg);color:var(--ink);border-radius:var(--r);padding:10px 14px;font-size:14px;font-weight:600;max-width:620px;margin-bottom:16px}
.aierr{background:var(--ink);color:#fff;border-radius:var(--r);padding:10px 14px;font-size:14px;font-weight:600;max-width:620px;margin-bottom:16px}
.ul-list{list-style:none;margin:0;padding:0;max-width:620px}
.ul-list li{display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--line);font-size:14.5px}
.ul-list li:last-child{border-bottom:0}
.ul-list .who{font-weight:600}
</style>

<header class="page-head">
  <p class="page-ey">Admin · Overrides</p>
  <h1>Unlock modules</h1>
  <p class="page-sub">Manually open a locked module for a specific learner (enterprise clients, special cases).</p>
</header>

<?php if ($flash): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
<?php if ($err): ?><div class="aierr">⚠ <?= e($err) ?></div><?php endif; ?>

<div class="ul-card">
  <h2>Grant an unlock</h2>
  <p class="sub">The learner will be able to open this module even if they haven't passed the previous quiz.</p>
  <form method="post" class="ul-row">
    <input type="hidden" name="action" value="unlock">
    <div class="fld"><label>Learner email</label><input name="email" type="email" placeholder="learner@example.com" required></div>
    <div class="fld"><label>Module</label>
      <select name="module_id" required>
        <?php foreach ($mods as $m): ?><option value="<?= (int)$m['id'] ?>"><?= e($m['course_title']) ?> — <?= e($m['title']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <button class="btn btn-primary" type="submit">Unlock</button>
  </form>
</div>

<h2 style="font-size:16px;margin:0 0 10px">Current manual unlocks</h2>
<ul class="ul-list">
  <?php foreach ($unlocks as $u): ?>
    <li>
      <span class="who"><?= e($u['email']) ?></span>
      <span style="color:var(--ink-3)"><?= e($u['course_title']) ?> — <?= e($u['module_title']) ?></span>
      <form method="post" style="margin-left:auto">
        <input type="hidden" name="action" value="revoke">
        <input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>">
        <input type="hidden" name="module_id" value="<?= (int)$u['module_id'] ?>">
        <button class="btn btn-ghost" style="padding:6px 12px" type="submit">Remove</button>
      </form>
    </li>
  <?php endforeach; ?>
  <?php if (!$unlocks): ?><li style="color:var(--ink-3)">No manual unlocks yet.</li><?php endif; ?>
</ul>
<p style="margin-top:20px"><a class="btn btn-ghost" href="/admin-courses.php">← Back to builder</a></p>
<?php layout_footer(); ?>

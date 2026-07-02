<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
if (current_user_id()) { header('Location: /dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $result] = attempt_login($_POST['email'] ?? '', $_POST['password'] ?? '');
    if ($ok) { login_session($result['id'], $result['email']); header('Location: /dashboard.php'); exit; }
    $error = $result;
}
layout_header('Log in');
?>
<div class="auth">
  <div class="auth-card">
    <p class="auth-ey">Welcome back</p>
    <h1>Log in</h1>
    <p class="auth-sub">Pick up right where you left off.</p>
    <?php if ($error): ?><div class="flash err"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="form">
      <label>Email address<input type="email" name="email" required autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>"></label>
      <label class="lbl-row">Password <a class="lbl-link" href="/forgot-password.php">Forgot?</a>
        <input type="password" name="password" required autocomplete="current-password"></label>
      <button class="btn btn-primary btn-block">Log in</button>
    </form>
    <p class="auth-alt">New here? <a href="/register.php">Create a free account</a></p>
  </div>
</div>
<?php layout_footer(); ?>

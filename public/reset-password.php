<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
if (current_user_id()) { header('Location: /dashboard.php'); exit; }

$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$error = ''; $success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pw = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if ($pw !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        [$ok, $result] = consume_reset_token($token, $pw);
        if ($ok) { $success = true; } else { $error = $result; }
    }
}
layout_header('Set a new password');
?>
<div class="auth">
  <div class="auth-card">
    <p class="auth-ey">Account recovery</p>
    <h1>Set a new password</h1>
    <?php if ($success): ?>
      <div class="flash ok">Your password has been updated. You can log in now.</div>
      <a class="btn btn-primary btn-block" href="/login.php">Go to log in</a>
    <?php elseif (!$token): ?>
      <div class="flash err">This reset link is missing its token. Request a new one.</div>
      <a class="btn btn-primary btn-block" href="/forgot-password.php">Request a reset link</a>
    <?php else: ?>
      <?php if ($error): ?><div class="flash err"><?= e($error) ?></div><?php endif; ?>
      <form method="post" class="form">
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <label>New password<input type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="At least 8 characters"></label>
        <label>Confirm new password<input type="password" name="confirm" required minlength="8" autocomplete="new-password"></label>
        <button class="btn btn-primary btn-block">Update password</button>
      </form>
    <?php endif; ?>
  </div>
</div>
<?php layout_footer(); ?>

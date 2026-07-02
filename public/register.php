<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
if (current_user_id()) { header('Location: /dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        [$ok, $result] = register_user($email, $password);
        if ($ok) { login_session($result['id'], $result['email']); header('Location: /dashboard.php'); exit; }
        $error = $result;
    }
}
layout_header('Create your account');
?>
<div class="auth">
  <div class="auth-card">
    <p class="auth-ey">Free forever · no card</p>
    <h1>Create your account</h1>
    <p class="auth-sub">Start the course, earn XP, and track your progress.</p>
    <?php if ($error): ?><div class="flash err"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="form">
      <label>Email address<input type="email" name="email" required autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>"></label>
      <label>Password<input type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="At least 8 characters"></label>
      <label>Confirm password<input type="password" name="confirm" required minlength="8" autocomplete="new-password"></label>
      <button class="btn btn-primary btn-block">Create account</button>
    </form>
    <p class="auth-alt">Already have an account? <a href="/login.php">Log in</a></p>
  </div>
</div>
<?php layout_footer(); ?>

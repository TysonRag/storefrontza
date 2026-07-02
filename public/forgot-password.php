<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mailer.php';
require_once __DIR__ . '/../includes/layout.php';
if (current_user_id()) { header('Location: /dashboard.php'); exit; }

$done = false; $displayLink = null; $note = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $res = create_reset_token($email); // null if no such user (don't reveal)
    if ($res) {
        $link = base_url() . '/reset-password.php?token=' . $res['token'];
        $out = deliver_reset_link($email, $link);
        if ($out['mode'] === 'display') { $displayLink = $out['link']; }
        elseif ($out['mode'] === 'error') { $note = 'We could not send the email just now. Please try again later.'; }
    }
    $done = true;
}
layout_header('Forgot password');
?>
<div class="auth">
  <div class="auth-card">
    <p class="auth-ey">Account recovery</p>
    <h1>Reset your password</h1>
    <?php if (!$done): ?>
      <p class="auth-sub">Enter your email and we'll send you a link to set a new password.</p>
      <form method="post" class="form">
        <label>Email address<input type="email" name="email" required autocomplete="email"></label>
        <button class="btn btn-primary btn-block">Send reset link</button>
      </form>
      <p class="auth-alt"><a href="/login.php">Back to log in</a></p>
    <?php else: ?>
      <div class="flash ok">If an account exists for that email, a reset link has been created. It's valid for 1 hour.</div>
      <?php if ($note): ?><div class="flash err"><?= e($note) ?></div><?php endif; ?>
      <?php if ($displayLink): ?>
        <div class="reset-display">
          <p><strong>Email isn't configured yet</strong>, so here is your reset link (visible because the site is in setup mode):</p>
          <a class="reset-link" href="<?= e($displayLink) ?>"><?= e($displayLink) ?></a>
        </div>
      <?php endif; ?>
      <p class="auth-alt"><a href="/login.php">Back to log in</a></p>
    <?php endif; ?>
  </div>
</div>
<?php layout_footer(); ?>

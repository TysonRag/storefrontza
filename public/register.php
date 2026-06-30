<?php
require_once __DIR__ . '/../includes/auth.php';

if (current_user_id()) {
    header('Location: /dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        [$ok, $result] = register_user($email, $password);
        if ($ok) {
            login_session($result['id'], $result['email']);
            header('Location: /dashboard.php');
            exit;
        } else {
            $error = $result;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create your account — StorefrontZA</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
  <div class="topbar">
    <div class="brand">Storefront<span>ZA</span></div>
    <div><a href="/login.php">Log in</a></div>
  </div>

  <div class="wrap">
    <div class="card">
      <h1>Create your free account</h1>
      <p class="sub">Start the course, track your progress, pick up where you left off.</p>

      <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post">
        <label for="email">Email address</label>
        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="8">

        <label for="confirm">Confirm password</label>
        <input type="password" id="confirm" name="confirm" required minlength="8">

        <button type="submit">Create account</button>
      </form>

      <p class="muted-link">Already have an account? <a href="/login.php">Log in</a></p>
    </div>
  </div>
</body>
</html>

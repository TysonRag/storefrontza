—<?php
require_once __DIR__ . '/../includes/auth.php';

if (current_user_id()) {
    header('Location: /dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    [$ok, $result] = attempt_login($email, $password);
    if ($ok) {
        $_SESSION['user_id'] = $result;
        header('Location: /dashboard.php');
        exit;
    } else {
        $error = $result;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Log in — StorefrontZA</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
  <div class="topbar">
    <div class="brand">Storefront<span>ZA</span></div>
    <div><a href="/register.php">Create account</a></div>
  </div>

  <div class="wrap">
    <div class="card">
      <h1>Welcome back</h1>
      <p class="sub">Log in to continue where you left off.</p>

      <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post">
        <label for="email">Email address</label>
        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Log in</button>
      </form>

      <p class="muted-link">Don't have an account? <a href="/register.php">Create one free</a></p>
    </div>
  </div>
</body>
</html>

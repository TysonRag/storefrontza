<?php
require_once __DIR__ . '/../includes/auth.php';
if (current_user_id()) {
    header('Location: /dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StorefrontZA — Build a real business, step by step</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
  <div class="topbar">
    <div class="brand">Storefront<span>ZA</span></div>
    <div><a href="/login.php">Log in</a></div>
  </div>

  <div class="wrap">
    <div class="card" style="text-align:center;">
      <h1>Build a real online business — free, step by step.</h1>
      <p class="sub">Product research, store setup, local payments and couriers, ads, and social media — built for South African founders, not repackaged advice from somewhere else.</p>
      <a href="/register.php" class="btn">Create your free account</a>
      <p class="muted-link">Already started? <a href="/login.php">Log in</a></p>
    </div>
  </div>
</body>
</html>

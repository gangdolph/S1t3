<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en" data-theme="vaporwave">
<head>
  <meta charset="UTF-8">
  <title>SkuzE | Electronics Repair & Modding</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <div class="hero">
    <img src="assets/logo.png" alt="SkuzE logo" class="hero-logo" />
    <h2>Repair. Modding. Modern Support.</h2>
    <p>Get started below. Whether you're fixing, upgrading, or building — SkuzE has you covered.</p>

    <div class="cta-buttons">
      <a href="services.php">Services</a>
      <a href="buy.php">Buy</a>
      <a href="sell.php">Sell</a>
      <a href="trade.php">Trade</a>
    </div>

    <div class="secondary-links">
      <a href="about.php">About</a>
      <a href="help.php">Help/FAQ</a>
      <?php if(isset($_SESSION['user_id'])): ?>
        <a href="dashboard.php">Dashboard</a>
      <?php else: ?>
        <a href="login.php">Login / Register</a>
      <?php endif; ?>
    </div>
  </div>

  <?php include 'includes/footer.php'; ?>
</body>
</html>

<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/user.php';

$username = '';
$status = '';
if (!empty($_SESSION['user_id'])) {
  if (!empty($_SESSION['username']) && !empty($_SESSION['status'])) {
    $username = $_SESSION['username'];
    $status = $_SESSION['status'];
  } else {
    if ($stmt = $conn->prepare('SELECT username, status FROM users WHERE id = ?')) {
      $stmt->bind_param('i', $_SESSION['user_id']);
      $stmt->execute();
      $stmt->bind_result($username, $status);
      $stmt->fetch();
      $stmt->close();
      $_SESSION['username'] = $username;
      $_SESSION['status'] = $status;
    }
  }
}

$defaults = [
  'color1' => '#ff71ce',
  'color2' => '#01cdfe',
  'color3' => '#05ffa1',
  'speed' => 20,
];
$settingsFile = __DIR__ . '/../assets/vaporwave.json';
if (file_exists($settingsFile)) {
  $json = json_decode(file_get_contents($settingsFile), true);
  if (is_array($json)) {
    $defaults = array_merge($defaults, $json);
  }
}
?>
<style>
  :root {
    --vap1: <?= htmlspecialchars($defaults['color1'], ENT_QUOTES, 'UTF-8'); ?>;
    --vap2: <?= htmlspecialchars($defaults['color2'], ENT_QUOTES, 'UTF-8'); ?>;
    --vap3: <?= htmlspecialchars($defaults['color3'], ENT_QUOTES, 'UTF-8'); ?>;
    --vap-speed: <?= htmlspecialchars($defaults['speed'], ENT_QUOTES, 'UTF-8'); ?>s;
  }
</style>
<header class="site-header">
  <a href="/index.php" class="logo-link">
    <img class="logo-img" src="/assets/logo.png" alt="SkuzE Logo">
  </a>
  <nav class="site-nav">
    <ul>
      <li><a href="/index.php">Home</a></li>
      <li><a href="/about.php">About</a></li>
<?php if (empty($_SESSION['user_id'])): ?>
      <li><a href="/login.php">Login</a></li>
      <li><a href="/register.php">Register</a></li>
<?php else: ?>
      <li><a href="/dashboard.php">Dashboard</a></li>
      <li><a href="/logout.php">Logout</a></li>
<?php endif; ?>
    </ul>
  </nav>
  <button id="theme-toggle">Toggle Theme</button>
</header>
<script src="/assets/theme-toggle.js" defer></script>

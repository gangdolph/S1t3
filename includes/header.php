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
  $unread_messages = 0;
  if ($stmt = $conn->prepare('SELECT COUNT(*) FROM messages WHERE recipient_id = ? AND read_at IS NULL')) {
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($unread_messages);
    $stmt->fetch();
    $stmt->close();
  }
}

$defaults = [
  'color1' => '#ff71ce',
  'color2' => '#01cdfe',
  'color3' => '#05ffa1',
  'speed' => 20,
  'header_texture' => '',
  'footer_texture' => '',
  'sidebar_depth' => 12,
  'button_depth' => 6,
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
    --header-texture: <?= $defaults['header_texture'] ? 'url(' . htmlspecialchars($defaults['header_texture'], ENT_QUOTES, 'UTF-8') . ')' : 'none'; ?>;
    --footer-texture: <?= $defaults['footer_texture'] ? 'url(' . htmlspecialchars($defaults['footer_texture'], ENT_QUOTES, 'UTF-8') . ')' : 'none'; ?>;
    --nav-depth: <?= htmlspecialchars($defaults['sidebar_depth'], ENT_QUOTES, 'UTF-8'); ?>px;
    --btn-depth: <?= htmlspecialchars($defaults['button_depth'], ENT_QUOTES, 'UTF-8'); ?>px;
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
      <li><a href="/help.php">Help/FAQ</a></li>
<?php if (empty($_SESSION['user_id'])): ?>
      <li><a href="/login.php">Login</a></li>
      <li><a href="/register.php">Register</a></li>
<?php else: ?>
      <li><a href="/dashboard.php">Dashboard</a></li>
      <li><a href="/messages.php">Messages<?php if (!empty($unread_messages)): ?><span class="badge"><?= $unread_messages ?></span><?php endif; ?></a></li>
      <li><a href="/logout.php">Logout</a></li>
<?php endif; ?>
    </ul>
  </nav>
  <?php if (!empty($_SESSION['user_id'])): ?>
    <?= username_with_avatar($conn, $_SESSION['user_id'], $username) ?>
  <?php endif; ?>
  <form class="site-search" action="/search.php" method="get">
    <input type="text" name="q" placeholder="Search..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
  </form>
  <button id="theme-toggle" type="button" aria-haspopup="dialog" aria-controls="theme-modal">Themes</button>
</header>
<div id="theme-modal" class="theme-modal" role="dialog" aria-modal="true" hidden tabindex="-1">
  <div class="modal-content">
    <h2>Select Theme</h2>
    <div class="theme-options">
      <button type="button" class="btn" data-theme="light">Light</button>
      <button type="button" class="btn" data-theme="dark">Dark</button>
      <button type="button" class="btn" data-theme="vaporwave">Vaporwave</button>
    </div>
    <div id="theme-preview" class="theme-preview">
      <p>Sample text</p>
      <button type="button" class="btn">Sample Button</button>
    </div>
    <button id="theme-close" type="button" class="btn">Close</button>
  </div>
</div>
<script src="/assets/theme-toggle.js" defer></script>

<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/user.php';
require_once __DIR__ . '/notifications.php';

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

    $unread_notifications = count_unread_notifications($conn, $_SESSION['user_id']);
  }
  $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<header class="site-header">
  <nav class="site-nav header-left">
    <a href="/index.php" class="logo-link">
      <img class="logo-img" src="/assets/logo.png" alt="SkuzE Logo">
    </a>
    <ul>
      <li><a href="/index.php">Home</a></li>
      <li><a href="/about.php">About</a></li>
      <li><a href="/help.php">Help/FAQ</a></li>
    </ul>
  </nav>
  <form class="site-search header-center" action="/search.php" method="get">
    <input type="text" name="q" placeholder="Search..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
  </form>
  <nav class="site-nav header-right">
    <ul>
      <li><button id="theme-toggle" type="button" aria-haspopup="dialog" aria-controls="theme-modal">Themes</button></li>
      <li class="language-selector">
        <select id="language-select" name="language">
          <option value="en">EN</option>
          <option value="es">ES</option>
        </select>
      </li>
      <li class="user-info">
<?php if (empty($_SESSION['user_id'])): ?>
        <a href="/login.php">Login</a> /
        <a href="/register.php">Register</a>
<?php else: ?>
        <?= username_with_avatar($conn, $_SESSION['user_id'], $username) ?>
<?php endif; ?>
      </li>
      <li class="cart-link">
        <a href="/checkout.php">
          <img src="/assets/cart.svg" alt="Cart">
          <?php if (!empty($cart_count)): ?><span class="badge"><?= $cart_count ?></span><?php endif; ?>
        </a>
      </li>
    </ul>
  </nav>
</header>
<div id="theme-modal" class="theme-modal" role="dialog" aria-modal="true" aria-labelledby="theme-modal-title" hidden tabindex="-1">
  <div class="modal-content">
    <h2 id="theme-modal-title">Select Theme</h2>
    <div class="theme-error" role="alert"></div>
    <div class="theme-options"></div>
    <div id="theme-preview" class="theme-preview">
      <p>Sample text</p>
      <button type="button" class="btn">Sample Button</button>
    </div>
    <button id="theme-close" type="button" class="btn">Close</button>
  </div>
</div>
<script type="module" src="/assets/admin-pattern.js" defer></script>
<script type="module" src="/assets/theme-toggle.js" defer></script>

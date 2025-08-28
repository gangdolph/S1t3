<?php
require 'includes/auth.php';
require 'includes/db.php';

$id = $_SESSION['user_id'];
$username = '';
$vip = 0;
$vip_expires = null;
$stmt = $conn->prepare("SELECT username, vip_status, vip_expires_at FROM users WHERE id = ?");
if ($stmt === false) {
  error_log('Prepare failed: ' . $conn->error);
} else {
  $stmt->bind_param("i", $id);
  if (!$stmt->execute()) {
    error_log('Execute failed: ' . $stmt->error);
  } else {
    $stmt->bind_result($username, $vip, $vip_expires);
    $stmt->fetch();
    $stmt->close();
  }
}
$vip_active = $vip && (!$vip_expires || strtotime($vip_expires) > time());
$unread_messages = 0;
if ($stmt = $conn->prepare('SELECT COUNT(*) FROM messages WHERE recipient_id = ? AND read_at IS NULL')) {
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $stmt->bind_result($unread_messages);
  $stmt->fetch();
  $stmt->close();
}
?>
<?php require 'includes/layout.php'; ?>
  <title>Dashboard</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <?php include 'includes/sidebar.php'; ?>
  <?php include 'includes/header.php'; ?>

  <h2>Welcome, <?= htmlspecialchars($username) ?></h2>
  <?php if ($vip_active): ?>
    <?php $expiresTs = strtotime($vip_expires); $days = floor(($expiresTs - time())/86400); ?>
    <?php if ($days <= 7): ?>
      <p class="notice">Your VIP membership expires on <?= htmlspecialchars($vip_expires) ?>. <a href="vip.php">Renew now</a>.</p>
    <?php endif; ?>
  <?php elseif ($vip): ?>
    <p class="notice">Your VIP membership expired on <?= htmlspecialchars($vip_expires) ?>. <a href="vip.php">Renew now</a>.</p>
  <?php endif; ?>
  <p><a class="btn" role="button" href="services.php">Start a Service Request</a></p>
  <p><a class="btn" role="button" href="my-requests.php">View My Service Requests</a></p>
  <p><a class="btn" role="button" href="my-listings.php">Manage My Listings</a></p>
  <p><a class="btn" role="button" href="messages.php">Messages<?php if (!empty($unread_messages)): ?> <span class="badge"><?= $unread_messages ?></span><?php endif; ?></a></p>
  <?php if (!empty($_SESSION['is_admin'])): ?>
    <p><a class="btn" role="button" href="/admin/index.php">Admin Panel</a></p>
  <?php endif; ?>
  <p><a class="btn" role="button" href="profile.php">Edit Profile</a></p>
  <p><a class="btn" role="button" href="logout.php">Logout</a></p>
  <?php include 'includes/footer.php'; ?>
</body>
</html>

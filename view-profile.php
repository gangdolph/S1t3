<?php
require 'includes/auth.php';

$target = (int)($_GET['id'] ?? 0);
if (!$target) {
  echo 'No user specified.';
  exit;
}

$stmt = $conn->prepare("SELECT u.username, p.avatar_path, p.is_private FROM users u LEFT JOIN profiles p ON u.id = p.user_id WHERE u.id = ?");
$stmt->bind_param('i', $target);
$stmt->execute();
$stmt->bind_result($username, $avatar, $isPrivate);
$found = $stmt->fetch();
$stmt->close();
if (!$found) {
  echo 'User not found.';
  exit;
}
if ($avatar) {
  if (strpos($avatar, '/') !== false) {
    $candidate = $avatar;
    $fs = $avatar[0] === '/' ? __DIR__ . '/' . ltrim($avatar, '/') : __DIR__ . '/' . $avatar;
  } else {
    $candidate = 'assets/avatars/' . $avatar;
    $fs = __DIR__ . '/assets/avatars/' . $avatar;
  }
  $avatar = is_file($fs) ? $candidate : '';
}

$viewer = $_SESSION['user_id'];
$isFriend = false;
if ($viewer === $target) {
  $isFriend = true;
} else {
  $stmt2 = $conn->prepare("SELECT 1 FROM friends WHERE user_id = ? AND friend_id = ? AND status='accepted'");
  if ($stmt2) {
    $stmt2->bind_param('ii', $target, $viewer);
    $stmt2->execute();
    $stmt2->store_result();
    $isFriend = $stmt2->num_rows === 1;
    $stmt2->close();
  }
}
?>
<?php require 'includes/layout.php'; ?>
<head>
  <title>Profile of <?= htmlspecialchars($username); ?></title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <?php include 'includes/sidebar.php'; ?>
  <?php include 'includes/header.php'; ?>
  <h2><?= htmlspecialchars($username); ?></h2>
  <?php if ($isPrivate && !$isFriend): ?>
    <p>This profile is private.</p>
  <?php else: ?>
    <?php if ($avatar): ?>
      <img src="<?= htmlspecialchars($avatar); ?>" alt="Avatar" width="100">
    <?php endif; ?>
  <?php endif; ?>
  <?php include 'includes/footer.php'; ?>
</body>
</html>

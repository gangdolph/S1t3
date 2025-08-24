<?php
require 'includes/auth.php';
require 'includes/db.php';

$id = $_SESSION['user_id'];
$username = '';
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
if ($stmt === false) {
  error_log('Prepare failed: ' . $conn->error);
} else {
  $stmt->bind_param("i", $id);
  if (!$stmt->execute()) {
    error_log('Execute failed: ' . $stmt->error);
  } else {
    $stmt->bind_result($username);
    $stmt->fetch();
    $stmt->close();
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Dashboard</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <?php include 'includes/sidebar.php'; ?>
  <?php include 'includes/header.php'; ?>

  <h2>Welcome, <?= htmlspecialchars($username) ?></h2>
  <p><a href="services.php">Start a Service Request</a></p>
  <p><a href="my-requests.php">View My Service Requests</a></p>
  <p><a href="my-listings.php">Manage My Listings</a></p>
  <?php if (!empty($_SESSION['is_admin'])): ?>
    <p><a href="/admin/index.php">Admin Panel</a></p>
  <?php endif; ?>
  <p><a href="profile.php">Edit Profile</a></p>
  <p><a href="logout.php">Logout</a></p>
  <?php include 'includes/footer.php'; ?>
</body>
</html>

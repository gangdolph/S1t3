<?php
require '../includes/auth.php';
require '../includes/db.php';
require '../includes/user.php';

if (!$_SESSION['is_admin']) {
  header('Location: ../dashboard.php');
  exit;
}

// Handle approve/reject actions
if (isset($_GET['action'], $_GET['id'])) {
  $id = intval($_GET['id']);
  if ($_GET['action'] === 'approve') {
    $stmt = $conn->prepare("UPDATE listings SET status='approved' WHERE id=?");
  } elseif ($_GET['action'] === 'reject') {
    $stmt = $conn->prepare("UPDATE listings SET status='rejected' WHERE id=?");
  }
  if (isset($stmt)) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
  }
  header('Location: listings.php');
  exit;
}

$result = $conn->query("SELECT l.id, l.title, l.price, l.status, u.id AS user_id, u.username FROM listings l JOIN users u ON l.owner_id = u.id ORDER BY l.created_at DESC");
$listings = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html>
<head>
  <title>Review Listings</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
  <?php include '../includes/header.php'; ?>
  <h2>Listings Review</h2>
  <p><a href="index.php">Back to Admin Panel</a></p>
  <table>
    <tr><th>ID</th><th>User</th><th>Title</th><th>Price</th><th>Status</th><th>Actions</th></tr>
    <?php foreach ($listings as $l): ?>
      <tr>
        <td><?= $l['id'] ?></td>
        <td><?= username_with_avatar($conn, $l['user_id'], $l['username']) ?></td>
        <td><?= htmlspecialchars($l['title']) ?></td>
        <td><?= htmlspecialchars($l['price']) ?></td>
        <td><?= htmlspecialchars($l['status']) ?></td>
        <td>
          <?php if ($l['status'] === 'pending'): ?>
            <a href="?action=approve&id=<?= $l['id'] ?>">Approve</a>
            <a href="?action=reject&id=<?= $l['id'] ?>">Reject</a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
  <?php include '../includes/footer.php'; ?>
</body>
</html>

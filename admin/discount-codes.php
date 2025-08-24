<?php
require '../includes/auth.php';
require '../includes/db.php';

if (!$_SESSION['is_admin']) {
  header('Location: ../dashboard.php');
  exit;
}

// Handle deletions
if (isset($_GET['delete'])) {
  $code = $_GET['delete'];
  $stmt = $conn->prepare('DELETE FROM discount_codes WHERE code = ?');
  if ($stmt) {
    $stmt->bind_param('s', $code);
    $stmt->execute();
    $stmt->close();
  }
  header('Location: discount-codes.php');
  exit;
}

// Handle creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $code = trim($_POST['code'] ?? '');
  $percent = intval($_POST['percent'] ?? 0);
  $expiry = $_POST['expiry'] ?? '';
  $limit = intval($_POST['usage_limit'] ?? 0);

  if ($code !== '' && $percent > 0 && $percent <= 100 && $expiry !== '' && $limit > 0) {
    $stmt = $conn->prepare('INSERT INTO discount_codes (code, percent_off, expiry, usage_limit) VALUES (?, ?, ?, ?)');
    if ($stmt) {
      $stmt->bind_param('sisi', $code, $percent, $expiry, $limit);
      $stmt->execute();
      $stmt->close();
    }
  }
}

$result = $conn->query('SELECT code, percent_off, expiry, usage_limit FROM discount_codes ORDER BY expiry');
$codes = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html>
<head>
  <title>Discount Codes</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
  <?php include '../includes/header.php'; ?>
  <h2>Discount Codes</h2>
  <p><a href="index.php">Back to Admin Panel</a></p>
  <form method="post">
    <h3>Create Code</h3>
    <label>Code <input name="code" required></label>
    <label>Percent Off <input type="number" name="percent" min="1" max="100" required></label>
    <label>Expiry <input type="date" name="expiry" required></label>
    <label>Usage Limit <input type="number" name="usage_limit" min="1" required></label>
    <button type="submit">Add Code</button>
  </form>

  <h3>Existing Codes</h3>
  <table>
    <tr><th>Code</th><th>Percent</th><th>Expiry</th><th>Remaining Uses</th><th>Actions</th></tr>
    <?php foreach ($codes as $c): ?>
      <tr>
        <td><?= htmlspecialchars($c['code']) ?></td>
        <td><?= $c['percent_off'] ?>%</td>
        <td><?= htmlspecialchars($c['expiry']) ?></td>
        <td><?= $c['usage_limit'] ?></td>
        <td><a href="?delete=<?= urlencode($c['code']) ?>">Delete</a></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <?php include '../includes/footer.php'; ?>
</body>
</html>

<?php
require 'includes/auth.php';
require 'includes/db.php';
require 'includes/csrf.php';
require 'includes/totp.php';

$id = $_SESSION['user_id'];
$statuses = ['online', 'offline', 'busy'];
$status = 'offline';
$has2fa = false;
$secretDisplay = '';
$recoveryDisplay = '';
$avatar = '';
$isPrivate = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!validate_token($_POST['csrf_token'] ?? '')) {
    $error = "Invalid CSRF token.";
  } elseif (isset($_POST['update_contact'])) {
    $email = trim($_POST['email']);
    $phone_raw = trim($_POST['phone']);
    $phone = preg_replace('/[^0-9+\-\s()]/', '', $phone_raw);
    $status = $_POST['status'] ?? 'offline';
    if (!in_array($status, $statuses, true)) {
      $status = 'offline';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $error = "Please enter a valid email address.";
    } elseif ($phone_raw && !preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {
      $error = "Please enter a valid phone number.";
    } else {
      $stmt = $conn->prepare("UPDATE users SET email = ?, phone = ?, status = ? WHERE id = ?");
      if ($stmt === false) {
        error_log('Prepare failed: ' . $conn->error);
        $error = "Database error.";
      } else {
        $stmt->bind_param("sssi", $email, $phone, $status, $id);
        if (!$stmt->execute()) {
          error_log('Execute failed: ' . $stmt->error);
          $error = "Database error.";
        } else {
          $_SESSION['status'] = $status;
          $msg = "Profile updated.";
        }
        $stmt->close();
      }
    }
  } elseif (isset($_POST['update_password'])) {
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    if ($new !== $confirm) {
      $error = "Passwords do not match.";
    } elseif (strlen($new) < 6) {
      $error = "Password too short.";
    } else {
      $hash = password_hash($new, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
      if ($stmt === false) {
        error_log('Prepare failed: ' . $conn->error);
        $error = "Database error.";
      } else {
        $stmt->bind_param("si", $hash, $id);
        if (!$stmt->execute()) {
          error_log('Execute failed: ' . $stmt->error);
          $error = "Database error.";
        } else {
          $msg = "Password updated.";
        }
        $stmt->close();
      }
    }
  } elseif (isset($_POST['enable_2fa'])) {
    $secret = generate_base32_secret();
    $recovery = bin2hex(random_bytes(4));
    $stmt = $conn->prepare("REPLACE INTO user_2fa (user_id, secret, recovery_code) VALUES (?, ?, ?)");
    if ($stmt) {
      $stmt->bind_param('iss', $id, $secret, $recovery);
      if ($stmt->execute()) {
        $msg = "2FA enabled.";
        $secretDisplay = $secret;
        $recoveryDisplay = $recovery;
        $has2fa = true;
      } else {
        $error = "Database error.";
      }
      $stmt->close();
    }
  } elseif (isset($_POST['update_profile'])) {
    $isPrivate = isset($_POST['is_private']) ? 1 : 0;
    $avatarFilename = '';
    if ($stmtCur = $conn->prepare('SELECT avatar_path FROM profiles WHERE user_id = ?')) {
      $stmtCur->bind_param('i', $id);
      if ($stmtCur->execute()) {
        $stmtCur->bind_result($avatarFilename);
        $stmtCur->fetch();
      }
      $stmtCur->close();
    }
    if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
      $tmp = $_FILES['avatar']['tmp_name'];
      $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
      if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'], true)) {
        if (!is_dir('assets/avatars')) {
          mkdir('assets/avatars', 0777, true);
        }
        $newName = $id . '_' . time() . '.' . $ext;
        $avatarPath = 'assets/avatars/' . $newName;
        if (is_uploaded_file($tmp) && move_uploaded_file($tmp, $avatarPath) && is_file($avatarPath)) {
          $avatarFilename = $newName;
        }
      }
    }
    $stmt = $conn->prepare("REPLACE INTO profiles (user_id, avatar_path, is_private) VALUES (?, ?, ?)");
    if ($stmt) {
      $stmt->bind_param('isi', $id, $avatarFilename, $isPrivate);
      if ($stmt->execute()) {
        $candidate = $avatarFilename ? 'assets/avatars/' . $avatarFilename : '';
        $avatar = $candidate && is_file($candidate) ? $candidate : '';
        $msg = "Profile settings updated.";
      } else {
        $error = "Database error.";
      }
      $stmt->close();
    }
  } elseif (isset($_POST['disable_2fa'])) {
    $stmt = $conn->prepare("DELETE FROM user_2fa WHERE user_id = ?");
    if ($stmt) {
      $stmt->bind_param('i', $id);
      if ($stmt->execute()) {
        $msg = "2FA disabled.";
        $has2fa = false;
      }
      $stmt->close();
    }
  }
}

$stmt = $conn->prepare("SELECT email, phone, status FROM users WHERE id = ?");
if ($stmt === false) {
  error_log('Prepare failed: ' . $conn->error);
} else {
  $stmt->bind_param("i", $id);
  if (!$stmt->execute()) {
    error_log('Execute failed: ' . $stmt->error);
  } else {
    $stmt->bind_result($email, $phone, $status);
    $stmt->fetch();
  }
  $stmt->close();
}

$stmt2 = $conn->prepare("SELECT secret, recovery_code FROM user_2fa WHERE user_id = ?");
if ($stmt2) {
  $stmt2->bind_param('i', $id);
  if ($stmt2->execute()) {
    $stmt2->store_result();
    if ($stmt2->num_rows === 1) {
      $stmt2->bind_result($secretDisplay, $recoveryDisplay);
      $stmt2->fetch();
      $has2fa = true;
    }
  }
  $stmt2->close();
}

$stmt3 = $conn->prepare("SELECT avatar_path, is_private FROM profiles WHERE user_id = ?");
if ($stmt3) {
  $stmt3->bind_param('i', $id);
  if ($stmt3->execute()) {
    $stmt3->bind_result($avatarName, $isPrivate);
    if ($stmt3->fetch() && $avatarName) {
      if (strpos($avatarName, '/') !== false) {
        $candidate = $avatarName;
        $fs = $avatarName[0] === '/' ? __DIR__ . '/' . ltrim($avatarName, '/') : __DIR__ . '/' . $avatarName;
      } else {
        $candidate = 'assets/avatars/' . $avatarName;
        $fs = __DIR__ . '/assets/avatars/' . $avatarName;
      }
      $avatar = is_file($fs) ? $candidate : '';
    } else {
      $avatar = '';
    }
  }
  $stmt3->close();
}
?>
<?php require 'includes/layout.php'; ?>
<head>
  <title>Edit Profile</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <?php include 'includes/sidebar.php'; ?>
  <?php include 'includes/header.php'; ?>
  <h2>Edit Profile</h2>
  <?php if (!empty($error)) echo "<p style='color:red;'>" . htmlspecialchars($error) . "</p>"; ?>
  <?php if (!empty($msg)) echo "<p style='color:green;'>" . htmlspecialchars($msg) . "</p>"; ?>

    <h3>Contact Info</h3>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= generate_token(); ?>">
      <input type="email" name="email" value="<?= htmlspecialchars($email); ?>" required placeholder="Email">
      <input type="text" name="phone" value="<?= htmlspecialchars($phone); ?>" placeholder="Phone">
      <select name="status">
        <?php foreach ($statuses as $opt): ?>
          <option value="<?= htmlspecialchars($opt); ?>" <?= $status === $opt ? 'selected' : ''; ?>><?= htmlspecialchars(ucfirst($opt)); ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" name="update_contact">Save</button>
    </form>

    <h3>Change Password</h3>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= generate_token(); ?>">
      <input type="password" name="new_password" required placeholder="New Password">
      <input type="password" name="confirm_password" required placeholder="Confirm Password">
      <button type="submit" name="update_password">Update Password</button>
    </form>

    <h3>Two-Factor Authentication</h3>
    <?php if ($has2fa): ?>
      <?php if ($secretDisplay): ?>
        <p>Secret: <code><?= htmlspecialchars($secretDisplay); ?></code></p>
      <?php endif; ?>
      <p>Recovery Code: <code><?= htmlspecialchars($recoveryDisplay); ?></code></p>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= generate_token(); ?>">
        <button type="submit" name="disable_2fa">Disable 2FA</button>
      </form>
    <?php else: ?>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= generate_token(); ?>">
        <button type="submit" name="enable_2fa">Enable 2FA</button>
      </form>
    <?php endif; ?>

    <h3>Avatar & Privacy</h3>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= generate_token(); ?>">
      <?php if ($avatar): ?>
        <img src="<?= htmlspecialchars($avatar); ?>" alt="Avatar" width="100">
      <?php endif; ?>
      <input type="file" name="avatar" accept="image/*">
      <label>
        <input type="checkbox" name="is_private" value="1" <?= $isPrivate ? 'checked' : ''; ?>> Private Profile
      </label>
      <button type="submit" name="update_profile">Save</button>
    </form>

  <p><a href="dashboard.php">Back to Dashboard</a></p>
  <?php include 'includes/footer.php'; ?>
</body>
</html>

<?php
require '../includes/auth.php';

if (!$_SESSION['is_admin']) {
  header("Location: ../dashboard.php");
  exit;
}

$settingsFile = __DIR__ . '/../assets/vaporwave.json';
$defaults = [
  'color1' => '#ff71ce',
  'color2' => '#01cdfe',
  'color3' => '#05ffa1',
  'speed' => 20,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = [
    'color1' => $_POST['color1'] ?? $defaults['color1'],
    'color2' => $_POST['color2'] ?? $defaults['color2'],
    'color3' => $_POST['color3'] ?? $defaults['color3'],
    'speed' => max(5, min(60, (int)($_POST['speed'] ?? $defaults['speed']))),
  ];
  file_put_contents($settingsFile, json_encode($data));
}

if (file_exists($settingsFile)) {
  $stored = json_decode(file_get_contents($settingsFile), true);
  if (is_array($stored)) {
    $defaults = array_merge($defaults, $stored);
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Vaporwave Theme Settings</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
  <?php include '../includes/header.php'; ?>
  <h2>Vaporwave Theme Settings</h2>
  <form method="post">
    <label>Gradient Color 1
      <input type="color" name="color1" value="<?= htmlspecialchars($defaults['color1'], ENT_QUOTES, 'UTF-8'); ?>">
    </label>
    <label>Gradient Color 2
      <input type="color" name="color2" value="<?= htmlspecialchars($defaults['color2'], ENT_QUOTES, 'UTF-8'); ?>">
    </label>
    <label>Gradient Color 3
      <input type="color" name="color3" value="<?= htmlspecialchars($defaults['color3'], ENT_QUOTES, 'UTF-8'); ?>">
    </label>
    <label>Animation Speed (seconds)
      <input type="range" min="5" max="60" name="speed" value="<?= htmlspecialchars($defaults['speed'], ENT_QUOTES, 'UTF-8'); ?>">
    </label>
    <button type="submit">Save</button>
  </form>
  <p><a href="index.php">Back to Admin Panel</a></p>
  <?php include '../includes/footer.php'; ?>
</body>
</html>

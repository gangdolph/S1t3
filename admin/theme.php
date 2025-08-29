<?php
require '../includes/auth.php';

if (!$_SESSION['is_admin']) {
  header("Location: ../dashboard.php");
  exit;
}

$themesFile = __DIR__ . '/../assets/themes.json';
$themes = [];
if (file_exists($themesFile)) {
  $json = json_decode(file_get_contents($themesFile), true);
  if (is_array($json)) {
    $themes = $json;
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = strtolower(preg_replace('/[^a-z0-9_-]/i', '', $_POST['name'] ?? ''));
  if ($name) {
    $themes[$name] = [
      'label' => $_POST['label'] ?: ucfirst($name),
      'vars' => [
        '--bg' => $_POST['bg'] ?? '#ffffff',
        '--fg' => $_POST['fg'] ?? '#000000',
        '--accent' => $_POST['accent'] ?? '#ff71ce',
        '--gradient' => $_POST['gradient'] ?? 'linear-gradient(135deg, #ff71ce 0%, #01cdfe 100%)',
        '--vap1' => $_POST['vap1'] ?? null,
        '--vap2' => $_POST['vap2'] ?? null,
        '--vap3' => $_POST['vap3'] ?? null,
      ],
    ];
    $freq = $_POST['pattern_freq'] ?? '';
    $amp = $_POST['pattern_amp'] ?? '';
    $poly = $_POST['pattern_poly'] ?? '';
    $hue = $_POST['pattern_hue'] ?? '';
    $sat = $_POST['pattern_sat'] ?? '';
    if ($freq !== '' || $amp !== '' || $poly !== '' || $hue !== '' || $sat !== '') {
      $themes[$name]['pattern'] = [
        'frequency' => (float)$freq,
        'amplitude' => (float)$amp,
        'poly' => array_map('floatval', array_filter(array_map('trim', explode(',', $poly)), 'strlen')),
        'hue' => (int)$hue,
        'sat' => (int)$sat,
      ];
    }
    file_put_contents($themesFile, json_encode($themes, JSON_PRETTY_PRINT));
  }
  header('Location: theme.php');
  exit;
}

$edit = $_GET['edit'] ?? '';
$current = $themes[$edit] ?? null;
?>
<?php require '../includes/layout.php'; ?>
  <title>Theme Settings</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
  <?php include '../includes/header.php'; ?>
  <h2>Theme Settings</h2>
<?php if ($current): ?>
  <form method="post">
    <input type="hidden" name="name" value="<?= htmlspecialchars($edit, ENT_QUOTES, 'UTF-8'); ?>">
    <label>Name
      <input type="text" name="label" value="<?= htmlspecialchars($current['label'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    </label>
    <label>Background Color
      <input type="color" name="bg" value="<?= htmlspecialchars($current['vars']['--bg'] ?? '#ffffff', ENT_QUOTES, 'UTF-8'); ?>">
    </label>
    <label>Foreground Color
      <input type="color" name="fg" value="<?= htmlspecialchars($current['vars']['--fg'] ?? '#000000', ENT_QUOTES, 'UTF-8'); ?>">
    </label>
    <label>Accent Color
      <input type="color" name="accent" value="<?= htmlspecialchars($current['vars']['--accent'] ?? '#ff71ce', ENT_QUOTES, 'UTF-8'); ?>">
    </label>
    <label>Gradient CSS
      <input type="text" name="gradient" value="<?= htmlspecialchars($current['vars']['--gradient'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    </label>
    <label>Vap Color 1
      <input type="color" name="vap1" value="<?= htmlspecialchars($current['vars']['--vap1'] ?? '#ff71ce', ENT_QUOTES, 'UTF-8'); ?>">
    </label>
    <label>Vap Color 2
      <input type="color" name="vap2" value="<?= htmlspecialchars($current['vars']['--vap2'] ?? '#01cdfe', ENT_QUOTES, 'UTF-8'); ?>">
    </label>
    <label>Vap Color 3
      <input type="color" name="vap3" value="<?= htmlspecialchars($current['vars']['--vap3'] ?? '#05ffa1', ENT_QUOTES, 'UTF-8'); ?>">
    </label>
    <label>Pattern Frequency
      <input type="number" step="0.1" name="pattern_freq" value="<?= htmlspecialchars($current['pattern']['frequency'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    </label>
    <label>Pattern Amplitude
      <input type="number" step="0.1" name="pattern_amp" value="<?= htmlspecialchars($current['pattern']['amplitude'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    </label>
    <label>Pattern Polynomial (comma-separated)
      <input type="text" name="pattern_poly" value="<?= htmlspecialchars(isset($current['pattern']['poly']) ? implode(',', $current['pattern']['poly']) : '', ENT_QUOTES, 'UTF-8'); ?>">
    </label>
    <label>Pattern Hue
      <input type="number" name="pattern_hue" value="<?= htmlspecialchars($current['pattern']['hue'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    </label>
    <label>Pattern Saturation
      <input type="number" name="pattern_sat" value="<?= htmlspecialchars($current['pattern']['sat'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    </label>
    <button type="submit">Save Theme</button>
  </form>
  <p><a href="theme.php">Back</a></p>
<?php else: ?>
  <ul>
    <?php foreach ($themes as $name => $t): ?>
      <li><?= htmlspecialchars($t['label'], ENT_QUOTES, 'UTF-8'); ?> - <a href="?edit=<?= urlencode($name); ?>">Edit</a></li>
    <?php endforeach; ?>
  </ul>
  <p>To create a new theme, enter a unique name below:</p>
  <form method="post">
    <label>Key
      <input type="text" name="name" required>
    </label>
    <label>Display Name
      <input type="text" name="label" required>
    </label>
    <label>Background Color
      <input type="color" name="bg" value="#ffffff">
    </label>
    <label>Foreground Color
      <input type="color" name="fg" value="#000000">
    </label>
    <label>Accent Color
      <input type="color" name="accent" value="#ff71ce">
    </label>
    <label>Gradient CSS
      <input type="text" name="gradient" value="linear-gradient(135deg, #ff71ce 0%, #01cdfe 100%)">
    </label>
    <button type="submit">Create Theme</button>
  </form>
<?php endif; ?>
  <p><a href="index.php">Back to Admin Panel</a></p>
  <?php include '../includes/footer.php'; ?>
</body>
</html>

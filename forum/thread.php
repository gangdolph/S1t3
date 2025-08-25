<?php
require '../includes/auth.php';
require '../includes/csrf.php';
require '../includes/user.php';

$thread_id = (int)($_GET['id'] ?? 0);
if ($thread_id <= 0) {
  die('Invalid thread');
}

$thread = null;
if ($stmt = $conn->prepare("SELECT ft.title, u.id, u.username FROM forum_threads ft JOIN users u ON ft.user_id = u.id WHERE ft.id = ?")) {
  $stmt->bind_param('i', $thread_id);
  if ($stmt->execute()) {
    $stmt->bind_result($ttitle, $tuid, $tuser);
    if ($stmt->fetch()) {
      $thread = ['title' => $ttitle, 'user_id' => $tuid, 'username' => $tuser];
    }
  }
  $stmt->close();
}
if (!$thread) {
  die('Thread not found');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!validate_token($_POST['csrf_token'] ?? '')) {
    $error = 'Invalid CSRF token.';
  } else {
    $content = trim($_POST['content'] ?? '');
    if ($content !== '') {
      if ($stmt = $conn->prepare("INSERT INTO forum_posts (thread_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())")) {
        $stmt->bind_param('iis', $thread_id, $_SESSION['user_id'], $content);
        $stmt->execute();
        $stmt->close();
        header("Location: thread.php?id=" . $thread_id);
        exit;
      }
    } else {
      $error = 'Content required.';
    }
  }
}

$posts = [];
if ($pst = $conn->prepare("SELECT fp.content, fp.created_at, u.id, u.username FROM forum_posts fp JOIN users u ON fp.user_id = u.id WHERE fp.thread_id = ? ORDER BY fp.created_at")) {
  $pst->bind_param('i', $thread_id);
  if ($pst->execute()) {
    $pst->bind_result($pcontent, $pcreated, $puid, $puname);
    while ($pst->fetch()) {
      $posts[] = ['content' => $pcontent, 'created_at' => $pcreated, 'user_id' => $puid, 'username' => $puname];
    }
  }
  $pst->close();
}
?>
<?php require '../includes/layout.php'; ?>
<head>
  <title><?= htmlspecialchars($thread['title']); ?></title>
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<main>
  <h2><?= htmlspecialchars($thread['title']); ?></h2>
  <p>Started by <?= username_with_avatar($conn, $thread['user_id'], $thread['username']); ?></p>
  <?php if (!empty($error)) echo "<p class='error'>" . htmlspecialchars($error) . "</p>"; ?>
  <?php foreach ($posts as $post): ?>
    <div class="post">
      <strong><?= username_with_avatar($conn, $post['user_id'], $post['username']); ?></strong> on <?= htmlspecialchars($post['created_at']); ?>
      <p><?= nl2br(htmlspecialchars($post['content'])); ?></p>
    </div>
  <?php endforeach; ?>
  <h3>Reply</h3>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= generate_token(); ?>">
    <textarea name="content" placeholder="Your reply..."></textarea>
    <button type="submit">Post Reply</button>
  </form>
</main>
<?php include '../includes/footer.php'; ?>
</body>
</html>

<?php
session_start();
require 'includes/db.php';

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$where = "WHERE status='approved'";
$params = [];
$types = '';

if ($search !== '') {
    $where .= " AND (title LIKE ? OR description LIKE ? )";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}

if ($category !== '') {
    $where .= " AND category = ?";
    $params[] = $category;
    $types .= 's';
}

$countSql = "SELECT COUNT(*) FROM listings $where";
$stmt = $conn->prepare($countSql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();
$totalPages = (int)ceil($total / $limit);

$sql = "SELECT id, title, price, category, image FROM listings $where ORDER BY created_at DESC LIMIT ? OFFSET ?";
$paramsLimit = $params;
$typesLimit = $types . 'ii';
$paramsLimit[] = $limit;
$paramsLimit[] = $offset;
$stmt = $conn->prepare($sql);
$stmt->bind_param($typesLimit, ...$paramsLimit);
$stmt->execute();
$listings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<?php require 'includes/layout.php'; ?>
  <meta charset="UTF-8">
  <title>Buy from SkuzE</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <?php include 'includes/sidebar.php'; ?>
  <?php include 'includes/header.php'; ?>
  <h2>Available Listings</h2>
  <div class="content">
    <aside class="filters">
      <form method="get">
        <input type="text" name="search" placeholder="Search" value="<?= htmlspecialchars($search) ?>">
        <select name="category">
          <option value="">All Categories</option>
          <option value="phone" <?= $category==='phone'?'selected':'' ?>>Phone</option>
          <option value="console" <?= $category==='console'?'selected':'' ?>>Game Console</option>
          <option value="pc" <?= $category==='pc'?'selected':'' ?>>PC</option>
          <option value="other" <?= $category==='other'?'selected':'' ?>>Other</option>
        </select>
        <button type="submit">Filter</button>
      </form>
    </aside>
    <section class="listing-results">
      <?php if ($listings): ?>
        <ul>
        <?php foreach ($listings as $l): ?>
          <li class="listing">
            <?php $link = isset($_SESSION['user_id']) ? "checkout.php?listing_id={$l['id']}" : 'login.php'; ?>
            <a href="<?= $link ?>" class="listing-link">
              <h3><?= htmlspecialchars($l['title']) ?></h3>
              <p>Price: $<?= htmlspecialchars($l['price']) ?></p>
              <p>Category: <?= htmlspecialchars($l['category']) ?></p>
              <?php if ($l['image']): ?>
                <img src="uploads/<?= htmlspecialchars($l['image']) ?>" alt="" width="120">
              <?php endif; ?>
            </a>
          </li>
        <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p>No listings found. <a href="buy-step.php">Request a device</a></p>
      <?php endif; ?>
    </section>
  </div>
  <?php if ($totalPages > 1): ?>
    <nav class="pagination">
      <?php if ($page > 1): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">&laquo; Prev</a>
      <?php endif; ?>
      <?php if ($page < $totalPages): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next &raquo;</a>
      <?php endif; ?>
    </nav>
  <?php endif; ?>
  <?php include 'includes/footer.php'; ?>
</body>
</html>

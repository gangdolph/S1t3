<?php
require 'includes/auth.php';
require 'includes/db.php';

// Load Stripe secret from config file if present or fallback to environment variable
$stripeSecret = null;
$configPath = __DIR__ . '/config.php';
if (file_exists($configPath)) {
  $config = require $configPath;
  $stripeSecret = $config['stripe_secret'] ?? null;
} else {
  $stripeSecret = getenv('STRIPE_SECRET');
}

// Load Stripe dependencies if available
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
  require_once __DIR__ . '/vendor/autoload.php';
} else {
  die('Stripe dependencies missing.');
}

if (empty($stripeSecret)) {
  die('Stripe API key not configured.');
}

\Stripe\Stripe::setApiKey($stripeSecret);

$listing_id = (int)($_GET['listing_id'] ?? 0);
$amount = 5000; // default $50.00
$productName = 'PC Build Reservation';
if ($listing_id > 0) {
  $stmt = $conn->prepare('SELECT title, price FROM listings WHERE id = ? AND status = "approved"');
  if ($stmt) {
    $stmt->bind_param('i', $listing_id);
    $stmt->execute();
    $stmt->bind_result($ltitle, $lprice);
    if ($stmt->fetch()) {
      $productName = $ltitle;
      $amount = (int) round($lprice * 100);
    } else {
      die('Listing not found');
    }
    $stmt->close();
  }
}
$code = trim($_POST['discount_code'] ?? '');
if ($code !== '') {
  $stmt = $conn->prepare('SELECT percent_off, expiry, usage_limit FROM discount_codes WHERE code = ?');
  if ($stmt) {
    $stmt->bind_param('s', $code);
    $stmt->execute();
    $stmt->bind_result($percent, $expiry, $limit);
    if ($stmt->fetch()) {
      if ($limit > 0 && strtotime($expiry) >= time()) {
        $amount = (int) round($amount * (100 - $percent) / 100);
        $stmt->close();
        $upd = $conn->prepare('UPDATE discount_codes SET usage_limit = usage_limit - 1 WHERE code = ?');
        if ($upd) {
          $upd->bind_param('s', $code);
          $upd->execute();
          $upd->close();
        }
      } else {
        die('Discount code expired or depleted.');
      }
    } else {
      die('Invalid discount code.');
    }
    $stmt->close();
  }
}

$session = \Stripe\Checkout\Session::create([
  'payment_method_types' => ['card'],
  'line_items' => [[
    'price_data' => [
      'currency' => 'usd',
      'product_data' => ['name' => $productName],
      'unit_amount' => $amount,
    ],
    'quantity' => 1,
  ]],
  'mode' => 'payment',
  'success_url' => 'success.php',
  'cancel_url' => 'cancel.php',
]);

header("Location: " . $session->url);
exit;

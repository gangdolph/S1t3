<?php
// Copy this file to config.php and fill in real credentials.
// Never commit your actual config.php to version control.
return [
  'db_host' => 'localhost',
  'db_user' => 'your_db_user',
  'db_pass' => 'your_db_password',
  'db_name' => 'skuze_site',

  'smtp_host' => 'mail.example.com',
  'smtp_user' => 'user@example.com',
  'smtp_pass' => 'your_email_password',
  'smtp_port' => 465,

  // Google AdSense configuration
  'adsense_client' => 'ca-pub-XXXXXXXXXXXXXXXX',
  'adsense_slot' => '1234567890',

  // Stripe secret API key used for payment processing
  'stripe_secret' => 'your_stripe_secret_key'
];

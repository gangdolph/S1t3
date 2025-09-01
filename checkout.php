<?php
require 'includes/requirements.php';
require 'includes/auth.php';
require 'includes/db.php';
require 'includes/url.php';

// Placeholder for future payment integration.
// TODO: integrate a provider such as Square.
http_response_code(503);
echo 'Payments are temporarily unavailable.';
exit;


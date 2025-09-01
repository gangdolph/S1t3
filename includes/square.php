<?php
/**
 * Square helper.
 *
 * Loads credentials from config.php or environment variables, instantiates
 * Square\SquareClient, and exposes both the configuration array
 * ($squareConfig) and client ($square).
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load Square-specific credentials if present. This file may define
// SQUARE_ACCESS_TOKEN and optionally SQUARE_ENV.
$squareConfigPath = __DIR__ . '/../config.square.php';
if (file_exists($squareConfigPath)) {
    require $squareConfigPath;
}

if (!isset($config)) {
    $configPath = __DIR__ . '/../config.php';
    if (file_exists($configPath)) {
        $config = require $configPath;
    } else {
        $config = [];
    }
}

use Square\SquareClient;

$squareConfig = [
    'application_id' => $config['square_application_id'] ?? getenv('SQUARE_APPLICATION_ID') ?? '',
    'location_id'   => $config['square_location_id'] ?? getenv('SQUARE_LOCATION_ID') ?? '',
    'access_token'  => defined('SQUARE_ACCESS_TOKEN')
        ? SQUARE_ACCESS_TOKEN
        : ($config['square_access_token'] ?? getenv('SQUARE_ACCESS_TOKEN') ?? ''),
    'environment'   => defined('SQUARE_ENV')
        ? SQUARE_ENV
        : ($config['square_environment'] ?? getenv('SQUARE_ENVIRONMENT') ?? 'sandbox'),
];

$square = new SquareClient([
    'accessToken' => $squareConfig['access_token'],
    'environment' => $squareConfig['environment'],
]);

?>

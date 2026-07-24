<?php
/**
 * Shared API bootstrap + CORS
 */
require_once __DIR__ . '/../backend/bootstrap.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Max-Age: 3600');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Keep legacy constants for older scripts
if (!defined('DB_HOST')) {
    define('DB_HOST', config('db.host', 'localhost'));
    define('DB_USER', config('db.username', 'root'));
    define('DB_PASS', config('db.password', ''));
    define('DB_NAME', config('db.database', 'pentagon_quest'));
}

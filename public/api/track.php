<?php
require_once __DIR__ . '/config.php';

$code = trim($_GET['code'] ?? '');
if ($code === '') {
    json_response(['status' => 'error', 'message' => 'Tracking code required'], 422);
}

try {
    $stmt = Database::get()->prepare(
        'SELECT tracking_code, title, status, progress_percent, start_date, end_date, guests, currency, amount
         FROM bookings WHERE tracking_code = ? LIMIT 1'
    );
    $stmt->execute([$code]);
    $row = $stmt->fetch();
    if (!$row) {
        json_response(['status' => 'error', 'message' => 'Not found'], 404);
    }
    json_response(['status' => 'success', 'data' => $row]);
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => 'Unavailable'], 503);
}

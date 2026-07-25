<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status' => 'error', 'message' => 'POST required'], 405);
}

$data = request_json();
if (!$data) {
    $data = $_POST;
}

$name = trim($data['name'] ?? ($data['form_fields']['name'] ?? ''));
$email = strtolower(trim($data['email'] ?? ($data['form_fields']['email'] ?? '')));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['status' => 'error', 'message' => 'Valid email required'], 422);
}

try {
    $db = Database::get();
    if (Database::isMysql()) {
        $db->prepare('INSERT IGNORE INTO newsletter_subscribers (name, email, is_active, created_at) VALUES (?, ?, 1, ?)')
            ->execute([$name ?: null, $email, date('Y-m-d H:i:s')]);
    } else {
        $db->prepare('INSERT OR IGNORE INTO newsletter_subscribers (name, email, is_active, created_at) VALUES (?, ?, 1, ?)')
            ->execute([$name ?: null, $email, date('Y-m-d H:i:s')]);
    }
    json_response(['status' => 'success', 'message' => 'Subscribed successfully.']);
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => 'Subscription failed.'], 500);
}

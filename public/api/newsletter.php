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
    $now = date('Y-m-d H:i:s');

    // Upsert so re-subscribes reactivate and still get a confirmation email
    if (Database::isMysql()) {
        $db->prepare(
            'INSERT INTO newsletter_subscribers (name, email, is_active, created_at)
             VALUES (?, ?, 1, ?)
             ON DUPLICATE KEY UPDATE
                name = COALESCE(VALUES(name), name),
                is_active = 1'
        )->execute([$name !== '' ? $name : null, $email, $now]);
    } else {
        $db->prepare(
            'INSERT INTO newsletter_subscribers (name, email, is_active, created_at)
             VALUES (?, ?, 1, ?)
             ON CONFLICT(email) DO UPDATE SET
                name = COALESCE(excluded.name, newsletter_subscribers.name),
                is_active = 1'
        )->execute([$name !== '' ? $name : null, $email, $now]);
    }

    $brand = config('app_name', 'Pentagon Quest');
    $greeting = $name !== '' ? ('Hi ' . e($name) . ',') : 'Hi,';
    $mailer = new Mailer();
    $sent = $mailer->sendTemplate(
        $email,
        'You are subscribed to ' . $brand,
        'Welcome to ' . $brand,
        '<p>' . $greeting . '</p>'
            . '<p>You have successfully subscribed to <strong>' . e($brand) . '</strong>.</p>'
            . '<p>You will receive travel offers, new destinations, and exclusive deals as soon as we publish them.</p>'
            . '<p>Thank you for joining us.</p>',
        'Explore tours',
        app_url('/packages/')
    );

    if (!$sent) {
        // Still confirm in the UI; surface SMTP detail for debugging via email_logs
        json_response([
            'status' => 'success',
            'message' => 'You have successfully subscribed to ' . $brand . '.',
            'email_sent' => false,
            'email_error' => $mailer->getLastError(),
        ]);
    }

    json_response([
        'status' => 'success',
        'message' => 'You have successfully subscribed to ' . $brand . '.',
        'email_sent' => true,
    ]);
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => 'Subscription failed.', 'detail' => $e->getMessage()], 500);
}

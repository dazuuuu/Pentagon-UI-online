<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status' => 'error', 'message' => 'POST required'], 405);
}

$data = request_json();
if (!$data) {
    $data = $_POST;
}

// Support Elementor-style fields
$name = trim($data['name'] ?? ($data['form_fields']['name'] ?? ''));
$email = strtolower(trim($data['email'] ?? ($data['form_fields']['email'] ?? '')));
$subject = trim($data['subject'] ?? ($data['form_fields']['field_c4ab847'] ?? $data['form_fields']['subject'] ?? ''));
$message = trim($data['message'] ?? ($data['form_fields']['message'] ?? ''));
$phone = trim($data['phone'] ?? ($data['form_fields']['phone'] ?? ''));
$honeypot = trim($data['honeypot'] ?? ($data['form_fields']['Honeypot'] ?? ''));

if ($honeypot !== '') {
    json_response(['status' => 'success', 'message' => 'Thank you.']);
}

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
    json_response(['status' => 'error', 'message' => 'Name, valid email, and message are required.'], 422);
}

try {
    $db = Database::get();
    $clientId = null;
    $find = $db->prepare('SELECT id FROM clients WHERE email = ? LIMIT 1');
    $find->execute([$email]);
    $clientId = $find->fetchColumn() ?: null;

    $db->prepare(
        'INSERT INTO client_requests (client_id, name, email, phone, subject, message, status, source, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([
        $clientId ?: null,
        $name,
        $email,
        $phone ?: null,
        $subject ?: null,
        $message,
        'new',
        'contact-form',
        date('Y-m-d H:i:s'),
    ]);
    $requestId = (int)$db->lastInsertId();

    $mailer = new Mailer();
    $notify = Settings::get('site_contact_email', 'info@pentagonquest.com');
    $mailer->sendTemplate(
        $notify,
        'New inquiry from ' . $name,
        'New client request',
        '<p><strong>Name:</strong> ' . e($name) . '<br><strong>Email:</strong> ' . e($email) . '<br><strong>Subject:</strong> ' . e($subject) . '</p><p>' . nl2br(e($message)) . '</p>',
        'Open in admin',
        app_url('/admin/requests.php?id=' . $requestId)
    );
    $mailer->sendTemplate(
        $email,
        'We received your message · Pentagon Quest',
        'Thanks for contacting us',
        '<p>Hi ' . e($name) . ',</p><p>Our team has received your inquiry and will get back to you shortly.</p>'
    );

    json_response([
        'status' => 'success',
        'message' => 'Thank you! Your message has been received.',
        'request_id' => $requestId,
    ]);
} catch (Throwable $e) {
    json_response([
        'status' => 'error',
        'message' => 'Could not save your request. Please try WhatsApp or email us directly.',
        'detail' => $e->getMessage(),
    ], 500);
}

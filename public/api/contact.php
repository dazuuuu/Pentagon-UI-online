<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status' => 'error', 'message' => 'POST required'], 405);
}

$data = request_json();
if (!$data) {
    $data = $_POST;
}

// Support Elementor-style fields (flat or nested)
$name = trim($data['name'] ?? ($data['form_fields']['name'] ?? ''));
$email = strtolower(trim($data['email'] ?? ($data['form_fields']['email'] ?? '')));
$subject = trim($data['subject'] ?? ($data['form_fields']['field_c4ab847'] ?? $data['form_fields']['subject'] ?? ''));
$message = trim($data['message'] ?? ($data['form_fields']['message'] ?? ''));
$phone = trim($data['phone'] ?? ($data['form_fields']['phone'] ?? ''));
$destination = trim($data['destination'] ?? ($data['form_fields']['destination'] ?? ''));
$honeypot = trim($data['honeypot'] ?? ($data['form_fields']['Honeypot'] ?? ''));

if ($honeypot !== '') {
    json_response(['status' => 'success', 'message' => 'Thank you for contacting Pentagon Quest.']);
}

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
    json_response(['status' => 'error', 'message' => 'Name, valid email, and message are required.'], 422);
}

$brand = config('app_name', 'Pentagon Quest');

try {
    $db = Database::get();
    $find = $db->prepare('SELECT id FROM clients WHERE email = ? LIMIT 1');
    $find->execute([$email]);
    $clientId = $find->fetchColumn() ?: null;

    $db->prepare(
        'INSERT INTO client_requests (client_id, name, email, phone, subject, message, tour_interest, status, source, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([
        $clientId ?: null,
        $name,
        $email,
        $phone ?: null,
        $subject ?: null,
        $message,
        $destination ?: null,
        'new',
        'contact-form',
        date('Y-m-d H:i:s'),
    ]);
    $requestId = (int)$db->lastInsertId();

    $mailer = new Mailer();
    $emailErrors = [];

    // Notify site inbox
    $notify = Settings::get('site_contact_email', '') ?: ($mailer->isConfigured() ? (config('smtp.from_email') ?: 'info@pentagonquest.com') : 'info@pentagonquest.com');
    $notify = trim((string)$notify);
    $destinationLine = $destination !== '' ? '<br><strong>Destination:</strong> ' . e($destination) : '';
    $phoneLine = $phone !== '' ? '<br><strong>Phone:</strong> ' . e($phone) : '';

    if ($notify !== '' && filter_var($notify, FILTER_VALIDATE_EMAIL)) {
        $okAdmin = $mailer->sendTemplate(
            $notify,
            'New inquiry from ' . $name . ' · ' . $brand,
            'New client request',
            '<p><strong>Name:</strong> ' . e($name)
                . '<br><strong>Email:</strong> ' . e($email)
                . $phoneLine
                . '<br><strong>Subject:</strong> ' . e($subject ?: '—')
                . $destinationLine
                . '</p><p>' . nl2br(e($message)) . '</p>',
            'Open in admin',
            app_url('/admin/requests.php?id=' . $requestId)
        );
        if (!$okAdmin) {
            $emailErrors[] = 'admin: ' . $mailer->getLastError();
        }
    }

    // Always confirm to the user who contacted us
    $okUser = $mailer->sendTemplate(
        $email,
        'We received your message · ' . $brand,
        'Thanks for contacting ' . $brand,
        '<p>Hi ' . e($name) . ',</p>'
            . '<p>Thank you for contacting <strong>' . e($brand) . '</strong>. We have successfully received your inquiry'
            . ($subject !== '' ? ' about “' . e($subject) . '”' : '')
            . ' and our team will get back to you shortly.</p>'
            . '<p>If your matter is urgent, you can also reach us on WhatsApp at +254 729 836 336.</p>',
        'Browse packages',
        app_url('/packages/')
    );
    if (!$okUser) {
        $emailErrors[] = 'user: ' . $mailer->getLastError();
    }

    json_response([
        'status' => 'success',
        'message' => 'Thank you for contacting ' . $brand . '. Your message has been received successfully.',
        'request_id' => $requestId,
        'email_sent' => $okUser,
        'email_error' => $emailErrors ? implode(' | ', $emailErrors) : null,
    ]);
} catch (Throwable $e) {
    json_response([
        'status' => 'error',
        'message' => 'Could not save your request. Please try WhatsApp or email us directly.',
        'detail' => $e->getMessage(),
    ], 500);
}

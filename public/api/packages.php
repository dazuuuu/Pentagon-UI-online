<?php
require_once __DIR__ . '/config.php';

try {
    $db = Database::get();
    $status = $_GET['status'] ?? 'published';
    if ($status === 'all') {
        $packages = $db->query('SELECT id, title, slug, description, duration_days, duration_label, price, currency, location, status, featured FROM tours ORDER BY featured DESC, id DESC')->fetchAll();
    } else {
        $stmt = $db->prepare('SELECT id, title, slug, description, duration_days, duration_label, price, currency, location, status, featured FROM tours WHERE status = ? ORDER BY featured DESC, id DESC');
        $stmt->execute([$status]);
        $packages = $stmt->fetchAll();
    }

    json_response([
        'status' => 'success',
        'theme' => 'gold-green-black-white',
        'data' => $packages,
    ]);
} catch (Throwable $e) {
    json_response([
        'status' => 'error',
        'message' => 'Database unavailable. Run /admin/migrate.php first.',
        'data' => [],
    ], 503);
}

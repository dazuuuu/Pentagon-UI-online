<?php
require_once dirname(__DIR__, 2) . '/apps/backend/bootstrap.php';
Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['_csrf'] ?? null)) {
    json_response(['status' => 'error', 'message' => 'Invalid request.'], 400);
}

$kind = ($_POST['kind'] ?? 'image') === 'video' ? 'video' : 'image';

try {
    $url = Uploader::handle($_FILES['file'] ?? [], $kind);
    json_response(['status' => 'success', 'url' => $url]);
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 422);
}

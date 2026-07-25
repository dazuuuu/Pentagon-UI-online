<?php
require_once __DIR__ . '/config.php';

try {
    $db = Database::get();
    $travels = $db->query("SELECT id, title, slug, location, country, summary, status, featured FROM travels WHERE status = 'published' ORDER BY featured DESC, title ASC")->fetchAll();
    json_response(['status' => 'success', 'data' => $travels]);
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => 'Unavailable', 'data' => []], 503);
}

<?php
require_once __DIR__ . '/config.php';

try {
    $db = Database::get();
    $testimonials = $db->query(
        "SELECT author_name, author_role, quote, rating, avatar_url FROM testimonials WHERE status = 'published' ORDER BY sort_order ASC, id DESC"
    )->fetchAll();
    json_response(['status' => 'success', 'data' => $testimonials]);
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => 'Unavailable', 'data' => []], 503);
}

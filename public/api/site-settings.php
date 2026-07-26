<?php
require_once __DIR__ . '/config.php';

try {
    $data = [
        'hero_video_url' => Settings::get('hero_video_url', ''),
        'footer_image_url' => Settings::get('footer_image_url', ''),
        'page_hero_image_url' => Settings::get('page_hero_image_url', ''),
    ];
    json_response(['status' => 'success', 'data' => $data]);
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => 'Unavailable', 'data' => []], 503);
}

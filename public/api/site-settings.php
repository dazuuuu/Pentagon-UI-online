<?php
require_once __DIR__ . '/config.php';

// media_url() lives in the front-end bootstrap
require_once dirname(__DIR__) . '/includes/bootstrap-lite.php';

try {
    $hero = (string)Settings::get('hero_video_url', '');
    $footer = (string)Settings::get('footer_image_url', '');
    $pageHero = (string)Settings::get('page_hero_image_url', '');

    $data = [
        'hero_video_url' => $hero !== '' ? media_url($hero) : '',
        'footer_image_url' => $footer !== '' ? media_url($footer) : '',
        'page_hero_image_url' => $pageHero !== '' ? media_url($pageHero) : '',
    ];
    json_response(['status' => 'success', 'data' => $data]);
} catch (Throwable $e) {
    json_response(['status' => 'error', 'message' => 'Unavailable', 'data' => []], 503);
}

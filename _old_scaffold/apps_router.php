<?php
require_once __DIR__ . '/path.php';

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$path = '/' . ltrim($uri, '/');
$projectRoot = pq_project_root();

$segments = array_values(array_filter(explode('/', $path), static fn($segment) => $segment !== ''));
$prefix = $segments[0] ?? '';

if ($prefix === 'admin') {
    $target = $projectRoot . '/admin/' . ($segments[1] ?? 'index.php');
} elseif ($prefix === 'client') {
    $target = $projectRoot . '/client/' . ($segments[1] ?? 'index.php');
} elseif ($prefix === 'api') {
    $target = $projectRoot . '/api/' . ($segments[1] ?? 'index.php');
} else {
    $target = $projectRoot . '/admin/index.php';
}

if ($target !== '' && is_file($target)) {
    require $target;
    return;
}

if (is_dir($target)) {
    $index = rtrim($target, '/') . '/index.php';
    if (is_file($index)) {
        require $index;
        return;
    }
}

http_response_code(404);
echo 'Backend route not found.';

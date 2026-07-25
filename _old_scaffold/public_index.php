<?php
require_once dirname(__DIR__) . '/apps/path.php';

$uri = '/' . ltrim(urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/'), '/');
$publicRoot = pq_public_root();
$projectRoot = pq_project_root();

$serveFile = static function (string $basePath, string $path) use ($uri): void {
    $candidate = $basePath . $uri;
    if ($candidate === $basePath . '/' && is_file($basePath . '/index.php')) {
        require $basePath . '/index.php';
        return;
    }

    if (is_file($candidate)) {
        $extension = strtolower(pathinfo($candidate, PATHINFO_EXTENSION));
        $mime = [
            'css' => 'text/css; charset=UTF-8',
            'js' => 'application/javascript; charset=UTF-8',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'json' => 'application/json; charset=UTF-8',
            'txt' => 'text/plain; charset=UTF-8',
            'html' => 'text/html; charset=UTF-8',
            'php' => 'text/html; charset=UTF-8',
        ];
        if (isset($mime[$extension])) {
            header('Content-Type: ' . $mime[$extension]);
        }
        readfile($candidate);
        return;
    }

    if (is_dir($candidate)) {
        foreach (['index.php', 'index.html'] as $index) {
            $indexPath = rtrim($candidate, '/') . '/' . $index;
            if (is_file($indexPath)) {
                require $indexPath;
                return;
            }
        }
    }
};

if ($uri === '/' || $uri === '/index.php') {
    require __DIR__ . '/home.php';
    return;
}

$serveFile($publicRoot, $uri);

if (str_starts_with($uri, '/admin') || str_starts_with($uri, '/client') || str_starts_with($uri, '/api')) {
    require $projectRoot . '/apps/router.php';
    return;
}

$serveFile($projectRoot, $uri);

if (str_starts_with($uri, '/admin') || str_starts_with($uri, '/client') || str_starts_with($uri, '/api')) {
    require $projectRoot . '/apps/router.php';
    return;
}

http_response_code(404);
echo 'The requested page could not be found.';

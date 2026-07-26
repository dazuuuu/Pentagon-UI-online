<?php
/**
 * Local dev server router — mirrors how Apache serves the site once
 * `public/` is uploaded as the cPanel document root:
 *   php -S localhost:8080 router.php
 */
$publicRoot = __DIR__ . '/public';
$uri = '/' . ltrim(urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/'), '/');
$file = $publicRoot . $uri;

if ($uri !== '/' && is_file($file)) {
    return false; // let the built-in server serve/execute it directly
}

if (is_dir($file)) {
    foreach (['index.php', 'index.html'] as $index) {
        $indexFile = rtrim($file, '/') . '/' . $index;
        if (is_file($indexFile)) {
            $_SERVER['SCRIPT_NAME'] = rtrim($uri, '/') . '/' . $index;
            if (str_ends_with($index, '.php')) {
                chdir(dirname($indexFile));
                require $indexFile;
            } else {
                readfile($indexFile);
            }
            return true;
        }
    }
}

http_response_code(404);
echo '404 Not Found';

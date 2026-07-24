<?php
/**
 * Router for PHP built-in server:
 *   php -S localhost:8080 router.php
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$file = __DIR__ . $uri;

if ($uri !== '/' && is_file($file)) {
    return false; // serve static file
}

// Directory index
if (is_dir($file)) {
    foreach (['index.php', 'index.html'] as $index) {
        if (is_file(rtrim($file, '/') . '/' . $index)) {
            if (str_ends_with($index, '.php')) {
                require rtrim($file, '/') . '/' . $index;
                return true;
            }
            return false;
        }
    }
}

http_response_code(404);
echo '404 Not Found';
return true;

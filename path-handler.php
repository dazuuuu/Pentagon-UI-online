<?php
/**
 * Pentagon Quest UI – single path handler
 *
 * The real site files live under /public. When the whole project folder
 * ("Pentagon Quest UI") is the Apache/AMPPS document root, URLs like
 *   /Pentagon Quest UI/admin/login.php
 * would 404 because those files are actually at
 *   /Pentagon Quest UI/public/admin/login.php
 *
 * This file maps every request into /public.
 *
 * Works with:
 *   1) Root .htaccess rewrite  → /admin/login.php
 *   2) PATH_INFO fallback     → /path-handler.php/admin/login.php
 *   3) Query fallback         → /path-handler.php?r=/admin/login.php
 *   4) PHP built-in server via router.php
 */

declare(strict_types=1);

define('PQ_PROJECT_ROOT', __DIR__);
define('PQ_PUBLIC_ROOT', __DIR__ . '/public');

/**
 * Detect URL prefix for this install (e.g. "/Pentagon Quest UI").
 */
function pq_detect_base_path(): string
{
    $scriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
    $uri = rawurldecode((string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/'));

    // Front controllers: /Pentagon Quest UI/path-handler.php
    if (preg_match('#^(.*?)/(?:path-handler|router|index)\.php$#', $scriptName, $m)) {
        return $m[1];
    }
    if (in_array(basename($scriptName), ['path-handler.php', 'router.php', 'index.php'], true)) {
        $dir = str_replace('\\', '/', dirname($scriptName));
        return ($dir === '/' || $dir === '.' || $dir === '\\') ? '' : rtrim($dir, '/');
    }

    // Prefer the install folder that sits before /admin|/devs|/client|/api
    if (preg_match('#^(.*?)/(?:admin|client|api|devs)(?:/|$)#', $uri, $m)) {
        return $m[1];
    }
    if (preg_match('#^(.*?)/(?:admin|client|api|devs)(?:/|$)#', $scriptName, $m)) {
        return $m[1];
    }

    // PHP built-in server often sets SCRIPT_NAME to the request path — do not
    // treat "/admin" itself as the base path.
    if ($scriptName === $uri) {
        return '';
    }

    $dir = str_replace('\\', '/', dirname($scriptName));
    if ($dir === '/' || $dir === '.' || $dir === '\\') {
        return '';
    }
    // Guard: never return an app segment as the base
    if (preg_match('#/(?:admin|client|api|devs)$#', $dir)) {
        return dirname($dir) === '/' ? '' : rtrim(str_replace('\\', '/', dirname($dir)), '/');
    }
    return rtrim($dir, '/');
}

/**
 * Path under /public to serve (starts with /).
 */
function pq_request_relative_path(string $basePath): string
{
    // 1) Explicit query: path-handler.php?r=/admin/login.php
    if (isset($_GET['r']) && is_string($_GET['r']) && $_GET['r'] !== '') {
        return '/' . ltrim(rawurldecode($_GET['r']), '/');
    }

    // 2) PATH_INFO: path-handler.php/admin/login.php
    $pathInfo = (string)($_SERVER['PATH_INFO'] ?? '');
    if ($pathInfo !== '') {
        return '/' . ltrim(rawurldecode($pathInfo), '/');
    }

    // 3) Pretty URL from REQUEST_URI (after .htaccess rewrite / built-in router)
    $uri = (string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
    $uri = rawurldecode($uri);

    if ($basePath !== '' && str_starts_with($uri, $basePath . '/')) {
        $uri = substr($uri, strlen($basePath)) ?: '/';
    } elseif ($basePath !== '' && $uri === $basePath) {
        $uri = '/';
    }

    // Strip handler filename if present in the URI
    $uri = preg_replace('#^/(?:path-handler|router|index)\.php#', '', $uri) ?? $uri;
    if ($uri === '' || $uri === false) {
        $uri = '/';
    }

    return '/' . ltrim($uri, '/');
}

function pq_is_under_public(string $realFile): bool
{
    $publicReal = realpath(PQ_PUBLIC_ROOT);
    if ($publicReal === false) {
        return false;
    }
    $publicReal = rtrim(str_replace('\\', '/', $publicReal), '/');
    $realFile = str_replace('\\', '/', $realFile);
    return $realFile === $publicReal || str_starts_with($realFile, $publicReal . '/');
}

function pq_mime(string $file): string
{
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $map = [
        'html' => 'text/html; charset=UTF-8',
        'htm' => 'text/html; charset=UTF-8',
        'css' => 'text/css; charset=UTF-8',
        'js' => 'application/javascript; charset=UTF-8',
        'json' => 'application/json; charset=UTF-8',
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'gif' => 'image/gif',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'map' => 'application/json',
        'txt' => 'text/plain; charset=UTF-8',
        'xml' => 'application/xml; charset=UTF-8',
        'pdf' => 'application/pdf',
    ];
    if (isset($map[$ext])) {
        return $map[$ext];
    }
    if (function_exists('mime_content_type')) {
        $detected = @mime_content_type($file);
        if (is_string($detected) && $detected !== '') {
            return $detected;
        }
    }
    return 'application/octet-stream';
}

function pq_resolve_file(string $relPath): ?array
{
    if ($relPath === '' || str_contains($relPath, "\0") || preg_match('#\.\.#', $relPath)) {
        return null;
    }

    $relPath = '/' . ltrim($relPath, '/');
    $candidate = PQ_PUBLIC_ROOT . ($relPath === '/' ? '' : $relPath);

    if (is_dir($candidate)) {
        foreach (['index.php', 'index.html'] as $index) {
            $indexFile = rtrim($candidate, '/\\') . DIRECTORY_SEPARATOR . $index;
            if (is_file($indexFile)) {
                $relPath = rtrim($relPath, '/') . '/' . $index;
                $candidate = $indexFile;
                break;
            }
        }
    }

    // Allow /admin/login → /admin/login.php
    if (!is_file($candidate) && !str_contains(basename($relPath), '.')) {
        if (is_file($candidate . '.php')) {
            $candidate .= '.php';
            $relPath .= '.php';
        } elseif (is_file($candidate . '.html')) {
            $candidate .= '.html';
            $relPath .= '.html';
        }
    }

    if (!is_file($candidate)) {
        return null;
    }

    $real = realpath($candidate);
    if ($real === false || !pq_is_under_public($real)) {
        return null;
    }

    return ['file' => $real, 'rel' => $relPath];
}

function pq_dispatch(): void
{
    $basePath = pq_detect_base_path();
    define('PQ_BASE_PATH', $basePath);

    $relPath = pq_request_relative_path($basePath);

    // Never expose apps/, scripts/, or the handler internals
    $blocked = ['#^/(apps|scripts|_old_scaffold|\.git)(/|$)#i'];
    foreach ($blocked as $re) {
        if (preg_match($re, $relPath)) {
            http_response_code(404);
            echo '404 Not Found';
            return;
        }
    }

    $resolved = pq_resolve_file($relPath);
    if ($resolved === null) {
        http_response_code(404);
        header('Content-Type: text/html; charset=UTF-8');
        $try = htmlspecialchars($relPath, ENT_QUOTES, 'UTF-8');
        $base = htmlspecialchars($basePath === '' ? '' : $basePath, ENT_QUOTES, 'UTF-8');
        echo "<!DOCTYPE html><html><head><meta charset=\"utf-8\"><title>404</title></head><body>";
        echo "<h1>404 – page not found</h1>";
        echo "<p>Requested path: <code>{$try}</code></p>";
        echo "<p>Try:</p><ul>";
        echo "<li><a href=\"{$base}/admin/login.php\">Admin login</a></li>";
        echo "<li><a href=\"{$base}/devs/register.php\">Register admin (devs)</a></li>";
        echo "<li><a href=\"{$base}/admin/migrate.php\">Run migrations</a></li>";
        echo "<li><a href=\"{$base}/\">Home</a></li>";
        echo "</ul></body></html>";
        return;
    }

    $file = $resolved['file'];
    $rel = $resolved['rel'];

    // Pretty SCRIPT_NAME so url()/base_path() keep working inside PHP pages
    $_SERVER['SCRIPT_FILENAME'] = $file;
    $_SERVER['SCRIPT_NAME'] = ($basePath === '' ? '' : $basePath) . $rel;
    $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];

    if (str_ends_with(strtolower($file), '.php')) {
        chdir(dirname($file));
        require $file;
        return;
    }

    header('Content-Type: ' . pq_mime($file));
    header('Content-Length: ' . (string)filesize($file));
    readfile($file);
}

pq_dispatch();

<?php
/**
 * Minimal bootstrap for shared front-end template partials (footer, hero,
 * etc.) — just enough to read Settings from the database. Does NOT start a
 * session or load Auth/Mailer/etc., since static pages render far more
 * often than admin pages and shouldn't carry that overhead.
 */
$__pqRoot = dirname(__DIR__, 2);
require_once $__pqRoot . '/apps/backend/lib/helpers.php';
require_once $__pqRoot . '/apps/backend/lib/Database.php';
require_once $__pqRoot . '/apps/backend/lib/Settings.php';
load_dotenv($__pqRoot . '/apps/backend/.env');

/**
 * Site-root-relative path, correct whether this page is served at a true
 * domain root (production, or the local vhost) or nested under a subfolder
 * (e.g. local AMPPS serving the whole www/ folder). Unlike admin's
 * base_path(), which looks for /admin/, this looks for the /public/
 * segment that only appears in subfolder-mode URLs.
 */
function site_path(string $path = ''): string
{
    static $prefix = null;
    if ($prefix === null) {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $marker = '/public/';
        $pos = strpos($script, $marker);
        $prefix = $pos !== false ? substr($script, 0, $pos + strlen($marker) - 1) : '';
    }
    $path = '/' . ltrim($path, '/');
    return $prefix . $path;
}

/**
 * Resolves a stored media value (from Settings/uploads) to a working URL.
 * Uploaded files are stored as plain site-root-relative paths ("/uploads/x.jpg")
 * with no knowledge of subfolder-mode hosting, so those need site_path().
 * Full external URLs (https://..., //cdn...) are left untouched.
 */
function media_url(string $value): string
{
    if ($value === '' || preg_match('#^(https?:)?//#i', $value)) {
        return $value;
    }
    return site_path($value);
}

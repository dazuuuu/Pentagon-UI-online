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
 * Site-root-relative path for front-end pages.
 * Uses the shared install prefix (e.g. "/Pentagon Quest UI").
 */
if (!function_exists('site_path')) {
    function site_path(string $path = ''): string
    {
        return base_path($path);
    }
}

/**
 * Resolves a stored media value (from Settings/uploads) to a working URL.
 * Uploaded files are stored as plain site-root-relative paths ("/uploads/x.jpg")
 * with no knowledge of subfolder-mode hosting, so those need site_path().
 * Full external URLs (https://..., //cdn...) are left untouched.
 */
if (!function_exists('media_url')) {
    function media_url(string $value): string
    {
        if ($value === '' || preg_match('#^(https?:)?//#i', $value)) {
            return $value;
        }
        return site_path($value);
    }
}

<?php

function config(?string $key = null, $default = null)
{
    static $cfg = null;
    if ($cfg === null) {
        $configPath = getenv('PQ_CONFIG_PATH');
        if ($configPath === false || $configPath === '') {
            $configPath = __DIR__ . '/../config.php';
        }
        $cfg = require $configPath;
    }
    if ($key === null) {
        return $cfg;
    }
    $parts = explode('.', $key);
    $val = $cfg;
    foreach ($parts as $p) {
        if (!is_array($val) || !array_key_exists($p, $val)) {
            return $default;
        }
        $val = $val[$p];
    }
    return $val;
}

/**
 * URL prefix for the site when it lives in a subfolder.
 * Default / configured root folder: "Pentagon Quest UI"
 * Example: http://localhost/Pentagon%20Quest%20UI/admin/login.php
 */
function base_path(): string
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $configured = config('base_path', null);
    if (is_string($configured) && $configured !== '') {
        $cached = rtrim(str_replace('\\', '/', $configured), '/');
        if ($cached === '/' || $cached === '.') {
            $cached = '';
        }
        return $cached;
    }

    $script = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
    if (preg_match('#^(.*?)/(?:admin|client|api|devs)(?:/|$)#', $script, $m)) {
        $cached = $m[1];
        return $cached;
    }

    // Fallback for local AMPPS/XAMPP folder name when auto-detect is unavailable
    $cached = '/Pentagon Quest UI';
    return $cached;
}

/**
 * Build a site-root-relative URL under the configured base path.
 * url('admin/login.php') => /Pentagon Quest UI/admin/login.php
 */
function url(string $path = ''): string
{
    if (preg_match('#^(https?:)?//#i', $path) || str_starts_with($path, 'mailto:') || str_starts_with($path, 'tel:')) {
        return $path;
    }

    $base = base_path();
    $path = ltrim($path, '/');
    if ($path === '') {
        return $base === '' ? '/' : $base . '/';
    }
    return ($base === '' ? '' : $base) . '/' . $path;
}

function app_url(string $path = ''): string
{
    $base = rtrim((string)config('app_url'), '/');
    if ($base === '') {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base = ($https ? 'https' : 'http') . '://' . $host . base_path();
    }
    $path = ltrim($path, '/');
    return $path === '' ? $base : $base . '/' . $path;
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $to): never
{
    // Rewrite site-absolute paths so subdirectory installs keep working
    if ($to !== '' && $to[0] === '/' && !str_starts_with($to, '//') && !preg_match('#^https?://#i', $to)) {
        $to = url($to);
    }
    header('Location: ' . $to);
    exit;
}

function flash(string $key, ?string $message = null): ?string
{
    if (!isset($_SESSION['_flash'])) {
        $_SESSION['_flash'] = [];
    }
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }
    $msg = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $msg;
}

function csrf_token(): string
{
    $key = config('csrf_key', 'pq_csrf_token');
    if (empty($_SESSION[$key])) {
        $_SESSION[$key] = bin2hex(random_bytes(32));
    }
    return $_SESSION[$key];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(?string $token = null): bool
{
    $key = config('csrf_key', 'pq_csrf_token');
    $token = $token ?? ($_POST['_csrf'] ?? '');
    return is_string($token)
        && isset($_SESSION[$key])
        && hash_equals($_SESSION[$key], $token);
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function request_json(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return $_POST ?: [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function notify_subscribers(string $subject, string $title, string $bodyHtml, ?string $ctaLabel = null, ?string $ctaUrl = null): void
{
    try {
        $emails = Database::get()
            ->query("SELECT email FROM newsletter_subscribers WHERE is_active = 1")
            ->fetchAll(PDO::FETCH_COLUMN);
    } catch (Throwable $e) {
        return;
    }
    if (!$emails) {
        return;
    }
    $mailer = new Mailer();
    foreach ($emails as $email) {
        try {
            $mailer->sendTemplate($email, $subject, $title, $bodyHtml, $ctaLabel, $ctaUrl);
        } catch (Throwable $e) {
            // One bad address shouldn't stop the rest of the broadcast
        }
    }
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-') ?: 'item';
}

function money_format_kes($amount): string
{
    return 'KES ' . number_format((float)$amount, 0);
}

function status_badge(string $status): string
{
    $map = [
        'pending' => 'badge-warn',
        'new' => 'badge-info',
        'confirmed' => 'badge-ok',
        'accepted' => 'badge-ok',
        'in_progress' => 'badge-info',
        'completed' => 'badge-ok',
        'cancelled' => 'badge-danger',
        'rejected' => 'badge-danger',
        'draft' => 'badge-muted',
        'published' => 'badge-ok',
        'sent' => 'badge-ok',
        'failed' => 'badge-danger',
        'active' => 'badge-ok',
        'inactive' => 'badge-muted',
    ];
    $class = $map[$status] ?? 'badge-muted';
    return '<span class="badge ' . $class . '">' . e(str_replace('_', ' ', $status)) . '</span>';
}

function tracking_code(): string
{
    return 'PQ-' . strtoupper(bin2hex(random_bytes(4)));
}

function log_activity(string $actorType, ?int $actorId, string $action, ?string $details = null): void
{
    try {
        $db = Database::get();
        $db->prepare(
            'INSERT INTO activity_logs (actor_type, actor_id, action, details, ip_address, created_at)
             VALUES (?, ?, ?, ?, ?, ?)'
        )->execute([
            $actorType,
            $actorId,
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? null,
            date('Y-m-d H:i:s'),
        ]);
    } catch (Throwable $e) {
        // Ignore logging failures during early setup
    }
}

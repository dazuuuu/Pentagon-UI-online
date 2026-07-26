<?php
/**
 * Pentagon Quest – application configuration
 * Copy values here for your environment (SMTP, DB, URLs).
 */

return [
    'app_name' => 'Pentagon Quest',
    'app_url' => '', // Auto-detected if empty, e.g. https://pentagonquest.com
    'timezone' => 'Africa/Nairobi',

    // Database: mysql (default) or sqlite
    'db' => [
        'driver' => 'mysql',
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => (int)(getenv('DB_PORT') ?: 3306),
        'database' => getenv('DB_NAME') ?: 'pentagon_quest',
        'username' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASS') ?: 'mysql', // AMPPS' default local MySQL root password
        'charset' => 'utf8mb4',
        'sqlite_path' => __DIR__ . '/../data/pentagon_quest.sqlite',
    ],

    // SMTP – configure via Admin → Settings or here
    'smtp' => [
        'host' => getenv('SMTP_HOST') ?: '',
        'port' => (int)(getenv('SMTP_PORT') ?: 587),
        'encryption' => getenv('SMTP_ENCRYPTION') ?: 'tls', // tls, ssl, or none
        'username' => getenv('SMTP_USER') ?: '',
        'password' => getenv('SMTP_PASS') ?: '',
        'from_email' => getenv('SMTP_FROM') ?: 'noreply@pentagonquest.com',
        'from_name' => getenv('SMTP_FROM_NAME') ?: 'Pentagon Quest',
    ],

    'session_name' => 'pq_session',
    'csrf_key' => 'pq_csrf_token',

    // First-admin registration is open until at least one admin exists
    'allow_open_admin_register' => true,

    'uploads_path' => dirname(__DIR__, 2) . '/public/uploads',
    'uploads_url' => '/uploads',
];

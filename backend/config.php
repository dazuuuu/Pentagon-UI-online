<?php
/**
 * Pentagon Quest – application configuration
 * Copy values here for your environment (SMTP, DB, URLs).
 */

return [
    'app_name' => 'Pentagon Quest',
    'app_url' => '', // Auto-detected if empty, e.g. https://pentagonquest.com
    'timezone' => 'Africa/Nairobi',

    // Database: sqlite (default) or mysql
    'db' => [
        'driver' => 'sqlite',
        'sqlite_path' => __DIR__ . '/../data/pentagon_quest.sqlite',
        // MySQL (set driver to 'mysql' to use):
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'pentagon_quest',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
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

    'uploads_path' => __DIR__ . '/../uploads',
    'uploads_url' => '/uploads',
];

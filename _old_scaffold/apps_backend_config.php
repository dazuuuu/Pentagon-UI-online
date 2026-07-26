<?php
return [
    'app_name' => 'Pentagon Quest',
    'app_url' => '',
    'timezone' => 'Africa/Nairobi',
    'db' => [
        'driver' => 'mysql',
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => (int)(getenv('DB_PORT') ?: 3306),
        'database' => getenv('DB_NAME') ?: 'pentagon_quest',
        'username' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    'smtp' => [
        'host' => getenv('SMTP_HOST') ?: '',
        'port' => (int)(getenv('SMTP_PORT') ?: 587),
        'encryption' => getenv('SMTP_ENCRYPTION') ?: 'tls',
        'username' => getenv('SMTP_USER') ?: '',
        'password' => getenv('SMTP_PASS') ?: '',
        'from_email' => getenv('SMTP_FROM') ?: 'noreply@pentagonquest.com',
        'from_name' => getenv('SMTP_FROM_NAME') ?: 'Pentagon Quest',
    ],
    'session_name' => 'pq_session',
    'csrf_key' => 'pq_csrf_token',
    'allow_open_admin_register' => true,
    'uploads_path' => dirname(__DIR__, 2) . '/uploads',
    'uploads_url' => '/uploads',
];

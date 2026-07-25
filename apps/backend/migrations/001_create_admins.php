<?php

return function (PDO $db): void {
    $id = Database::idColumn();
    $dt = Database::datetimeDefault();
    $db->exec("CREATE TABLE IF NOT EXISTS admins (
        {$id},
        name VARCHAR(120) NOT NULL,
        email VARCHAR(190) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(40) NOT NULL DEFAULT 'admin',
        is_active INTEGER NOT NULL DEFAULT 1,
        last_login_at TEXT NULL,
        created_at {$dt},
        updated_at TEXT NULL
    )");
};

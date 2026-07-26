<?php

return function (PDO $db): void {
    $id = Database::idColumn();
    $dt = Database::datetimeDefault();
    $ref = Database::refType();
    $db->exec("CREATE TABLE IF NOT EXISTS client_requests (
        {$id},
        client_id {$ref} NULL,
        name VARCHAR(120) NOT NULL,
        email VARCHAR(190) NOT NULL,
        phone VARCHAR(40) NULL,
        subject VARCHAR(200) NULL,
        message TEXT NOT NULL,
        tour_interest VARCHAR(200) NULL,
        preferred_dates VARCHAR(120) NULL,
        status VARCHAR(40) NOT NULL DEFAULT 'new',
        admin_notes TEXT NULL,
        assigned_admin_id {$ref} NULL,
        source VARCHAR(60) NOT NULL DEFAULT 'website',
        created_at {$dt},
        updated_at TEXT NULL,
        FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS newsletter_subscribers (
        {$id},
        name VARCHAR(120) NULL,
        email VARCHAR(190) NOT NULL UNIQUE,
        is_active INTEGER NOT NULL DEFAULT 1,
        created_at {$dt}
    )");
};

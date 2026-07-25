<?php

return function (PDO $db): void {
    $id = Database::idColumn();
    $dt = Database::datetimeDefault();
    $db->exec("CREATE TABLE IF NOT EXISTS travels (
        {$id},
        title VARCHAR(200) NOT NULL,
        slug VARCHAR(220) NOT NULL UNIQUE,
        location VARCHAR(160) NULL,
        country VARCHAR(100) NULL,
        summary TEXT NULL,
        description TEXT NULL,
        cover_image VARCHAR(255) NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'published',
        featured INTEGER NOT NULL DEFAULT 0,
        created_at {$dt},
        updated_at TEXT NULL
    )");
};

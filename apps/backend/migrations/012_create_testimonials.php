<?php

return function (PDO $db): void {
    $id = Database::idColumn();
    $dt = Database::datetimeDefault();
    $db->exec("CREATE TABLE IF NOT EXISTS testimonials (
        {$id},
        author_name VARCHAR(120) NOT NULL,
        author_role VARCHAR(160) NULL,
        quote TEXT NOT NULL,
        rating INTEGER NOT NULL DEFAULT 5,
        avatar_url VARCHAR(255) NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'published',
        sort_order INTEGER NOT NULL DEFAULT 0,
        created_at {$dt},
        updated_at TEXT NULL
    )");
};

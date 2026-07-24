<?php

return function (PDO $db): void {
    $id = Database::idColumn();
    $dt = Database::datetimeDefault();
    $db->exec("CREATE TABLE IF NOT EXISTS tours (
        {$id},
        travel_id INTEGER NULL,
        title VARCHAR(200) NOT NULL,
        slug VARCHAR(220) NOT NULL UNIQUE,
        description TEXT NULL,
        duration_days INTEGER NULL,
        duration_label VARCHAR(60) NULL,
        price DECIMAL(12,2) NULL DEFAULT 0,
        currency VARCHAR(10) NOT NULL DEFAULT 'KES',
        location VARCHAR(160) NULL,
        inclusions TEXT NULL,
        exclusions TEXT NULL,
        itinerary TEXT NULL,
        cover_image VARCHAR(255) NULL,
        max_guests INTEGER NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'published',
        featured INTEGER NOT NULL DEFAULT 0,
        created_at {$dt},
        updated_at TEXT NULL,
        FOREIGN KEY (travel_id) REFERENCES travels(id) ON DELETE SET NULL
    )");
};

<?php

return function (PDO $db): void {
    $id = Database::idColumn();
    $dt = Database::datetimeDefault();
    $ref = Database::refType();
    $db->exec("CREATE TABLE IF NOT EXISTS bookings (
        {$id},
        client_id {$ref} NOT NULL,
        tour_id {$ref} NULL,
        travel_id {$ref} NULL,
        tracking_code VARCHAR(40) NOT NULL UNIQUE,
        title VARCHAR(200) NOT NULL,
        status VARCHAR(40) NOT NULL DEFAULT 'pending',
        start_date TEXT NULL,
        end_date TEXT NULL,
        guests INTEGER NOT NULL DEFAULT 1,
        amount DECIMAL(12,2) NULL DEFAULT 0,
        currency VARCHAR(10) NOT NULL DEFAULT 'KES',
        notes TEXT NULL,
        admin_notes TEXT NULL,
        progress_percent INTEGER NOT NULL DEFAULT 0,
        created_at {$dt},
        updated_at TEXT NULL,
        FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
        FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE SET NULL,
        FOREIGN KEY (travel_id) REFERENCES travels(id) ON DELETE SET NULL
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS booking_updates (
        {$id},
        booking_id {$ref} NOT NULL,
        title VARCHAR(200) NOT NULL,
        message TEXT NULL,
        status VARCHAR(40) NULL,
        created_by_admin_id {$ref} NULL,
        created_at {$dt},
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
    )");
};

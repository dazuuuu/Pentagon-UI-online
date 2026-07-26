<?php

return function (PDO $db): void {
    $id = Database::idColumn();
    $dt = Database::datetimeDefault();
    $db->exec("CREATE TABLE IF NOT EXISTS password_resets (
        {$id},
        user_type VARCHAR(20) NOT NULL,
        user_id INTEGER NOT NULL,
        email VARCHAR(190) NOT NULL,
        token VARCHAR(64) NOT NULL,
        expires_at TEXT NOT NULL,
        used_at TEXT NULL,
        created_at {$dt}
    )");
};

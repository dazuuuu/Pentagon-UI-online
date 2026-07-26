<?php

return function (PDO $db): void {
    $id = Database::idColumn();
    $dt = Database::datetimeDefault();
    $db->exec("CREATE TABLE IF NOT EXISTS activity_logs (
        {$id},
        actor_type VARCHAR(20) NOT NULL,
        actor_id INTEGER NULL,
        action VARCHAR(120) NOT NULL,
        details TEXT NULL,
        ip_address VARCHAR(60) NULL,
        created_at {$dt}
    )");
};

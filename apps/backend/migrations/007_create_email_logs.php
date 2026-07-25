<?php

return function (PDO $db): void {
    $id = Database::idColumn();
    $dt = Database::datetimeDefault();
    $db->exec("CREATE TABLE IF NOT EXISTS email_logs (
        {$id},
        recipient VARCHAR(190) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        body_html TEXT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'pending',
        error_message TEXT NULL,
        sent_by_admin_id INTEGER NULL,
        related_request_id INTEGER NULL,
        related_booking_id INTEGER NULL,
        created_at {$dt}
    )");
};

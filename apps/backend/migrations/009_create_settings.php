<?php

return function (PDO $db): void {
    $id = Database::idColumn();
    $dt = Database::datetimeDefault();
    $db->exec("CREATE TABLE IF NOT EXISTS settings (
        {$id},
        setting_key VARCHAR(120) NOT NULL UNIQUE,
        setting_value TEXT NULL,
        updated_at {$dt}
    )");

    $now = date('Y-m-d H:i:s');
    $defaults = [
        ['smtp_host', ''],
        ['smtp_port', '587'],
        ['smtp_encryption', 'tls'],
        ['smtp_username', ''],
        ['smtp_password', ''],
        ['smtp_from_email', 'noreply@pentagonquest.com'],
        ['smtp_from_name', 'Pentagon Quest'],
        ['site_contact_email', 'info@pentagonquest.com'],
    ];
    $stmt = Database::isMysql()
        ? $db->prepare('INSERT IGNORE INTO settings (setting_key, setting_value, updated_at) VALUES (?, ?, ?)')
        : $db->prepare('INSERT OR IGNORE INTO settings (setting_key, setting_value, updated_at) VALUES (?, ?, ?)');
    foreach ($defaults as [$k, $v]) {
        $stmt->execute([$k, $v, $now]);
    }
};

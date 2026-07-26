<?php

class Settings
{
    public static function get(string $key, $default = null)
    {
        try {
            $stmt = Database::get()->prepare('SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1');
            $stmt->execute([$key]);
            $val = $stmt->fetchColumn();
            return $val === false ? $default : $val;
        } catch (Throwable $e) {
            return $default;
        }
    }

    public static function set(string $key, ?string $value): void
    {
        $db = Database::get();
        $exists = $db->prepare('SELECT id FROM settings WHERE setting_key = ?');
        $exists->execute([$key]);
        if ($exists->fetch()) {
            $db->prepare('UPDATE settings SET setting_value = ?, updated_at = ? WHERE setting_key = ?')
                ->execute([$value, date('Y-m-d H:i:s'), $key]);
        } else {
            $db->prepare('INSERT INTO settings (setting_key, setting_value, updated_at) VALUES (?, ?, ?)')
                ->execute([$key, $value, date('Y-m-d H:i:s')]);
        }
    }

    public static function many(array $pairs): void
    {
        foreach ($pairs as $k => $v) {
            self::set($k, $v === null ? null : (string)$v);
        }
    }
}

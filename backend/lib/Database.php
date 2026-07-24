<?php

class Database
{
    private static ?PDO $pdo = null;

    public static function get(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $driver = config('db.driver', 'sqlite');

        if ($driver === 'mysql') {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                config('db.host'),
                (int)config('db.port', 3306),
                config('db.database'),
                config('db.charset', 'utf8mb4')
            );
            self::$pdo = new PDO($dsn, config('db.username'), config('db.password'), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } else {
            $path = config('db.sqlite_path');
            $dir = dirname($path);
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            self::$pdo = new PDO('sqlite:' . $path, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            self::$pdo->exec('PRAGMA foreign_keys = ON');
        }

        return self::$pdo;
    }

    public static function driver(): string
    {
        return (string)config('db.driver', 'sqlite');
    }

    public static function isMysql(): bool
    {
        return self::driver() === 'mysql';
    }

    /** Auto-increment / PK helper for migration SQL */
    public static function idColumn(): string
    {
        return self::isMysql()
            ? 'id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY'
            : 'id INTEGER PRIMARY KEY AUTOINCREMENT';
    }

    public static function text(): string
    {
        return self::isMysql() ? 'TEXT' : 'TEXT';
    }

    public static function datetimeDefault(): string
    {
        return self::isMysql()
            ? 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
            : 'TEXT NOT NULL DEFAULT (datetime(\'now\'))';
    }
}

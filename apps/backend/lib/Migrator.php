<?php

class Migrator
{
    private string $path;
    private PDO $db;

    public function __construct(?string $path = null)
    {
        $this->path = $path ?? (__DIR__ . '/../migrations');
        $this->db = Database::get();
        $this->ensureMigrationsTable();
    }

    private function ensureMigrationsTable(): void
    {
        $id = Database::idColumn();
        $dt = Database::datetimeDefault();
        $this->db->exec("CREATE TABLE IF NOT EXISTS migrations (
            {$id},
            migration VARCHAR(255) NOT NULL UNIQUE,
            batch INTEGER NOT NULL,
            ran_at {$dt}
        )");
    }

    public function pending(): array
    {
        $ran = $this->db->query('SELECT migration FROM migrations')->fetchAll(PDO::FETCH_COLUMN);
        $files = glob($this->path . '/*.php') ?: [];
        sort($files);
        $pending = [];
        foreach ($files as $file) {
            $name = basename($file);
            if (!in_array($name, $ran, true)) {
                $pending[] = $name;
            }
        }
        return $pending;
    }

    public function ran(): array
    {
        return $this->db->query('SELECT * FROM migrations ORDER BY id ASC')->fetchAll();
    }

    public function runAll(): array
    {
        $results = [];
        $pending = $this->pending();
        if (!$pending) {
            return [['status' => 'ok', 'message' => 'Nothing to migrate. Database is up to date.']];
        }

        $batch = (int)$this->db->query('SELECT COALESCE(MAX(batch), 0) FROM migrations')->fetchColumn() + 1;

        foreach ($pending as $name) {
            $results[] = $this->runOne($name, $batch);
        }
        return $results;
    }

    public function runOne(string $name, ?int $batch = null): array
    {
        $file = $this->path . '/' . $name;
        if (!is_file($file)) {
            return ['status' => 'error', 'migration' => $name, 'message' => 'File not found'];
        }

        $batch = $batch ?? ((int)$this->db->query('SELECT COALESCE(MAX(batch), 0) FROM migrations')->fetchColumn() + 1);

        try {
            $migration = require $file;
            if (!is_callable($migration)) {
                throw new RuntimeException('Migration must return a callable');
            }
            $this->db->beginTransaction();
            $migration($this->db);
            $this->db->prepare('INSERT INTO migrations (migration, batch, ran_at) VALUES (?, ?, ?)')
                ->execute([$name, $batch, date('Y-m-d H:i:s')]);
            // MySQL implicitly commits (and ends) the transaction on DDL like CREATE TABLE,
            // so only commit if one is still open.
            if ($this->db->inTransaction()) {
                $this->db->commit();
            }
            return ['status' => 'ok', 'migration' => $name, 'message' => 'Migrated successfully'];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['status' => 'error', 'migration' => $name, 'message' => $e->getMessage()];
        }
    }
}

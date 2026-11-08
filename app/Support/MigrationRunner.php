<?php

declare(strict_types=1);

namespace App\Support;

use PDO;
use RuntimeException;

final class MigrationRunner
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly string $path
    ) {
    }

    public function migrate(): array
    {
        $this->ensureTrackingTable();

        $applied = $this->migrated();
        $files = glob($this->path . DIRECTORY_SEPARATOR . '*.php') ?: [];
        sort($files);

        $executed = [];

        foreach ($files as $file) {
            $migration = basename($file);

            if (in_array($migration, $applied, true)) {
                continue;
            }

            $instance = $this->load($file);
            $instance->up($this->pdo);

            $statement = $this->pdo->prepare(
                'INSERT INTO schema_migrations (migration, applied_at) VALUES (:migration, :applied_at)'
            );
            $statement->execute([
                'migration' => $migration,
                'applied_at' => date('Y-m-d H:i:s'),
            ]);

            $executed[] = $migration;
        }

        return $executed;
    }

    public function rollback(int $steps = 1): array
    {
        $this->ensureTrackingTable();

        $steps = max(1, $steps);

        $statement = $this->pdo->query(
            'SELECT migration FROM schema_migrations ORDER BY applied_at DESC, migration DESC LIMIT ' . $steps
        );
        $migrations = $statement->fetchAll(PDO::FETCH_COLUMN);

        $rolledBack = [];

        foreach (array_reverse($migrations) as $migration) {
            $file = $this->path . DIRECTORY_SEPARATOR . $migration;

            if (!is_file($file)) {
                throw new RuntimeException(sprintf('Arquivo da migration "%s" não encontrado.', $migration));
            }

            $instance = $this->load($file);
            $instance->down($this->pdo);

            $delete = $this->pdo->prepare('DELETE FROM schema_migrations WHERE migration = :migration');
            $delete->execute(['migration' => $migration]);

            $rolledBack[] = $migration;
        }

        return $rolledBack;
    }

    public function migrated(): array
    {
        $this->ensureTrackingTable();

        $statement = $this->pdo->query('SELECT migration FROM schema_migrations ORDER BY migration ASC');

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    private function ensureTrackingTable(): void
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS schema_migrations ('
            . ' migration VARCHAR(191) NOT NULL PRIMARY KEY,'
            . ' applied_at DATETIME NOT NULL'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    private function load(string $file): object
    {
        $instance = require $file;

        if (!is_object($instance) || !method_exists($instance, 'up') || !method_exists($instance, 'down')) {
            throw new RuntimeException(sprintf('Migration inválida: "%s".', basename($file)));
        }

        return $instance;
    }
}

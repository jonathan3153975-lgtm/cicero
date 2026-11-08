<?php

declare(strict_types=1);

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS health_checks ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' checked_at DATETIME NOT NULL,'
            . ' note VARCHAR(255) NULL,'
            . ' PRIMARY KEY (id),'
            . ' INDEX idx_health_checks_checked_at (checked_at)'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS health_checks');
    }
};

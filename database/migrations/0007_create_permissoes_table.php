<?php

declare(strict_types=1);

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS permissoes ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' slug VARCHAR(120) NOT NULL,'
            . ' modulo VARCHAR(60) NOT NULL,'
            . ' acao VARCHAR(60) NOT NULL,'
            . ' descricao VARCHAR(255) NULL,'
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' UNIQUE KEY uq_permissoes_slug (slug),'
            . ' KEY idx_permissoes_modulo (modulo)'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS permissoes');
    }
};

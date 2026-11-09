<?php

declare(strict_types=1);

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS perfis ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' slug VARCHAR(80) NOT NULL,'
            . ' nome VARCHAR(120) NOT NULL,'
            . ' nivel SMALLINT UNSIGNED NOT NULL,'
            . ' escopo VARCHAR(20) NOT NULL DEFAULT \'UNIDADE\','
            . ' sistema TINYINT(1) NOT NULL DEFAULT 0,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' UNIQUE KEY uq_perfis_slug (slug),'
            . ' KEY idx_perfis_nivel (nivel),'
            . ' KEY idx_perfis_status (status)'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS perfis');
    }
};

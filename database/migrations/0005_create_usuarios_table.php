<?php

declare(strict_types=1);

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS usuarios ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' nome VARCHAR(180) NOT NULL,'
            . ' email VARCHAR(180) NOT NULL,'
            . ' senha_hash VARCHAR(255) NOT NULL,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' ultimo_login_em DATETIME NULL,'
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' UNIQUE KEY uq_usuarios_email (email),'
            . ' KEY idx_usuarios_status (status)'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS usuarios');
    }
};

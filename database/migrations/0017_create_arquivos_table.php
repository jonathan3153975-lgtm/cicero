<?php

declare(strict_types=1);

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS arquivos ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' public_id CHAR(26) NOT NULL,'
            . ' disco VARCHAR(30) NOT NULL DEFAULT \'server\','
            . ' caminho VARCHAR(500) NOT NULL,'
            . ' nome_original VARCHAR(255) NOT NULL,'
            . ' mime VARCHAR(120) NULL,'
            . ' tamanho BIGINT UNSIGNED NULL,'
            . ' hash VARCHAR(64) NULL,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' enviado_por BIGINT UNSIGNED NULL,'
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' UNIQUE KEY uq_arquivos_public_id (public_id),'
            . ' KEY idx_arquivos_disco (disco),'
            . ' KEY idx_arquivos_status (status),'
            . ' KEY idx_arquivos_enviado_por (enviado_por),'
            . ' CONSTRAINT fk_arquivos_usuario FOREIGN KEY (enviado_por) REFERENCES usuarios (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS arquivos');
    }
};

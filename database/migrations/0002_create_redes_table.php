<?php

declare(strict_types=1);

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS redes ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' razao_social VARCHAR(180) NOT NULL,'
            . ' nome_fantasia VARCHAR(180) NOT NULL,'
            . ' cnpj VARCHAR(14) NOT NULL,'
            . ' email VARCHAR(180) NULL,'
            . ' telefone VARCHAR(30) NULL,'
            . ' cep VARCHAR(9) NULL,'
            . ' logradouro VARCHAR(180) NULL,'
            . ' numero VARCHAR(20) NULL,'
            . ' complemento VARCHAR(120) NULL,'
            . ' bairro VARCHAR(120) NULL,'
            . ' cidade VARCHAR(120) NULL,'
            . ' estado CHAR(2) NULL,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVA\','
            . ' plano_contratado VARCHAR(80) NULL,'
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' UNIQUE KEY uq_redes_cnpj (cnpj),'
            . ' KEY idx_redes_status (status)'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS redes');
    }
};

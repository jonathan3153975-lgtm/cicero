<?php

declare(strict_types=1);

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS unidades ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' rede_id BIGINT UNSIGNED NOT NULL,'
            . ' nome VARCHAR(180) NOT NULL,'
            . ' codigo_interno VARCHAR(40) NOT NULL,'
            . ' situacao VARCHAR(20) NOT NULL DEFAULT \'ATIVA\','
            . ' cep VARCHAR(9) NULL,'
            . ' logradouro VARCHAR(180) NULL,'
            . ' numero VARCHAR(20) NULL,'
            . ' complemento VARCHAR(120) NULL,'
            . ' bairro VARCHAR(120) NULL,'
            . ' cidade VARCHAR(120) NULL,'
            . ' estado CHAR(2) NULL,'
            . ' telefone VARCHAR(30) NULL,'
            . ' whatsapp VARCHAR(30) NULL,'
            . ' email VARCHAR(180) NULL,'
            . ' data_abertura DATE NULL,'
            . ' horario_funcionamento VARCHAR(120) NULL,'
            . ' capacidade_maxima INT UNSIGNED NULL,'
            . ' metragem DECIMAL(10,2) NULL,'
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' UNIQUE KEY uq_unidades_rede_codigo (rede_id, codigo_interno),'
            . ' KEY idx_unidades_situacao (situacao),'
            . ' CONSTRAINT fk_unidades_rede FOREIGN KEY (rede_id) REFERENCES redes (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS unidades');
    }
};

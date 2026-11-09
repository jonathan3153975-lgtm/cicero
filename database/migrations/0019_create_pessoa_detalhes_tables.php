<?php

declare(strict_types=1);

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS pessoa_enderecos ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' pessoa_id BIGINT UNSIGNED NOT NULL,'
            . ' tipo VARCHAR(30) NOT NULL DEFAULT \'RESIDENCIAL\','
            . ' cep VARCHAR(9) NULL,'
            . ' logradouro VARCHAR(180) NULL,'
            . ' numero VARCHAR(20) NULL,'
            . ' complemento VARCHAR(120) NULL,'
            . ' bairro VARCHAR(120) NULL,'
            . ' cidade VARCHAR(120) NULL,'
            . ' estado CHAR(2) NULL,'
            . ' pais VARCHAR(60) NOT NULL DEFAULT \'Brasil\','
            . ' principal TINYINT(1) NOT NULL DEFAULT 0,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' KEY idx_pessoa_enderecos_pessoa (pessoa_id),'
            . ' KEY idx_pessoa_enderecos_principal (pessoa_id, principal),'
            . ' CONSTRAINT fk_pessoa_enderecos_pessoa FOREIGN KEY (pessoa_id) REFERENCES pessoas (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS pessoa_contatos ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' pessoa_id BIGINT UNSIGNED NOT NULL,'
            . ' tipo VARCHAR(30) NOT NULL,'
            . ' valor VARCHAR(180) NOT NULL,'
            . ' principal TINYINT(1) NOT NULL DEFAULT 0,'
            . ' verificado_em DATETIME NULL,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' KEY idx_pessoa_contatos_pessoa (pessoa_id),'
            . ' KEY idx_pessoa_contatos_tipo (tipo),'
            . ' KEY idx_pessoa_contatos_principal (pessoa_id, principal),'
            . ' CONSTRAINT fk_pessoa_contatos_pessoa FOREIGN KEY (pessoa_id) REFERENCES pessoas (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS pessoa_documentos ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' pessoa_id BIGINT UNSIGNED NOT NULL,'
            . ' tipo VARCHAR(40) NOT NULL,'
            . ' numero VARCHAR(80) NULL,'
            . ' orgao_emissor VARCHAR(80) NULL,'
            . ' uf_emissor CHAR(2) NULL,'
            . ' data_emissao DATE NULL,'
            . ' validade DATE NULL,'
            . ' arquivo_id BIGINT UNSIGNED NULL,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' KEY idx_pessoa_documentos_pessoa (pessoa_id),'
            . ' KEY idx_pessoa_documentos_tipo (tipo),'
            . ' CONSTRAINT fk_pessoa_documentos_pessoa FOREIGN KEY (pessoa_id) REFERENCES pessoas (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_pessoa_documentos_arquivo FOREIGN KEY (arquivo_id) REFERENCES arquivos (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS pessoa_documentos');
        $pdo->exec('DROP TABLE IF EXISTS pessoa_contatos');
        $pdo->exec('DROP TABLE IF EXISTS pessoa_enderecos');
    }
};

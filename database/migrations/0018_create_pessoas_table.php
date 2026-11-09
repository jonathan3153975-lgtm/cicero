<?php

declare(strict_types=1);

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS pessoas ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' rede_id BIGINT UNSIGNED NULL,'
            . ' public_id CHAR(26) NOT NULL,'
            . ' tipo VARCHAR(10) NOT NULL DEFAULT \'PF\','
            . ' nome VARCHAR(180) NOT NULL,'
            . ' nome_social VARCHAR(180) NULL,'
            . ' cpf VARCHAR(11) NULL,'
            . ' cnpj VARCHAR(14) NULL,'
            . ' data_nascimento DATE NULL,'
            . ' sexo VARCHAR(20) NULL,'
            . ' estado_civil VARCHAR(30) NULL,'
            . ' email_principal VARCHAR(180) NULL,'
            . ' telefone_principal VARCHAR(30) NULL,'
            . ' foto_arquivo_id BIGINT UNSIGNED NULL,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' observacoes TEXT NULL,'
            . ' created_by BIGINT UNSIGNED NULL,'
            . ' updated_by BIGINT UNSIGNED NULL,'
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' UNIQUE KEY uq_pessoas_public_id (public_id),'
            . ' UNIQUE KEY uq_pessoas_cpf (cpf),'
            . ' UNIQUE KEY uq_pessoas_cnpj (cnpj),'
            . ' KEY idx_pessoas_rede (rede_id),'
            . ' KEY idx_pessoas_nome (nome),'
            . ' KEY idx_pessoas_status (status),'
            . ' KEY idx_pessoas_email (email_principal),'
            . ' CONSTRAINT fk_pessoas_rede FOREIGN KEY (rede_id) REFERENCES redes (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_pessoas_foto FOREIGN KEY (foto_arquivo_id) REFERENCES arquivos (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_pessoas_created_by FOREIGN KEY (created_by) REFERENCES usuarios (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_pessoas_updated_by FOREIGN KEY (updated_by) REFERENCES usuarios (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS pessoas');
    }
};

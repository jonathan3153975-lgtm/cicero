<?php

declare(strict_types=1);

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS alunos ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' public_id CHAR(26) NOT NULL,'
            . ' pessoa_id BIGINT UNSIGNED NOT NULL,'
            . ' rede_id BIGINT UNSIGNED NOT NULL,'
            . ' unidade_id BIGINT UNSIGNED NOT NULL,'
            . ' codigo VARCHAR(40) NULL,'
            . ' data_entrada DATE NULL,'
            . ' como_conheceu VARCHAR(80) NULL,'
            . ' indicado_por_pessoa_id BIGINT UNSIGNED NULL,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' observacoes TEXT NULL,'
            . ' created_by BIGINT UNSIGNED NULL,'
            . ' updated_by BIGINT UNSIGNED NULL,'
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' UNIQUE KEY uq_alunos_public_id (public_id),'
            . ' UNIQUE KEY uq_alunos_pessoa (pessoa_id),'
            . ' UNIQUE KEY uq_alunos_rede_codigo (rede_id, codigo),'
            . ' KEY idx_alunos_rede (rede_id),'
            . ' KEY idx_alunos_unidade (unidade_id),'
            . ' KEY idx_alunos_status (status),'
            . ' CONSTRAINT fk_alunos_pessoa FOREIGN KEY (pessoa_id) REFERENCES pessoas (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_alunos_rede FOREIGN KEY (rede_id) REFERENCES redes (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_alunos_unidade FOREIGN KEY (unidade_id) REFERENCES unidades (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_alunos_indicado FOREIGN KEY (indicado_por_pessoa_id) REFERENCES pessoas (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_alunos_created_by FOREIGN KEY (created_by) REFERENCES usuarios (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_alunos_updated_by FOREIGN KEY (updated_by) REFERENCES usuarios (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS aluno_responsaveis ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' aluno_id BIGINT UNSIGNED NOT NULL,'
            . ' responsavel_pessoa_id BIGINT UNSIGNED NOT NULL,'
            . ' parentesco VARCHAR(60) NULL,'
            . ' responsavel_financeiro TINYINT(1) NOT NULL DEFAULT 0,'
            . ' autorizado_buscar TINYINT(1) NOT NULL DEFAULT 0,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' UNIQUE KEY uq_aluno_responsavel (aluno_id, responsavel_pessoa_id),'
            . ' KEY idx_aluno_responsaveis_pessoa (responsavel_pessoa_id),'
            . ' CONSTRAINT fk_aluno_responsaveis_aluno FOREIGN KEY (aluno_id) REFERENCES alunos (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_aluno_responsaveis_pessoa FOREIGN KEY (responsavel_pessoa_id) REFERENCES pessoas (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS aluno_restricoes_saude ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' aluno_id BIGINT UNSIGNED NOT NULL,'
            . ' tipo VARCHAR(60) NOT NULL,'
            . ' descricao TEXT NOT NULL,'
            . ' gravidade VARCHAR(20) NULL,'
            . ' orientacao TEXT NULL,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' created_by BIGINT UNSIGNED NULL,'
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' KEY idx_aluno_restricoes_aluno (aluno_id),'
            . ' CONSTRAINT fk_aluno_restricoes_aluno FOREIGN KEY (aluno_id) REFERENCES alunos (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_aluno_restricoes_created_by FOREIGN KEY (created_by) REFERENCES usuarios (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS aluno_restricoes_saude');
        $pdo->exec('DROP TABLE IF EXISTS aluno_responsaveis');
        $pdo->exec('DROP TABLE IF EXISTS alunos');
    }
};

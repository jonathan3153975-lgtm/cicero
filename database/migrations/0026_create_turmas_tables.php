<?php

declare(strict_types=1);

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS turmas ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' rede_id BIGINT UNSIGNED NOT NULL,'
            . ' unidade_id BIGINT UNSIGNED NOT NULL,'
            . ' modalidade_id BIGINT UNSIGNED NULL,'
            . ' nome VARCHAR(120) NOT NULL,'
            . ' capacidade SMALLINT UNSIGNED NULL,'
            . ' sala VARCHAR(80) NULL,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVA\','
            . ' created_by BIGINT UNSIGNED NULL,'
            . ' updated_by BIGINT UNSIGNED NULL,'
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' KEY idx_turmas_rede (rede_id),'
            . ' KEY idx_turmas_unidade (unidade_id),'
            . ' KEY idx_turmas_status (status),'
            . ' CONSTRAINT fk_turmas_rede FOREIGN KEY (rede_id) REFERENCES redes (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_turmas_unidade FOREIGN KEY (unidade_id) REFERENCES unidades (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_turmas_modalidade FOREIGN KEY (modalidade_id) REFERENCES modalidades (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_turmas_created_by FOREIGN KEY (created_by) REFERENCES usuarios (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_turmas_updated_by FOREIGN KEY (updated_by) REFERENCES usuarios (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS turma_horarios ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' turma_id BIGINT UNSIGNED NOT NULL,'
            . ' dia_semana TINYINT UNSIGNED NOT NULL,'
            . ' hora_inicio TIME NOT NULL,'
            . ' hora_fim TIME NOT NULL,'
            . ' professor_pessoa_id BIGINT UNSIGNED NULL,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' KEY idx_turma_horarios_turma (turma_id),'
            . ' KEY idx_turma_horarios_dia (dia_semana),'
            . ' CONSTRAINT fk_turma_horarios_turma FOREIGN KEY (turma_id) REFERENCES turmas (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_turma_horarios_professor FOREIGN KEY (professor_pessoa_id) REFERENCES pessoas (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS turma_alunos ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' turma_id BIGINT UNSIGNED NOT NULL,'
            . ' matricula_id BIGINT UNSIGNED NOT NULL,'
            . ' data_entrada DATE NOT NULL,'
            . ' data_saida DATE NULL,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' UNIQUE KEY uq_turma_alunos (turma_id, matricula_id),'
            . ' KEY idx_turma_alunos_matricula (matricula_id),'
            . ' CONSTRAINT fk_turma_alunos_turma FOREIGN KEY (turma_id) REFERENCES turmas (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_turma_alunos_matricula FOREIGN KEY (matricula_id) REFERENCES matriculas (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS turma_alunos');
        $pdo->exec('DROP TABLE IF EXISTS turma_horarios');
        $pdo->exec('DROP TABLE IF EXISTS turmas');
    }
};

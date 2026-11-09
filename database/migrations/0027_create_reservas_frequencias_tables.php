<?php

declare(strict_types=1);

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS reservas ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' rede_id BIGINT UNSIGNED NOT NULL,'
            . ' unidade_id BIGINT UNSIGNED NOT NULL,'
            . ' aluno_id BIGINT UNSIGNED NOT NULL,'
            . ' turma_horario_id BIGINT UNSIGNED NOT NULL,'
            . ' data DATE NOT NULL,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'CONFIRMADA\','
            . ' observacao VARCHAR(255) NULL,'
            . ' created_by BIGINT UNSIGNED NULL,'
            . ' updated_by BIGINT UNSIGNED NULL,'
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' KEY idx_reservas_rede (rede_id),'
            . ' KEY idx_reservas_unidade (unidade_id),'
            . ' KEY idx_reservas_aluno (aluno_id),'
            . ' KEY idx_reservas_horario_data (turma_horario_id, data),'
            . ' KEY idx_reservas_status (status),'
            . ' CONSTRAINT fk_reservas_rede FOREIGN KEY (rede_id) REFERENCES redes (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_reservas_unidade FOREIGN KEY (unidade_id) REFERENCES unidades (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_reservas_aluno FOREIGN KEY (aluno_id) REFERENCES alunos (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_reservas_horario FOREIGN KEY (turma_horario_id) REFERENCES turma_horarios (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_reservas_created_by FOREIGN KEY (created_by) REFERENCES usuarios (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_reservas_updated_by FOREIGN KEY (updated_by) REFERENCES usuarios (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS frequencias ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' rede_id BIGINT UNSIGNED NOT NULL,'
            . ' unidade_id BIGINT UNSIGNED NOT NULL,'
            . ' aluno_id BIGINT UNSIGNED NOT NULL,'
            . ' turma_id BIGINT UNSIGNED NOT NULL,'
            . ' turma_horario_id BIGINT UNSIGNED NULL,'
            . ' data DATE NOT NULL,'
            . ' presenca TINYINT(1) NOT NULL DEFAULT 1,'
            . ' origem VARCHAR(20) NOT NULL DEFAULT \'MANUAL\','
            . ' registrado_por BIGINT UNSIGNED NULL,'
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' KEY idx_frequencias_rede (rede_id),'
            . ' KEY idx_frequencias_unidade (unidade_id),'
            . ' KEY idx_frequencias_aluno (aluno_id),'
            . ' KEY idx_frequencias_turma_data (turma_id, data),'
            . ' KEY idx_frequencias_data (data),'
            . ' CONSTRAINT fk_frequencias_rede FOREIGN KEY (rede_id) REFERENCES redes (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_frequencias_unidade FOREIGN KEY (unidade_id) REFERENCES unidades (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_frequencias_aluno FOREIGN KEY (aluno_id) REFERENCES alunos (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_frequencias_turma FOREIGN KEY (turma_id) REFERENCES turmas (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_frequencias_horario FOREIGN KEY (turma_horario_id) REFERENCES turma_horarios (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_frequencias_registrado_por FOREIGN KEY (registrado_por) REFERENCES usuarios (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS frequencias');
        $pdo->exec('DROP TABLE IF EXISTS reservas');
    }
};

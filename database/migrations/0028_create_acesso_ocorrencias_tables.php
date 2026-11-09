<?php

declare(strict_types=1);

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS dispositivos_acesso ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' rede_id BIGINT UNSIGNED NOT NULL,'
            . ' unidade_id BIGINT UNSIGNED NOT NULL,'
            . ' nome VARCHAR(120) NOT NULL,'
            . ' tipo VARCHAR(40) NOT NULL DEFAULT \'CATRACA\','
            . ' identificador VARCHAR(120) NOT NULL,'
            . ' direcao VARCHAR(20) NOT NULL DEFAULT \'AMBOS\','
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' ultima_comunicacao_em DATETIME NULL,'
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' UNIQUE KEY uq_dispositivos_identificador (identificador),'
            . ' KEY idx_dispositivos_rede (rede_id),'
            . ' KEY idx_dispositivos_unidade (unidade_id),'
            . ' KEY idx_dispositivos_status (status),'
            . ' CONSTRAINT fk_dispositivos_rede FOREIGN KEY (rede_id) REFERENCES redes (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_dispositivos_unidade FOREIGN KEY (unidade_id) REFERENCES unidades (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS eventos_acesso ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' dispositivo_id BIGINT UNSIGNED NOT NULL,'
            . ' rede_id BIGINT UNSIGNED NOT NULL,'
            . ' unidade_id BIGINT UNSIGNED NOT NULL,'
            . ' pessoa_id BIGINT UNSIGNED NOT NULL,'
            . ' aluno_id BIGINT UNSIGNED NULL,'
            . ' tipo VARCHAR(20) NOT NULL,'
            . ' ocorrido_em DATETIME NOT NULL,'
            . ' autorizado TINYINT(1) NOT NULL DEFAULT 1,'
            . ' motivo_negacao VARCHAR(120) NULL,'
            . ' payload_json JSON NULL,'
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' KEY idx_eventos_acesso_dispositivo (dispositivo_id),'
            . ' KEY idx_eventos_acesso_rede (rede_id),'
            . ' KEY idx_eventos_acesso_unidade (unidade_id),'
            . ' KEY idx_eventos_acesso_pessoa (pessoa_id),'
            . ' KEY idx_eventos_acesso_ocorrido (ocorrido_em),'
            . ' CONSTRAINT fk_eventos_acesso_dispositivo FOREIGN KEY (dispositivo_id) REFERENCES dispositivos_acesso (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_eventos_acesso_rede FOREIGN KEY (rede_id) REFERENCES redes (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_eventos_acesso_unidade FOREIGN KEY (unidade_id) REFERENCES unidades (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_eventos_acesso_pessoa FOREIGN KEY (pessoa_id) REFERENCES pessoas (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_eventos_acesso_aluno FOREIGN KEY (aluno_id) REFERENCES alunos (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS ocorrencias ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' rede_id BIGINT UNSIGNED NOT NULL,'
            . ' unidade_id BIGINT UNSIGNED NOT NULL,'
            . ' aluno_id BIGINT UNSIGNED NULL,'
            . ' pessoa_id BIGINT UNSIGNED NULL,'
            . ' tipo VARCHAR(60) NOT NULL,'
            . ' descricao TEXT NOT NULL,'
            . ' data DATE NOT NULL,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ABERTA\','
            . ' arquivo_id BIGINT UNSIGNED NULL,'
            . ' created_by BIGINT UNSIGNED NULL,'
            . ' updated_by BIGINT UNSIGNED NULL,'
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' KEY idx_ocorrencias_rede (rede_id),'
            . ' KEY idx_ocorrencias_unidade (unidade_id),'
            . ' KEY idx_ocorrencias_aluno (aluno_id),'
            . ' KEY idx_ocorrencias_data (data),'
            . ' KEY idx_ocorrencias_status (status),'
            . ' CONSTRAINT fk_ocorrencias_rede FOREIGN KEY (rede_id) REFERENCES redes (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_ocorrencias_unidade FOREIGN KEY (unidade_id) REFERENCES unidades (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_ocorrencias_aluno FOREIGN KEY (aluno_id) REFERENCES alunos (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_ocorrencias_pessoa FOREIGN KEY (pessoa_id) REFERENCES pessoas (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_ocorrencias_arquivo FOREIGN KEY (arquivo_id) REFERENCES arquivos (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_ocorrencias_created_by FOREIGN KEY (created_by) REFERENCES usuarios (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_ocorrencias_updated_by FOREIGN KEY (updated_by) REFERENCES usuarios (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS ocorrencias');
        $pdo->exec('DROP TABLE IF EXISTS eventos_acesso');
        $pdo->exec('DROP TABLE IF EXISTS dispositivos_acesso');
    }
};

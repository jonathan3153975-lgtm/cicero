<?php

declare(strict_types=1);

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS modalidades ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' rede_id BIGINT UNSIGNED NULL,'
            . ' nome VARCHAR(120) NOT NULL,'
            . ' descricao TEXT NULL,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' KEY idx_modalidades_rede (rede_id),'
            . ' KEY idx_modalidades_status (status),'
            . ' CONSTRAINT fk_modalidades_rede FOREIGN KEY (rede_id) REFERENCES redes (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS planos ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' rede_id BIGINT UNSIGNED NULL,'
            . ' public_id CHAR(26) NOT NULL,'
            . ' nome VARCHAR(120) NOT NULL,'
            . ' descricao TEXT NULL,'
            . ' tipo VARCHAR(30) NOT NULL DEFAULT \'MENSAL\','
            . ' duracao_meses SMALLINT UNSIGNED NULL,'
            . ' aulas_semana SMALLINT UNSIGNED NULL,'
            . ' limite_modalidades SMALLINT UNSIGNED NULL,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' created_by BIGINT UNSIGNED NULL,'
            . ' updated_by BIGINT UNSIGNED NULL,'
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' UNIQUE KEY uq_planos_public_id (public_id),'
            . ' KEY idx_planos_rede (rede_id),'
            . ' KEY idx_planos_status (status),'
            . ' CONSTRAINT fk_planos_rede FOREIGN KEY (rede_id) REFERENCES redes (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_planos_created_by FOREIGN KEY (created_by) REFERENCES usuarios (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_planos_updated_by FOREIGN KEY (updated_by) REFERENCES usuarios (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS plano_modalidades ('
            . ' plano_id BIGINT UNSIGNED NOT NULL,'
            . ' modalidade_id BIGINT UNSIGNED NOT NULL,'
            . ' aulas_semana SMALLINT UNSIGNED NULL,'
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (plano_id, modalidade_id),'
            . ' KEY idx_plano_modalidades_modalidade (modalidade_id),'
            . ' CONSTRAINT fk_plano_modalidades_plano FOREIGN KEY (plano_id) REFERENCES planos (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_plano_modalidades_modalidade FOREIGN KEY (modalidade_id) REFERENCES modalidades (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS plano_unidade_precos ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' plano_id BIGINT UNSIGNED NOT NULL,'
            . ' rede_id BIGINT UNSIGNED NOT NULL,'
            . ' unidade_id BIGINT UNSIGNED NOT NULL,'
            . ' vigencia_inicio DATE NOT NULL,'
            . ' vigencia_fim DATE NULL,'
            . ' valor_matricula DECIMAL(15,2) NOT NULL DEFAULT 0,'
            . ' valor_mensal DECIMAL(15,2) NOT NULL DEFAULT 0,'
            . ' valor_adesao DECIMAL(15,2) NULL,'
            . ' taxa_administrativa DECIMAL(15,2) NULL,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' created_by BIGINT UNSIGNED NULL,'
            . ' updated_by BIGINT UNSIGNED NULL,'
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' UNIQUE KEY uq_plano_unidade_vigencia (plano_id, unidade_id, vigencia_inicio),'
            . ' KEY idx_plano_unidade_precos_rede (rede_id),'
            . ' KEY idx_plano_unidade_precos_unidade (unidade_id),'
            . ' KEY idx_plano_unidade_precos_status (status),'
            . ' CONSTRAINT fk_plano_unidade_precos_plano FOREIGN KEY (plano_id) REFERENCES planos (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_plano_unidade_precos_rede FOREIGN KEY (rede_id) REFERENCES redes (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_plano_unidade_precos_unidade FOREIGN KEY (unidade_id) REFERENCES unidades (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_plano_unidade_precos_created_by FOREIGN KEY (created_by) REFERENCES usuarios (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_plano_unidade_precos_updated_by FOREIGN KEY (updated_by) REFERENCES usuarios (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS plano_unidade_precos');
        $pdo->exec('DROP TABLE IF EXISTS plano_modalidades');
        $pdo->exec('DROP TABLE IF EXISTS planos');
        $pdo->exec('DROP TABLE IF EXISTS modalidades');
    }
};

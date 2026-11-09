<?php

declare(strict_types=1);

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS servicos_adicionais ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' rede_id BIGINT UNSIGNED NULL,'
            . ' nome VARCHAR(120) NOT NULL,'
            . ' descricao TEXT NULL,'
            . ' tipo_cobranca VARCHAR(30) NOT NULL DEFAULT \'MENSAL\','
            . ' valor_padrao DECIMAL(15,2) NULL,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' KEY idx_servicos_adicionais_rede (rede_id),'
            . ' KEY idx_servicos_adicionais_status (status),'
            . ' CONSTRAINT fk_servicos_adicionais_rede FOREIGN KEY (rede_id) REFERENCES redes (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS promocoes ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' rede_id BIGINT UNSIGNED NOT NULL,'
            . ' unidade_id BIGINT UNSIGNED NULL,'
            . ' nome VARCHAR(120) NOT NULL,'
            . ' tipo_desconto VARCHAR(30) NOT NULL,'
            . ' valor DECIMAL(15,2) NULL,'
            . ' percentual DECIMAL(7,4) NULL,'
            . ' vigencia_inicio DATE NOT NULL,'
            . ' vigencia_fim DATE NULL,'
            . ' condicoes_json JSON NULL,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' KEY idx_promocoes_rede (rede_id),'
            . ' KEY idx_promocoes_unidade (unidade_id),'
            . ' KEY idx_promocoes_vigencia (vigencia_inicio, vigencia_fim),'
            . ' CONSTRAINT fk_promocoes_rede FOREIGN KEY (rede_id) REFERENCES redes (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_promocoes_unidade FOREIGN KEY (unidade_id) REFERENCES unidades (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS promocoes');
        $pdo->exec('DROP TABLE IF EXISTS servicos_adicionais');
    }
};

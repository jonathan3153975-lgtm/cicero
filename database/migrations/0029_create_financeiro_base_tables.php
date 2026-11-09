<?php

declare(strict_types=1);

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS contas_bancarias ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' rede_id BIGINT UNSIGNED NOT NULL,'
            . ' unidade_id BIGINT UNSIGNED NOT NULL,'
            . ' banco VARCHAR(120) NOT NULL,'
            . ' agencia VARCHAR(20) NULL,'
            . ' conta VARCHAR(40) NULL,'
            . ' tipo VARCHAR(30) NOT NULL DEFAULT \'CORRENTE\','
            . ' saldo_inicial DECIMAL(15,2) NOT NULL DEFAULT 0,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' UNIQUE KEY uq_contas_bancarias (unidade_id, banco, agencia, conta),'
            . ' KEY idx_contas_bancarias_rede (rede_id),'
            . ' KEY idx_contas_bancarias_unidade (unidade_id),'
            . ' KEY idx_contas_bancarias_status (status),'
            . ' CONSTRAINT fk_contas_bancarias_rede FOREIGN KEY (rede_id) REFERENCES redes (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_contas_bancarias_unidade FOREIGN KEY (unidade_id) REFERENCES unidades (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS formas_pagamento ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' rede_id BIGINT UNSIGNED NULL,'
            . ' nome VARCHAR(80) NOT NULL,'
            . ' tipo VARCHAR(30) NOT NULL,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' KEY idx_formas_pagamento_rede (rede_id),'
            . ' KEY idx_formas_pagamento_status (status),'
            . ' CONSTRAINT fk_formas_pagamento_rede FOREIGN KEY (rede_id) REFERENCES redes (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS centros_custo ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' rede_id BIGINT UNSIGNED NOT NULL,'
            . ' unidade_id BIGINT UNSIGNED NOT NULL,'
            . ' codigo VARCHAR(30) NOT NULL,'
            . ' nome VARCHAR(120) NOT NULL,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' UNIQUE KEY uq_centros_custo_codigo (unidade_id, codigo),'
            . ' KEY idx_centros_custo_rede (rede_id),'
            . ' KEY idx_centros_custo_status (status),'
            . ' CONSTRAINT fk_centros_custo_rede FOREIGN KEY (rede_id) REFERENCES redes (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_centros_custo_unidade FOREIGN KEY (unidade_id) REFERENCES unidades (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS fornecedores ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' rede_id BIGINT UNSIGNED NOT NULL,'
            . ' pessoa_id BIGINT UNSIGNED NOT NULL,'
            . ' nome_fantasia VARCHAR(180) NULL,'
            . ' categoria VARCHAR(80) NULL,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' UNIQUE KEY uq_fornecedores_rede_pessoa (rede_id, pessoa_id),'
            . ' KEY idx_fornecedores_rede (rede_id),'
            . ' KEY idx_fornecedores_status (status),'
            . ' CONSTRAINT fk_fornecedores_rede FOREIGN KEY (rede_id) REFERENCES redes (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_fornecedores_pessoa FOREIGN KEY (pessoa_id) REFERENCES pessoas (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS fornecedores');
        $pdo->exec('DROP TABLE IF EXISTS centros_custo');
        $pdo->exec('DROP TABLE IF EXISTS formas_pagamento');
        $pdo->exec('DROP TABLE IF EXISTS contas_bancarias');
    }
};

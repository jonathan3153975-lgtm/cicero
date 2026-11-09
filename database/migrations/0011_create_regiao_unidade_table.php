<?php

declare(strict_types=1);

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS regiao_unidade ('
            . ' regiao_id BIGINT UNSIGNED NOT NULL,'
            . ' unidade_id BIGINT UNSIGNED NOT NULL,'
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (regiao_id, unidade_id),'
            . ' KEY idx_regiao_unidade_unidade (unidade_id),'
            . ' CONSTRAINT fk_regiao_unidade_regiao FOREIGN KEY (regiao_id) REFERENCES regioes (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_regiao_unidade_unidade FOREIGN KEY (unidade_id) REFERENCES unidades (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS regiao_unidade');
    }
};

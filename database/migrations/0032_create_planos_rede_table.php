<?php

declare(strict_types=1);

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS planos_rede ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' nome VARCHAR(120) NOT NULL,'
            . ' descricao LONGTEXT NULL,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' created_by BIGINT UNSIGNED NULL,'
            . ' updated_by BIGINT UNSIGNED NULL,'
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' UNIQUE KEY uq_planos_rede_nome (nome),'
            . ' KEY idx_planos_rede_status (status),'
            . ' CONSTRAINT fk_planos_rede_created_by FOREIGN KEY (created_by) REFERENCES usuarios (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_planos_rede_updated_by FOREIGN KEY (updated_by) REFERENCES usuarios (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $pdo->exec(
            'ALTER TABLE redes ADD COLUMN plano_rede_id BIGINT UNSIGNED NULL AFTER plano_contratado,'
            . ' ADD KEY idx_redes_plano_rede (plano_rede_id),'
            . ' ADD CONSTRAINT fk_redes_plano_rede FOREIGN KEY (plano_rede_id) REFERENCES planos_rede (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE'
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('ALTER TABLE redes DROP FOREIGN KEY fk_redes_plano_rede');
        $pdo->exec('ALTER TABLE redes DROP KEY idx_redes_plano_rede');
        $pdo->exec('ALTER TABLE redes DROP COLUMN plano_rede_id');
        $pdo->exec('DROP TABLE IF EXISTS planos_rede');
    }
};

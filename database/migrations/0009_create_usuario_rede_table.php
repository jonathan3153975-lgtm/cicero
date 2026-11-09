<?php

declare(strict_types=1);

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS usuario_rede ('
            . ' usuario_id BIGINT UNSIGNED NOT NULL,'
            . ' rede_id BIGINT UNSIGNED NOT NULL,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (usuario_id, rede_id),'
            . ' KEY idx_usuario_rede_rede (rede_id),'
            . ' CONSTRAINT fk_usuario_rede_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_usuario_rede_rede FOREIGN KEY (rede_id) REFERENCES redes (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS usuario_rede');
    }
};

<?php

declare(strict_types=1);

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS auditoria ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' usuario_id BIGINT UNSIGNED NULL,'
            . ' rede_id BIGINT UNSIGNED NULL,'
            . ' unidade_id BIGINT UNSIGNED NULL,'
            . ' acao VARCHAR(80) NOT NULL,'
            . ' entidade VARCHAR(80) NOT NULL,'
            . ' entidade_id BIGINT UNSIGNED NULL,'
            . ' dados_anteriores JSON NULL,'
            . ' dados_posteriores JSON NULL,'
            . ' ip VARCHAR(45) NULL,'
            . ' user_agent VARCHAR(255) NULL,'
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' KEY idx_auditoria_usuario (usuario_id),'
            . ' KEY idx_auditoria_entidade (entidade, entidade_id),'
            . ' KEY idx_auditoria_created_at (created_at),'
            . ' CONSTRAINT fk_auditoria_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_auditoria_rede FOREIGN KEY (rede_id) REFERENCES redes (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_auditoria_unidade FOREIGN KEY (unidade_id) REFERENCES unidades (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS auditoria');
    }
};

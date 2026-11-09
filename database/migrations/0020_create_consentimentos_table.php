<?php

declare(strict_types=1);

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS consentimentos ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' pessoa_id BIGINT UNSIGNED NOT NULL,'
            . ' tipo VARCHAR(40) NOT NULL,'
            . ' finalidade VARCHAR(180) NULL,'
            . ' versao_termo VARCHAR(40) NULL,'
            . ' concedido_em DATETIME NULL,'
            . ' revogado_em DATETIME NULL,'
            . ' ip VARCHAR(45) NULL,'
            . ' user_agent VARCHAR(255) NULL,'
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' KEY idx_consentimentos_pessoa (pessoa_id),'
            . ' KEY idx_consentimentos_tipo (tipo),'
            . ' KEY idx_consentimentos_status (status),'
            . ' CONSTRAINT fk_consentimentos_pessoa FOREIGN KEY (pessoa_id) REFERENCES pessoas (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS consentimentos');
    }
};

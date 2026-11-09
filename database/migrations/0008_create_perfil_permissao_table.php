<?php

declare(strict_types=1);

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS perfil_permissao ('
            . ' perfil_id BIGINT UNSIGNED NOT NULL,'
            . ' permissao_id BIGINT UNSIGNED NOT NULL,'
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (perfil_id, permissao_id),'
            . ' KEY idx_perfil_permissao_permissao (permissao_id),'
            . ' CONSTRAINT fk_perfil_permissao_perfil FOREIGN KEY (perfil_id) REFERENCES perfis (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_perfil_permissao_permissao FOREIGN KEY (permissao_id) REFERENCES permissoes (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS perfil_permissao');
    }
};

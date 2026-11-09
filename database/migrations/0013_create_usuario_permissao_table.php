<?php

declare(strict_types=1);

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS usuario_permissao ('
            . ' id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,'
            . ' usuario_id BIGINT UNSIGNED NOT NULL,'
            . ' permissao_id BIGINT UNSIGNED NOT NULL,'
            . ' rede_id BIGINT UNSIGNED NULL,'
            . ' unidade_id BIGINT UNSIGNED NULL,'
            . ' regiao_id BIGINT UNSIGNED NULL,'
            . ' efeito VARCHAR(20) NOT NULL DEFAULT \'CONCEDER\','
            . ' status VARCHAR(20) NOT NULL DEFAULT \'ATIVO\','
            . ' created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,'
            . ' updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,'
            . ' PRIMARY KEY (id),'
            . ' KEY idx_usuario_permissao_usuario (usuario_id),'
            . ' KEY idx_usuario_permissao_permissao (permissao_id),'
            . ' KEY idx_usuario_permissao_escopo (rede_id, unidade_id, regiao_id),'
            . ' CONSTRAINT fk_usuario_permissao_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id)'
            . ' ON DELETE CASCADE ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_usuario_permissao_permissao FOREIGN KEY (permissao_id) REFERENCES permissoes (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_usuario_permissao_rede FOREIGN KEY (rede_id) REFERENCES redes (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_usuario_permissao_unidade FOREIGN KEY (unidade_id) REFERENCES unidades (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE,'
            . ' CONSTRAINT fk_usuario_permissao_regiao FOREIGN KEY (regiao_id) REFERENCES regioes (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS usuario_permissao');
    }
};

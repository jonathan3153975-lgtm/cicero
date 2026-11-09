<?php

declare(strict_types=1);

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'ALTER TABLE perfis'
            . ' ADD COLUMN rede_id BIGINT UNSIGNED NULL AFTER id,'
            . ' ADD KEY idx_perfis_rede (rede_id),'
            . ' ADD CONSTRAINT fk_perfis_rede FOREIGN KEY (rede_id) REFERENCES redes (id)'
            . ' ON DELETE RESTRICT ON UPDATE CASCADE'
        );
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec(
            'ALTER TABLE perfis'
            . ' DROP FOREIGN KEY fk_perfis_rede,'
            . ' DROP INDEX idx_perfis_rede,'
            . ' DROP COLUMN rede_id'
        );
    }
};

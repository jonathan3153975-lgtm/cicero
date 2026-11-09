<?php

declare(strict_types=1);

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'UPDATE auditoria SET unidade_id = NULL'
            . ' WHERE unidade_id IS NOT NULL AND acao IN ('
            . ' \'rede.criar\', \'rede.editar\','
            . ' \'regiao.criar\', \'regiao.editar\','
            . ' \'perfil.criar\', \'perfil.editar\','
            . ' \'unidade.criar\''
            . ')'
        );
    }

    public function down(\PDO $pdo): void
    {
        // Normalização de escopo histórico não é reversível com segurança.
    }
};

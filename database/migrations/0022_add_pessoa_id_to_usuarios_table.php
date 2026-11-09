<?php

declare(strict_types=1);

use App\Support\PublicId;

return new class () {
    public function up(\PDO $pdo): void
    {
        $pdo->exec(
            'ALTER TABLE usuarios'
            . ' ADD COLUMN pessoa_id BIGINT UNSIGNED NULL AFTER id,'
            . ' ADD UNIQUE KEY uq_usuarios_pessoa (pessoa_id),'
            . ' ADD KEY idx_usuarios_pessoa (pessoa_id),'
            . ' ADD CONSTRAINT fk_usuarios_pessoa FOREIGN KEY (pessoa_id) REFERENCES pessoas (id)'
            . ' ON DELETE SET NULL ON UPDATE CASCADE'
        );

        $users = $pdo->query(
            'SELECT u.id, u.nome, u.email,'
            . ' COALESCE('
            . '   (SELECT ur.rede_id FROM usuario_rede ur WHERE ur.usuario_id = u.id AND ur.status = \'ATIVO\' LIMIT 1),'
            . '   (SELECT up.rede_id FROM usuario_perfil up WHERE up.usuario_id = u.id'
            . '     AND up.status = \'ATIVO\' AND up.rede_id IS NOT NULL LIMIT 1)'
            . ' ) AS rede_id'
            . ' FROM usuarios u WHERE u.pessoa_id IS NULL ORDER BY u.id'
        )->fetchAll(\PDO::FETCH_ASSOC);

        $findPessoa = $pdo->prepare('SELECT id FROM pessoas WHERE email_principal = :email LIMIT 1');
        $insertPessoa = $pdo->prepare(
            'INSERT INTO pessoas (rede_id, public_id, tipo, nome, email_principal, status)'
            . ' VALUES (:rede_id, :public_id, \'PF\', :nome, :email, \'ATIVO\')'
        );
        $updateUser = $pdo->prepare('UPDATE usuarios SET pessoa_id = :pessoa_id WHERE id = :usuario_id');

        foreach ($users as $user) {
            $findPessoa->execute(['email' => $user['email']]);
            $pessoaId = $findPessoa->fetchColumn();

            if ($pessoaId === false) {
                $insertPessoa->execute([
                    'rede_id' => $user['rede_id'] !== null ? (int) $user['rede_id'] : null,
                    'public_id' => PublicId::generate(),
                    'nome' => $user['nome'],
                    'email' => $user['email'],
                ]);
                $pessoaId = (int) $pdo->lastInsertId();
            } else {
                $pessoaId = (int) $pessoaId;
            }

            $updateUser->execute([
                'pessoa_id' => $pessoaId,
                'usuario_id' => (int) $user['id'],
            ]);
        }
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('ALTER TABLE usuarios DROP FOREIGN KEY fk_usuarios_pessoa');
        $pdo->exec('ALTER TABLE usuarios DROP INDEX uq_usuarios_pessoa');
        $pdo->exec('ALTER TABLE usuarios DROP INDEX idx_usuarios_pessoa');
        $pdo->exec('ALTER TABLE usuarios DROP COLUMN pessoa_id');
    }
};

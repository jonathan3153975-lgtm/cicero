<?php

declare(strict_types=1);

namespace App\Repositories;

final class UsuarioRepository extends Repository
{
    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, nome, email, status, ultimo_login_em FROM usuarios WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, nome, email, senha_hash, status, ultimo_login_em'
            . ' FROM usuarios WHERE email = :email LIMIT 1'
        );
        $statement->execute(['email' => mb_strtolower($email)]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function all(): array
    {
        $statement = $this->pdo()->query(
            'SELECT id, nome, email, status, ultimo_login_em FROM usuarios ORDER BY nome ASC'
        );

        return $statement->fetchAll();
    }

    public function forScope(?int $redeId, ?int $unidadeId, ?int $regiaoId = null): array
    {
        if ($unidadeId !== null) {
            $statement = $this->pdo()->prepare(
                'SELECT DISTINCT u.id, u.nome, u.email, u.status, u.ultimo_login_em'
                . ' FROM usuarios u'
                . ' LEFT JOIN usuario_unidade uu ON uu.usuario_id = u.id AND uu.status = \'ATIVO\''
                . ' LEFT JOIN usuario_perfil up ON up.usuario_id = u.id AND up.status = \'ATIVO\''
                . ' WHERE uu.unidade_id = :unidade_id OR up.unidade_id = :unidade_id_perfil'
                . ' ORDER BY u.nome ASC'
            );
            $statement->execute(['unidade_id' => $unidadeId, 'unidade_id_perfil' => $unidadeId]);

            return $statement->fetchAll();
        }

        if ($regiaoId !== null) {
            $statement = $this->pdo()->prepare(
                'SELECT DISTINCT u.id, u.nome, u.email, u.status, u.ultimo_login_em'
                . ' FROM usuarios u'
                . ' WHERE EXISTS ('
                . '   SELECT 1 FROM usuario_unidade uu'
                . '   INNER JOIN regiao_unidade ru ON ru.unidade_id = uu.unidade_id'
                . '   WHERE uu.usuario_id = u.id AND uu.status = \'ATIVO\' AND ru.regiao_id = :regiao_unidade'
                . ' ) OR EXISTS ('
                . '   SELECT 1 FROM usuario_perfil up'
                . '   INNER JOIN regiao_unidade ru ON ru.unidade_id = up.unidade_id'
                . '   WHERE up.usuario_id = u.id AND up.status = \'ATIVO\' AND ru.regiao_id = :regiao_perfil_unidade'
                . ' ) OR EXISTS ('
                . '   SELECT 1 FROM usuario_perfil up'
                . '   WHERE up.usuario_id = u.id AND up.status = \'ATIVO\' AND up.regiao_id = :regiao_perfil'
                . ' ) ORDER BY u.nome ASC'
            );
            $statement->execute([
                'regiao_unidade' => $regiaoId,
                'regiao_perfil_unidade' => $regiaoId,
                'regiao_perfil' => $regiaoId,
            ]);

            return $statement->fetchAll();
        }

        if ($redeId !== null) {
            $statement = $this->pdo()->prepare(
                'SELECT DISTINCT u.id, u.nome, u.email, u.status, u.ultimo_login_em'
                . ' FROM usuarios u'
                . ' LEFT JOIN usuario_rede ur ON ur.usuario_id = u.id AND ur.status = \'ATIVO\''
                . ' LEFT JOIN usuario_perfil up ON up.usuario_id = u.id AND up.status = \'ATIVO\''
                . ' WHERE ur.rede_id = :rede_id OR up.rede_id = :rede_id_perfil'
                . ' ORDER BY u.nome ASC'
            );
            $statement->execute(['rede_id' => $redeId, 'rede_id_perfil' => $redeId]);

            return $statement->fetchAll();
        }

        return $this->all();
    }

    public function updateLastLogin(int $id, string $moment): void
    {
        $statement = $this->pdo()->prepare('UPDATE usuarios SET ultimo_login_em = :momento WHERE id = :id');
        $statement->execute(['momento' => $moment, 'id' => $id]);
    }

    public function create(string $nome, string $email, string $passwordHash, string $status = 'ATIVO', ?int $pessoaId = null): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO usuarios (nome, email, senha_hash, status, pessoa_id)'
            . ' VALUES (:nome, :email, :senha_hash, :status, :pessoa_id)'
        );
        $statement->execute([
            'nome' => $nome,
            'email' => mb_strtolower($email),
            'senha_hash' => $passwordHash,
            'status' => $status,
            'pessoa_id' => $pessoaId,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function findByPessoaId(int $pessoaId): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, nome, email, status, ultimo_login_em FROM usuarios WHERE pessoa_id = :pessoa_id LIMIT 1'
        );
        $statement->execute(['pessoa_id' => $pessoaId]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function update(int $id, string $nome, string $email, string $status): void
    {
        $statement = $this->pdo()->prepare(
            'UPDATE usuarios SET nome = :nome, email = :email, status = :status WHERE id = :id'
        );
        $statement->execute([
            'nome' => $nome,
            'email' => mb_strtolower($email),
            'status' => $status,
            'id' => $id,
        ]);
    }

    public function updateStatus(int $id, string $status): void
    {
        $statement = $this->pdo()->prepare('UPDATE usuarios SET status = :status WHERE id = :id');
        $statement->execute(['status' => $status, 'id' => $id]);
    }

    public function updatePassword(int $id, string $passwordHash): void
    {
        $statement = $this->pdo()->prepare('UPDATE usuarios SET senha_hash = :senha_hash WHERE id = :id');
        $statement->execute(['senha_hash' => $passwordHash, 'id' => $id]);
    }
}

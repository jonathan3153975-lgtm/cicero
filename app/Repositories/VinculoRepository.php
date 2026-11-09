<?php

declare(strict_types=1);

namespace App\Repositories;

final class VinculoRepository extends Repository
{
    public function profileAssignmentsForUser(int $usuarioId): array
    {
        $statement = $this->pdo()->prepare(
            'SELECT up.id, up.usuario_id, up.perfil_id, up.rede_id, up.unidade_id, up.regiao_id, up.status,'
            . ' p.slug AS perfil_slug, p.nome AS perfil_nome, p.nivel, p.escopo'
            . ' FROM usuario_perfil up'
            . ' INNER JOIN perfis p ON p.id = up.perfil_id'
            . ' WHERE up.usuario_id = :usuario_id AND up.status = \'ATIVO\' AND p.status = \'ATIVO\''
            . ' ORDER BY p.nivel DESC'
        );
        $statement->execute(['usuario_id' => $usuarioId]);

        return $statement->fetchAll();
    }

    public function explicitPermissionsForUser(int $usuarioId): array
    {
        $statement = $this->pdo()->prepare(
            'SELECT up.usuario_id, up.rede_id, up.unidade_id, up.regiao_id, up.efeito, p.slug'
            . ' FROM usuario_permissao up'
            . ' INNER JOIN permissoes p ON p.id = up.permissao_id'
            . ' WHERE up.usuario_id = :usuario_id AND up.status = \'ATIVO\''
        );
        $statement->execute(['usuario_id' => $usuarioId]);

        return $statement->fetchAll();
    }

    public function findAssignment(
        int $usuarioId,
        int $perfilId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): ?array {
        $statement = $this->pdo()->prepare(
            'SELECT id, usuario_id, perfil_id, rede_id, unidade_id, regiao_id, status'
            . ' FROM usuario_perfil WHERE usuario_id = :usuario_id AND perfil_id = :perfil_id'
            . ' AND rede_id <=> :rede_id AND unidade_id <=> :unidade_id AND regiao_id <=> :regiao_id LIMIT 1'
        );
        $statement->execute([
            'usuario_id' => $usuarioId,
            'perfil_id' => $perfilId,
            'rede_id' => $redeId,
            'unidade_id' => $unidadeId,
            'regiao_id' => $regiaoId,
        ]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function assignProfile(
        int $usuarioId,
        int $perfilId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): int {
        $existing = $this->findAssignment($usuarioId, $perfilId, $redeId, $unidadeId, $regiaoId);

        if ($existing !== null) {
            $statement = $this->pdo()->prepare(
                'UPDATE usuario_perfil SET status = \'ATIVO\' WHERE id = :id'
            );
            $statement->execute(['id' => $existing['id']]);

            return (int) $existing['id'];
        }

        $statement = $this->pdo()->prepare(
            'INSERT INTO usuario_perfil (usuario_id, perfil_id, rede_id, unidade_id, regiao_id, status)'
            . ' VALUES (:usuario_id, :perfil_id, :rede_id, :unidade_id, :regiao_id, \'ATIVO\')'
        );
        $statement->execute([
            'usuario_id' => $usuarioId,
            'perfil_id' => $perfilId,
            'rede_id' => $redeId,
            'unidade_id' => $unidadeId,
            'regiao_id' => $regiaoId,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function allAssignmentsForScope(?int $redeId, ?int $unidadeId, ?int $regiaoId = null): array
    {
        $base = 'SELECT up.id, up.usuario_id, up.perfil_id, up.rede_id, up.unidade_id, up.regiao_id, up.status,'
            . ' u.nome AS usuario_nome, u.email AS usuario_email,'
            . ' p.slug AS perfil_slug, p.nome AS perfil_nome, p.nivel,'
            . ' r.nome_fantasia AS rede_nome, un.nome AS unidade_nome, rg.nome AS regiao_nome'
            . ' FROM usuario_perfil up'
            . ' INNER JOIN usuarios u ON u.id = up.usuario_id'
            . ' INNER JOIN perfis p ON p.id = up.perfil_id'
            . ' LEFT JOIN redes r ON r.id = up.rede_id'
            . ' LEFT JOIN unidades un ON un.id = up.unidade_id'
            . ' LEFT JOIN regioes rg ON rg.id = up.regiao_id';

        if ($regiaoId !== null) {
            $statement = $this->pdo()->prepare(
                $base
                . ' WHERE up.status = \'ATIVO\' AND (up.regiao_id = :regiao_id OR up.unidade_id IN'
                . ' (SELECT unidade_id FROM regiao_unidade WHERE regiao_id = :regiao_unidade))'
                . ' ORDER BY u.nome ASC, p.nivel DESC'
            );
            $statement->execute(['regiao_id' => $regiaoId, 'regiao_unidade' => $regiaoId]);

            return $statement->fetchAll();
        }

        if ($unidadeId !== null) {
            $statement = $this->pdo()->prepare(
                $base
                . ' WHERE up.status = \'ATIVO\' AND up.unidade_id = :unidade_id'
                . ' ORDER BY u.nome ASC, p.nivel DESC'
            );
            $statement->execute(['unidade_id' => $unidadeId]);

            return $statement->fetchAll();
        }

        if ($redeId !== null) {
            $statement = $this->pdo()->prepare(
                $base . ' WHERE up.status = \'ATIVO\' AND up.rede_id = :rede_id'
                . ' ORDER BY u.nome ASC, p.nivel DESC'
            );
            $statement->execute(['rede_id' => $redeId]);

            return $statement->fetchAll();
        }

        $statement = $this->pdo()->query(
            $base . ' WHERE up.status = \'ATIVO\' ORDER BY u.nome ASC, p.nivel DESC'
        );

        return $statement->fetchAll();
    }

    public function findAssignmentById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, usuario_id, perfil_id, rede_id, unidade_id, regiao_id, status'
            . ' FROM usuario_perfil WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function revokeAssignment(int $id): void
    {
        $statement = $this->pdo()->prepare('UPDATE usuario_perfil SET status = \'INATIVO\' WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function linkRede(int $usuarioId, int $redeId): void
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO usuario_rede (usuario_id, rede_id, status) VALUES (:usuario_id, :rede_id, \'ATIVO\')'
            . ' ON DUPLICATE KEY UPDATE status = \'ATIVO\''
        );
        $statement->execute(['usuario_id' => $usuarioId, 'rede_id' => $redeId]);
    }

    public function linkUnidade(int $usuarioId, int $unidadeId): void
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO usuario_unidade (usuario_id, unidade_id, status) VALUES (:usuario_id, :unidade_id, \'ATIVO\')'
            . ' ON DUPLICATE KEY UPDATE status = \'ATIVO\''
        );
        $statement->execute(['usuario_id' => $usuarioId, 'unidade_id' => $unidadeId]);
    }

    public function userInDirectScope(int $usuarioId, ?int $redeId, ?int $unidadeId, ?int $regiaoId = null): bool
    {
        if ($redeId === null && $unidadeId === null && $regiaoId === null) {
            return true;
        }

        if ($regiaoId !== null) {
            $statement = $this->pdo()->prepare(
                'SELECT 1 FROM usuario_unidade uu'
                . ' INNER JOIN regiao_unidade ru ON ru.unidade_id = uu.unidade_id'
                . ' WHERE uu.usuario_id = ? AND uu.status = \'ATIVO\' AND ru.regiao_id = ?'
                . ' UNION SELECT 1 FROM usuario_perfil up'
                . ' INNER JOIN regiao_unidade ru ON ru.unidade_id = up.unidade_id'
                . ' WHERE up.usuario_id = ? AND up.status = \'ATIVO\' AND ru.regiao_id = ?'
                . ' UNION SELECT 1 FROM usuario_perfil'
                . ' WHERE usuario_id = ? AND status = \'ATIVO\' AND regiao_id = ? LIMIT 1'
            );
            $statement->execute([$usuarioId, $regiaoId, $usuarioId, $regiaoId, $usuarioId, $regiaoId]);

            return $statement->fetchColumn() !== false;
        }

        if ($unidadeId !== null) {
            $statement = $this->pdo()->prepare(
                'SELECT 1 FROM usuario_unidade WHERE usuario_id = ? AND unidade_id = ? AND status = \'ATIVO\''
                . ' UNION SELECT 1 FROM usuario_perfil WHERE usuario_id = ? AND unidade_id = ?'
                . ' AND status = \'ATIVO\' LIMIT 1'
            );
            $statement->execute([$usuarioId, $unidadeId, $usuarioId, $unidadeId]);

            return $statement->fetchColumn() !== false;
        }

        $statement = $this->pdo()->prepare(
            'SELECT 1 FROM usuario_rede WHERE usuario_id = ? AND rede_id = ? AND status = \'ATIVO\''
            . ' UNION SELECT 1 FROM usuario_perfil WHERE usuario_id = ? AND rede_id = ?'
            . ' AND status = \'ATIVO\' LIMIT 1'
        );
        $statement->execute([$usuarioId, $redeId, $usuarioId, $redeId]);

        return $statement->fetchColumn() !== false;
    }

    public function userInScope(int $usuarioId, ?int $redeId, ?int $unidadeId): bool
    {
        if ($unidadeId === null && $redeId === null) {
            return true;
        }

        if ($unidadeId !== null) {
            $statement = $this->pdo()->prepare(
                'SELECT 1 FROM usuario_unidade WHERE usuario_id = ? AND unidade_id = ?'
                . ' AND status = \'ATIVO\''
                . ' UNION SELECT 1 FROM usuario_perfil WHERE usuario_id = ?'
                . ' AND unidade_id = ? AND status = \'ATIVO\''
                . ' UNION SELECT 1 FROM usuario_perfil up'
                . ' INNER JOIN regiao_unidade ru ON ru.regiao_id = up.regiao_id'
                . ' WHERE up.usuario_id = ? AND ru.unidade_id = ?'
                . ' AND up.status = \'ATIVO\' LIMIT 1'
            );
            $statement->execute([$usuarioId, $unidadeId, $usuarioId, $unidadeId, $usuarioId, $unidadeId]);

            return $statement->fetchColumn() !== false;
        }

        $statement = $this->pdo()->prepare(
            'SELECT 1 FROM usuario_rede WHERE usuario_id = ? AND rede_id = ?'
            . ' AND status = \'ATIVO\''
            . ' UNION SELECT 1 FROM usuario_perfil WHERE usuario_id = ?'
            . ' AND rede_id = ? AND status = \'ATIVO\' LIMIT 1'
        );
        $statement->execute([$usuarioId, $redeId, $usuarioId, $redeId]);

        return $statement->fetchColumn() !== false;
    }

    public function hasRedeMembership(int $usuarioId, int $redeId): bool
    {
        $statement = $this->pdo()->prepare(
            'SELECT 1 FROM usuario_rede WHERE usuario_id = :usuario_id AND rede_id = :rede_id'
            . ' AND status = \'ATIVO\' LIMIT 1'
        );
        $statement->execute(['usuario_id' => $usuarioId, 'rede_id' => $redeId]);

        return $statement->fetchColumn() !== false;
    }

    public function hasUnidadeMembership(int $usuarioId, int $unidadeId): bool
    {
        $statement = $this->pdo()->prepare(
            'SELECT 1 FROM usuario_unidade WHERE usuario_id = :usuario_id AND unidade_id = :unidade_id'
            . ' AND status = \'ATIVO\' LIMIT 1'
        );
        $statement->execute(['usuario_id' => $usuarioId, 'unidade_id' => $unidadeId]);

        return $statement->fetchColumn() !== false;
    }
}

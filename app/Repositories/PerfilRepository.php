<?php

declare(strict_types=1);

namespace App\Repositories;

final class PerfilRepository extends Repository
{
    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, rede_id, slug, nome, nivel, escopo, sistema, status'
            . ' FROM perfis WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, rede_id, slug, nome, nivel, escopo, sistema, status'
            . ' FROM perfis WHERE slug = :slug LIMIT 1'
        );
        $statement->execute(['slug' => $slug]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function all(): array
    {
        $statement = $this->pdo()->query(
            'SELECT id, rede_id, slug, nome, nivel, escopo, sistema, status'
            . ' FROM perfis ORDER BY nivel DESC, nome ASC'
        );

        return $statement->fetchAll();
    }

    public function allForRede(?int $redeId, bool $includeAll = false): array
    {
        if ($includeAll) {
            return $this->all();
        }

        $statement = $this->pdo()->prepare(
            'SELECT id, rede_id, slug, nome, nivel, escopo, sistema, status'
            . ' FROM perfis WHERE rede_id IS NULL OR rede_id = :rede_id'
            . ' ORDER BY nivel DESC, nome ASC'
        );
        $statement->execute(['rede_id' => $redeId]);

        return $statement->fetchAll();
    }

    public function create(?int $redeId, array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO perfis (rede_id, slug, nome, nivel, escopo, sistema, status)'
            . ' VALUES (:rede_id, :slug, :nome, :nivel, :escopo, 0, :status)'
        );
        $statement->execute([
            'rede_id' => $redeId,
            'slug' => $data['slug'],
            'nome' => $data['nome'],
            'nivel' => (int) $data['nivel'],
            'escopo' => $data['escopo'],
            'status' => $data['status'] ?? 'ATIVO',
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $statement = $this->pdo()->prepare(
            'UPDATE perfis SET slug = :slug, nome = :nome, nivel = :nivel, escopo = :escopo,'
            . ' status = :status WHERE id = :id'
        );
        $statement->execute([
            'slug' => $data['slug'],
            'nome' => $data['nome'],
            'nivel' => (int) $data['nivel'],
            'escopo' => $data['escopo'],
            'status' => $data['status'] ?? 'ATIVO',
            'id' => $id,
        ]);
    }

    public function syncPermissions(int $perfilId, array $permissionIds): void
    {
        $delete = $this->pdo()->prepare('DELETE FROM perfil_permissao WHERE perfil_id = :perfil_id');
        $delete->execute(['perfil_id' => $perfilId]);

        if ($permissionIds === []) {
            return;
        }

        $insert = $this->pdo()->prepare(
            'INSERT INTO perfil_permissao (perfil_id, permissao_id) VALUES (:perfil_id, :permissao_id)'
        );

        foreach (array_unique($permissionIds) as $permissionId) {
            $insert->execute([
                'perfil_id' => $perfilId,
                'permissao_id' => (int) $permissionId,
            ]);
        }
    }
}

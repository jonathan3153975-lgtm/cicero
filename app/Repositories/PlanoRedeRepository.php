<?php

declare(strict_types=1);

namespace App\Repositories;

final class PlanoRedeRepository extends Repository
{
    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, nome, descricao, status FROM planos_rede WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function all(): array
    {
        $statement = $this->pdo()->query(
            'SELECT id, nome, descricao, status, created_at, updated_at'
            . ' FROM planos_rede ORDER BY nome ASC'
        );

        return $statement->fetchAll();
    }

    public function allActive(): array
    {
        $statement = $this->pdo()->query(
            'SELECT id, nome, descricao FROM planos_rede WHERE status = \'ATIVO\' ORDER BY nome ASC'
        );

        return $statement->fetchAll();
    }

    public function existsName(string $nome, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT 1 FROM planos_rede WHERE nome = :nome';
        $parameters = ['nome' => $nome];

        if ($ignoreId !== null) {
            $sql .= ' AND id <> :ignore_id';
            $parameters['ignore_id'] = $ignoreId;
        }

        $statement = $this->pdo()->prepare($sql . ' LIMIT 1');
        $statement->execute($parameters);

        return $statement->fetchColumn() !== false;
    }

    public function create(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO planos_rede (nome, descricao, status, created_by, updated_by)'
            . ' VALUES (:nome, :descricao, :status, :created_by, :updated_by)'
        );
        $statement->execute($this->parameters($data));

        return (int) $this->pdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $parameters = $this->parameters($data);
        unset($parameters['created_by']);
        $parameters['id'] = $id;

        $statement = $this->pdo()->prepare(
            'UPDATE planos_rede SET nome = :nome, descricao = :descricao, status = :status,'
            . ' updated_by = :updated_by WHERE id = :id'
        );
        $statement->execute($parameters);
    }

    private function parameters(array $data): array
    {
        return [
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?: null,
            'status' => $data['status'] ?? 'ATIVO',
            'created_by' => $data['created_by'] ?? null,
            'updated_by' => $data['updated_by'] ?? null,
        ];
    }
}

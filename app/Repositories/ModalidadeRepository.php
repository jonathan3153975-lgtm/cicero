<?php

declare(strict_types=1);

namespace App\Repositories;

final class ModalidadeRepository extends Repository
{
    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, rede_id, nome, descricao, status FROM modalidades WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function forScope(?int $redeId, bool $includeAll = false): array
    {
        $sql = 'SELECT m.id, m.rede_id, m.nome, m.descricao, m.status,'
            . ' r.nome_fantasia AS rede_nome'
            . ' FROM modalidades m'
            . ' LEFT JOIN redes r ON r.id = m.rede_id';

        if ($includeAll) {
            $sql .= ' ORDER BY m.nome ASC';
            $statement = $this->pdo()->query($sql);

            return $statement->fetchAll();
        }

        $sql .= ' WHERE m.rede_id IS NULL OR m.rede_id = :rede_id ORDER BY m.nome ASC';
        $statement = $this->pdo()->prepare($sql);
        $statement->execute(['rede_id' => $redeId]);

        return $statement->fetchAll();
    }

    public function existsName(?int $redeId, string $nome, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT 1 FROM modalidades WHERE nome = :nome'
            . ' AND ((rede_id IS NULL AND :rede_id_null IS NULL) OR rede_id = :rede_id)';

        if ($ignoreId !== null) {
            $sql .= ' AND id <> :ignore_id';
        }

        $sql .= ' LIMIT 1';

        $statement = $this->pdo()->prepare($sql);
        $parameters = [
            'nome' => $nome,
            'rede_id' => $redeId,
            'rede_id_null' => $redeId,
        ];

        if ($ignoreId !== null) {
            $parameters['ignore_id'] = $ignoreId;
        }

        $statement->execute($parameters);

        return $statement->fetchColumn() !== false;
    }

    public function allActiveForRede(?int $redeId, bool $includeAll = false): array
    {
        if ($includeAll) {
            $statement = $this->pdo()->query(
                'SELECT id, rede_id, nome FROM modalidades WHERE status = \'ATIVO\' ORDER BY nome ASC'
            );

            return $statement->fetchAll();
        }

        $statement = $this->pdo()->prepare(
            'SELECT id, rede_id, nome FROM modalidades'
            . ' WHERE status = \'ATIVO\' AND (rede_id IS NULL OR rede_id = :rede_id) ORDER BY nome ASC'
        );
        $statement->execute(['rede_id' => $redeId]);

        return $statement->fetchAll();
    }

    public function create(?int $redeId, array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO modalidades (rede_id, nome, descricao, status)'
            . ' VALUES (:rede_id, :nome, :descricao, :status)'
        );
        $statement->execute([
            'rede_id' => $redeId,
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?: null,
            'status' => $data['status'],
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $statement = $this->pdo()->prepare(
            'UPDATE modalidades SET nome = :nome, descricao = :descricao, status = :status WHERE id = :id'
        );
        $statement->execute([
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?: null,
            'status' => $data['status'],
            'id' => $id,
        ]);
    }
}

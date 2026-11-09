<?php

declare(strict_types=1);

namespace App\Repositories;

final class TurmaRepository extends Repository
{
    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT t.id, t.rede_id, t.unidade_id, t.modalidade_id, t.nome, t.capacidade,'
            . ' t.sala, t.status, u.nome AS unidade_nome, r.nome_fantasia AS rede_nome,'
            . ' m.nome AS modalidade_nome'
            . ' FROM turmas t'
            . ' INNER JOIN unidades u ON u.id = t.unidade_id'
            . ' INNER JOIN redes r ON r.id = t.rede_id'
            . ' LEFT JOIN modalidades m ON m.id = t.modalidade_id'
            . ' WHERE t.id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function forScope(
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        string $search = ''
    ): array {
        $sql = 'SELECT t.id, t.rede_id, t.unidade_id, t.modalidade_id, t.nome, t.capacidade,'
            . ' t.sala, t.status, u.nome AS unidade_nome, m.nome AS modalidade_nome'
            . ' FROM turmas t'
            . ' INNER JOIN unidades u ON u.id = t.unidade_id'
            . ' LEFT JOIN modalidades m ON m.id = t.modalidade_id';
        $where = [];
        $parameters = [];

        if ($unidadeId !== null) {
            $where[] = 't.unidade_id = :unidade_id';
            $parameters['unidade_id'] = $unidadeId;
        } elseif ($regiaoId !== null) {
            $where[] = 't.unidade_id IN (SELECT unidade_id FROM regiao_unidade WHERE regiao_id = :regiao_id)';
            $parameters['regiao_id'] = $regiaoId;
        } elseif ($redeId !== null) {
            $where[] = 't.rede_id = :rede_id';
            $parameters['rede_id'] = $redeId;
        }

        if ($search !== '') {
            $where[] = 't.nome LIKE :search_nome';
            $parameters['search_nome'] = '%' . $search . '%';
        }

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY t.nome ASC LIMIT 200';
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function create(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO turmas (rede_id, unidade_id, modalidade_id, nome, capacidade, sala,'
            . ' status, created_by, updated_by)'
            . ' VALUES (:rede_id, :unidade_id, :modalidade_id, :nome, :capacidade, :sala,'
            . ' :status, :created_by, :updated_by)'
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
            'UPDATE turmas SET rede_id = :rede_id, unidade_id = :unidade_id,'
            . ' modalidade_id = :modalidade_id, nome = :nome, capacidade = :capacidade,'
            . ' sala = :sala, status = :status, updated_by = :updated_by WHERE id = :id'
        );
        $statement->execute($parameters);
    }

    private function parameters(array $data): array
    {
        return [
            'rede_id' => (int) $data['rede_id'],
            'unidade_id' => (int) $data['unidade_id'],
            'modalidade_id' => $data['modalidade_id'] !== null ? (int) $data['modalidade_id'] : null,
            'nome' => $data['nome'],
            'capacidade' => $data['capacidade'] !== '' ? (int) $data['capacidade'] : null,
            'sala' => $data['sala'] ?: null,
            'status' => $data['status'] ?? 'ATIVA',
            'created_by' => $data['created_by'] ?? null,
            'updated_by' => $data['updated_by'] ?? null,
        ];
    }
}

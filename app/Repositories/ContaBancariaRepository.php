<?php

declare(strict_types=1);

namespace App\Repositories;

final class ContaBancariaRepository extends Repository
{
    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT c.id, c.rede_id, c.unidade_id, c.banco, c.agencia, c.conta, c.tipo,'
            . ' c.saldo_inicial, c.status, u.nome AS unidade_nome'
            . ' FROM contas_bancarias c'
            . ' INNER JOIN unidades u ON u.id = c.unidade_id'
            . ' WHERE c.id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function forScope(?int $redeId, ?int $unidadeId, ?int $regiaoId): array
    {
        $sql = 'SELECT c.id, c.rede_id, c.unidade_id, c.banco, c.agencia, c.conta, c.tipo,'
            . ' c.saldo_inicial, c.status, u.nome AS unidade_nome'
            . ' FROM contas_bancarias c'
            . ' INNER JOIN unidades u ON u.id = c.unidade_id';
        $where = [];
        $parameters = [];

        if ($unidadeId !== null) {
            $where[] = 'c.unidade_id = :unidade_id';
            $parameters['unidade_id'] = $unidadeId;
        } elseif ($regiaoId !== null) {
            $where[] = 'c.unidade_id IN (SELECT unidade_id FROM regiao_unidade WHERE regiao_id = :regiao_id)';
            $parameters['regiao_id'] = $regiaoId;
        } elseif ($redeId !== null) {
            $where[] = 'c.rede_id = :rede_id';
            $parameters['rede_id'] = $redeId;
        }

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY u.nome ASC, c.banco ASC';
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function countForScope(?int $redeId, ?int $unidadeId, ?int $regiaoId): int
    {
        $sql = 'SELECT COUNT(*) FROM contas_bancarias c';
        $where = [];
        $parameters = [];

        if ($unidadeId !== null) {
            $where[] = 'c.unidade_id = :unidade_id';
            $parameters['unidade_id'] = $unidadeId;
        } elseif ($regiaoId !== null) {
            $where[] = 'c.unidade_id IN (SELECT unidade_id FROM regiao_unidade WHERE regiao_id = :regiao_id)';
            $parameters['regiao_id'] = $regiaoId;
        } elseif ($redeId !== null) {
            $where[] = 'c.rede_id = :rede_id';
            $parameters['rede_id'] = $redeId;
        }

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $statement = $this->pdo()->prepare($sql);
        $statement->execute($parameters);

        return (int) $statement->fetchColumn();
    }

    public function create(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO contas_bancarias (rede_id, unidade_id, banco, agencia, conta, tipo,'
            . ' saldo_inicial, status)'
            . ' VALUES (:rede_id, :unidade_id, :banco, :agencia, :conta, :tipo, :saldo_inicial, :status)'
        );
        $statement->execute([
            'rede_id' => (int) $data['rede_id'],
            'unidade_id' => (int) $data['unidade_id'],
            'banco' => $data['banco'],
            'agencia' => $data['agencia'] ?: null,
            'conta' => $data['conta'] ?: null,
            'tipo' => $data['tipo'] ?? 'CORRENTE',
            'saldo_inicial' => $data['saldo_inicial'] ?? 0,
            'status' => $data['status'] ?? 'ATIVO',
        ]);

        return (int) $this->pdo()->lastInsertId();
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories;

final class ContaPagarRepository extends Repository
{
    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT cp.id, cp.public_id, cp.rede_id, cp.unidade_id, cp.fornecedor_id,'
            . ' cp.centro_custo_id, cp.descricao, cp.competencia, cp.data_emissao,'
            . ' cp.data_vencimento, cp.valor, cp.status, cp.data_pagamento,'
            . ' cp.conta_bancaria_id, u.nome AS unidade_nome, COALESCE(f.nome_fantasia, pf.nome) AS fornecedor_nome,'
            . ' cc.nome AS centro_custo_nome'
            . ' FROM contas_pagar cp'
            . ' INNER JOIN unidades u ON u.id = cp.unidade_id'
            . ' LEFT JOIN fornecedores f ON f.id = cp.fornecedor_id'
            . ' LEFT JOIN pessoas pf ON pf.id = f.pessoa_id'
            . ' LEFT JOIN centros_custo cc ON cc.id = cp.centro_custo_id'
            . ' WHERE cp.id = :id LIMIT 1'
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
        $sql = 'SELECT cp.id, cp.rede_id, cp.unidade_id, cp.fornecedor_id, cp.descricao,'
            . ' cp.competencia, cp.data_vencimento, cp.valor, cp.status, cp.data_pagamento,'
            . ' u.nome AS unidade_nome, COALESCE(f.nome_fantasia, pf.nome) AS fornecedor_nome'
            . ' FROM contas_pagar cp'
            . ' INNER JOIN unidades u ON u.id = cp.unidade_id'
            . ' LEFT JOIN fornecedores f ON f.id = cp.fornecedor_id'
            . ' LEFT JOIN pessoas pf ON pf.id = f.pessoa_id';
        $where = [];
        $parameters = [];

        if ($unidadeId !== null) {
            $where[] = 'cp.unidade_id = :unidade_id';
            $parameters['unidade_id'] = $unidadeId;
        } elseif ($regiaoId !== null) {
            $where[] = 'cp.unidade_id IN (SELECT unidade_id FROM regiao_unidade WHERE regiao_id = :regiao_id)';
            $parameters['regiao_id'] = $regiaoId;
        } elseif ($redeId !== null) {
            $where[] = 'cp.rede_id = :rede_id';
            $parameters['rede_id'] = $redeId;
        }

        if ($search !== '') {
            $where[] = '(cp.descricao LIKE :search_descricao OR COALESCE(f.nome_fantasia, pf.nome) LIKE :search_fornecedor)';
            $term = '%' . $search . '%';
            $parameters['search_descricao'] = $term;
            $parameters['search_fornecedor'] = $term;
        }

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY cp.data_vencimento ASC, cp.id DESC LIMIT 200';
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function countForScope(?int $redeId, ?int $unidadeId, ?int $regiaoId): int
    {
        $sql = 'SELECT COUNT(*) FROM contas_pagar cp';
        $where = [];
        $parameters = [];

        if ($unidadeId !== null) {
            $where[] = 'cp.unidade_id = :unidade_id';
            $parameters['unidade_id'] = $unidadeId;
        } elseif ($regiaoId !== null) {
            $where[] = 'cp.unidade_id IN (SELECT unidade_id FROM regiao_unidade WHERE regiao_id = :regiao_id)';
            $parameters['regiao_id'] = $regiaoId;
        } elseif ($redeId !== null) {
            $where[] = 'cp.rede_id = :rede_id';
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
            'INSERT INTO contas_pagar (public_id, rede_id, unidade_id, fornecedor_id,'
            . ' centro_custo_id, descricao, competencia, data_emissao, data_vencimento,'
            . ' valor, status, data_pagamento, conta_bancaria_id, created_by, updated_by)'
            . ' VALUES (:public_id, :rede_id, :unidade_id, :fornecedor_id,'
            . ' :centro_custo_id, :descricao, :competencia, :data_emissao, :data_vencimento,'
            . ' :valor, :status, :data_pagamento, :conta_bancaria_id, :created_by, :updated_by)'
        );
        $statement->execute([
            'public_id' => $data['public_id'],
            'rede_id' => (int) $data['rede_id'],
            'unidade_id' => (int) $data['unidade_id'],
            'fornecedor_id' => $data['fornecedor_id'] !== null ? (int) $data['fornecedor_id'] : null,
            'centro_custo_id' => $data['centro_custo_id'] !== null ? (int) $data['centro_custo_id'] : null,
            'descricao' => $data['descricao'],
            'competencia' => $data['competencia'],
            'data_emissao' => $data['data_emissao'] ?: null,
            'data_vencimento' => $data['data_vencimento'],
            'valor' => $data['valor'],
            'status' => $data['status'] ?? 'ABERTA',
            'data_pagamento' => ($data['data_pagamento'] ?? '') ?: null,
            'conta_bancaria_id' => ($data['conta_bancaria_id'] ?? '') !== '' ? (int) $data['conta_bancaria_id'] : null,
            'created_by' => $data['created_by'] ?? null,
            'updated_by' => $data['updated_by'] ?? null,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function markPaid(int $id, string $dataPagamento, int $contaBancariaId, ?int $userId): void
    {
        $statement = $this->pdo()->prepare(
            'UPDATE contas_pagar SET status = \'PAGA\', data_pagamento = :data_pagamento,'
            . ' conta_bancaria_id = :conta_bancaria_id, updated_by = :updated_by WHERE id = :id'
        );
        $statement->execute([
            'data_pagamento' => $dataPagamento,
            'conta_bancaria_id' => $contaBancariaId,
            'updated_by' => $userId,
            'id' => $id,
        ]);
    }

    public function updateStatus(int $id, string $status, ?int $userId): void
    {
        $statement = $this->pdo()->prepare(
            'UPDATE contas_pagar SET status = :status, updated_by = :updated_by WHERE id = :id'
        );
        $statement->execute(['status' => $status, 'updated_by' => $userId, 'id' => $id]);
    }
}

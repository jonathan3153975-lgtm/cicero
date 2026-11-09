<?php

declare(strict_types=1);

namespace App\Repositories;

final class PagamentoRepository extends Repository
{
    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT pg.id, pg.public_id, pg.rede_id, pg.unidade_id, pg.aluno_id, pg.cobranca_id,'
            . ' pg.conta_bancaria_id, pg.forma_pagamento_id, pg.valor, pg.data_pagamento,'
            . ' pg.data_compensacao, pg.status, pg.transacao_id, p.nome AS aluno_nome,'
            . ' c.numero AS cobranca_numero'
            . ' FROM pagamentos pg'
            . ' INNER JOIN alunos a ON a.id = pg.aluno_id'
            . ' INNER JOIN pessoas p ON p.id = a.pessoa_id'
            . ' LEFT JOIN cobrancas c ON c.id = pg.cobranca_id'
            . ' WHERE pg.id = :id LIMIT 1'
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
        $sql = 'SELECT pg.id, pg.aluno_id, pg.cobranca_id, pg.rede_id, pg.unidade_id, pg.valor,'
            . ' pg.data_pagamento, pg.status, p.nome AS aluno_nome, c.numero AS cobranca_numero,'
            . ' u.nome AS unidade_nome'
            . ' FROM pagamentos pg'
            . ' INNER JOIN alunos a ON a.id = pg.aluno_id'
            . ' INNER JOIN pessoas p ON p.id = a.pessoa_id'
            . ' LEFT JOIN cobrancas c ON c.id = pg.cobranca_id'
            . ' INNER JOIN unidades u ON u.id = pg.unidade_id';
        $where = [];
        $parameters = [];

        if ($unidadeId !== null) {
            $where[] = 'pg.unidade_id = :unidade_id';
            $parameters['unidade_id'] = $unidadeId;
        } elseif ($regiaoId !== null) {
            $where[] = 'pg.unidade_id IN (SELECT unidade_id FROM regiao_unidade WHERE regiao_id = :regiao_id)';
            $parameters['regiao_id'] = $regiaoId;
        } elseif ($redeId !== null) {
            $where[] = 'pg.rede_id = :rede_id';
            $parameters['rede_id'] = $redeId;
        }

        if ($search !== '') {
            $where[] = 'p.nome LIKE :search_nome';
            $parameters['search_nome'] = '%' . $search . '%';
        }

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY pg.data_pagamento DESC, pg.id DESC LIMIT 200';
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function create(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO pagamentos (public_id, rede_id, unidade_id, aluno_id, cobranca_id,'
            . ' conta_bancaria_id, forma_pagamento_id, valor, data_pagamento, data_compensacao,'
            . ' status, transacao_id, criado_por)'
            . ' VALUES (:public_id, :rede_id, :unidade_id, :aluno_id, :cobranca_id,'
            . ' :conta_bancaria_id, :forma_pagamento_id, :valor, :data_pagamento, :data_compensacao,'
            . ' :status, :transacao_id, :criado_por)'
        );
        $statement->execute([
            'public_id' => $data['public_id'],
            'rede_id' => (int) $data['rede_id'],
            'unidade_id' => (int) $data['unidade_id'],
            'aluno_id' => (int) $data['aluno_id'],
            'cobranca_id' => $data['cobranca_id'] !== null ? (int) $data['cobranca_id'] : null,
            'conta_bancaria_id' => (int) $data['conta_bancaria_id'],
            'forma_pagamento_id' => (int) $data['forma_pagamento_id'],
            'valor' => $data['valor'],
            'data_pagamento' => $data['data_pagamento'],
            'data_compensacao' => $data['data_compensacao'] ?: null,
            'status' => $data['status'] ?? 'CONFIRMADO',
            'transacao_id' => $data['transacao_id'] ?: null,
            'criado_por' => $data['criado_por'] ?? null,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function addAllocation(int $pagamentoId, int $cobrancaId, float $valor): void
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO pagamento_alocacoes (pagamento_id, cobranca_id, valor)'
            . ' VALUES (:pagamento_id, :cobranca_id, :valor)'
        );
        $statement->execute([
            'pagamento_id' => $pagamentoId,
            'cobranca_id' => $cobrancaId,
            'valor' => $valor,
        ]);
    }

    public function sumAllocatedForCobranca(int $cobrancaId): float
    {
        $statement = $this->pdo()->prepare(
            'SELECT COALESCE(SUM(pa.valor), 0) FROM pagamento_alocacoes pa'
            . ' INNER JOIN pagamentos pg ON pg.id = pa.pagamento_id'
            . ' WHERE pa.cobranca_id = :cobranca_id AND pg.status = \'CONFIRMADO\''
        );
        $statement->execute(['cobranca_id' => $cobrancaId]);

        return (float) $statement->fetchColumn();
    }

    public function updateStatus(int $id, string $status): void
    {
        $statement = $this->pdo()->prepare('UPDATE pagamentos SET status = :status WHERE id = :id');
        $statement->execute(['status' => $status, 'id' => $id]);
    }

    public function markReversed(int $id): void
    {
        $statement = $this->pdo()->prepare(
            'UPDATE pagamentos SET status = \'ESTORNADO\', estornado_em = NOW() WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories;

final class ConciliacaoRepository extends Repository
{
    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT cb.id, cb.rede_id, cb.unidade_id, cb.conta_bancaria_id, cb.competencia,'
            . ' cb.status, cb.importado_em, cb.fechado_em, c.banco, c.agencia, c.conta,'
            . ' u.nome AS unidade_nome'
            . ' FROM conciliacoes_bancarias cb'
            . ' INNER JOIN contas_bancarias c ON c.id = cb.conta_bancaria_id'
            . ' INNER JOIN unidades u ON u.id = cb.unidade_id'
            . ' WHERE cb.id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function forScope(?int $redeId, ?int $unidadeId, ?int $regiaoId): array
    {
        $sql = 'SELECT cb.id, cb.rede_id, cb.unidade_id, cb.conta_bancaria_id, cb.competencia,'
            . ' cb.status, cb.importado_em, cb.fechado_em, c.banco, c.agencia, c.conta,'
            . ' u.nome AS unidade_nome,'
            . ' (SELECT COUNT(*) FROM conciliacao_itens ci WHERE ci.conciliacao_id = cb.id) AS total_itens,'
            . ' (SELECT COUNT(*) FROM conciliacao_itens ci WHERE ci.conciliacao_id = cb.id AND ci.status = \'CONCILIADO\') AS itens_conciliados'
            . ' FROM conciliacoes_bancarias cb'
            . ' INNER JOIN contas_bancarias c ON c.id = cb.conta_bancaria_id'
            . ' INNER JOIN unidades u ON u.id = cb.unidade_id';
        $where = [];
        $parameters = [];

        if ($unidadeId !== null) {
            $where[] = 'cb.unidade_id = :unidade_id';
            $parameters['unidade_id'] = $unidadeId;
        } elseif ($regiaoId !== null) {
            $where[] = 'cb.unidade_id IN (SELECT unidade_id FROM regiao_unidade WHERE regiao_id = :regiao_id)';
            $parameters['regiao_id'] = $regiaoId;
        } elseif ($redeId !== null) {
            $where[] = 'cb.rede_id = :rede_id';
            $parameters['rede_id'] = $redeId;
        }

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY cb.created_at DESC, cb.id DESC LIMIT 100';
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function create(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO conciliacoes_bancarias (rede_id, unidade_id, conta_bancaria_id,'
            . ' competencia, status, importado_em)'
            . ' VALUES (:rede_id, :unidade_id, :conta_bancaria_id, :competencia, :status, :importado_em)'
        );
        $statement->execute([
            'rede_id' => (int) $data['rede_id'],
            'unidade_id' => (int) $data['unidade_id'],
            'conta_bancaria_id' => (int) $data['conta_bancaria_id'],
            'competencia' => $data['competencia'],
            'status' => $data['status'] ?? 'ABERTA',
            'importado_em' => $data['importado_em'] ?? null,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function addItem(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO conciliacao_itens (conciliacao_id, data, descricao, valor, tipo,'
            . ' movimento_caixa_id, status, diferenca)'
            . ' VALUES (:conciliacao_id, :data, :descricao, :valor, :tipo,'
            . ' :movimento_caixa_id, :status, :diferenca)'
        );
        $statement->execute([
            'conciliacao_id' => (int) $data['conciliacao_id'],
            'data' => $data['data'],
            'descricao' => $data['descricao'] ?? null,
            'valor' => $data['valor'],
            'tipo' => $data['tipo'],
            'movimento_caixa_id' => $data['movimento_caixa_id'] ?? null,
            'status' => $data['status'] ?? 'PENDENTE',
            'diferenca' => $data['diferenca'] ?? 0,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function items(int $conciliacaoId): array
    {
        $statement = $this->pdo()->prepare(
            'SELECT ci.id, ci.data, ci.descricao, ci.valor, ci.tipo, ci.status, ci.diferenca,'
            . ' ci.movimento_caixa_id, mc.descricao AS movimento_descricao'
            . ' FROM conciliacao_itens ci'
            . ' LEFT JOIN movimentos_caixa mc ON mc.id = ci.movimento_caixa_id'
            . ' WHERE ci.conciliacao_id = :conciliacao_id ORDER BY ci.data ASC, ci.id ASC'
        );
        $statement->execute(['conciliacao_id' => $conciliacaoId]);

        return $statement->fetchAll();
    }

    public function findItem(int $itemId): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, conciliacao_id, data, descricao, valor, tipo, movimento_caixa_id,'
            . ' status, diferenca FROM conciliacao_itens WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $itemId]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function updateItem(
        int $itemId,
        string $status,
        ?int $movimentoCaixaId,
        float $diferenca
    ): void {
        $statement = $this->pdo()->prepare(
            'UPDATE conciliacao_itens SET status = :status, movimento_caixa_id = :movimento_caixa_id,'
            . ' diferenca = :diferenca WHERE id = :id'
        );
        $statement->execute([
            'status' => $status,
            'movimento_caixa_id' => $movimentoCaixaId,
            'diferenca' => $diferenca,
            'id' => $itemId,
        ]);
    }

    public function close(int $id, int $userId): void
    {
        $statement = $this->pdo()->prepare(
            'UPDATE conciliacoes_bancarias SET status = \'FECHADA\', fechado_em = NOW(),'
            . ' fechado_por = :fechado_por WHERE id = :id'
        );
        $statement->execute(['fechado_por' => $userId, 'id' => $id]);
    }
}

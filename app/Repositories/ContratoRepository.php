<?php

declare(strict_types=1);

namespace App\Repositories;

final class ContratoRepository extends Repository
{
    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT c.id, c.public_id, c.aluno_id, c.rede_id, c.unidade_id, c.plano_id, c.numero,'
            . ' c.data_inicio, c.data_fim, c.renovacao_automatica, c.dia_vencimento, c.valor_mensal,'
            . ' c.status, c.observacoes, p.nome AS aluno_nome, pl.nome AS plano_nome,'
            . ' u.nome AS unidade_nome'
            . ' FROM contratos c'
            . ' INNER JOIN alunos a ON a.id = c.aluno_id'
            . ' INNER JOIN pessoas p ON p.id = a.pessoa_id'
            . ' INNER JOIN planos pl ON pl.id = c.plano_id'
            . ' INNER JOIN unidades u ON u.id = c.unidade_id'
            . ' WHERE c.id = :id LIMIT 1'
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
        $sql = 'SELECT c.id, c.aluno_id, c.rede_id, c.unidade_id, c.plano_id, c.numero,'
            . ' c.data_inicio, c.data_fim, c.valor_mensal, c.status,'
            . ' p.nome AS aluno_nome, pl.nome AS plano_nome, u.nome AS unidade_nome'
            . ' FROM contratos c'
            . ' INNER JOIN alunos a ON a.id = c.aluno_id'
            . ' INNER JOIN pessoas p ON p.id = a.pessoa_id'
            . ' INNER JOIN planos pl ON pl.id = c.plano_id'
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

        if ($search !== '') {
            $where[] = '(p.nome LIKE :search_nome OR c.numero LIKE :search_numero)';
            $term = '%' . $search . '%';
            $parameters['search_nome'] = $term;
            $parameters['search_numero'] = $term;
        }

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY c.id DESC LIMIT 200';
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function create(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO contratos (public_id, aluno_id, rede_id, unidade_id, plano_id, numero,'
            . ' data_inicio, data_fim, renovacao_automatica, dia_vencimento, valor_mensal, status,'
            . ' observacoes, created_by, updated_by)'
            . ' VALUES (:public_id, :aluno_id, :rede_id, :unidade_id, :plano_id, :numero,'
            . ' :data_inicio, :data_fim, :renovacao_automatica, :dia_vencimento, :valor_mensal,'
            . ' :status, :observacoes, :created_by, :updated_by)'
        );
        $statement->execute($this->parameters($data));

        return (int) $this->pdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $parameters = $this->parameters($data);
        unset($parameters['public_id'], $parameters['aluno_id'], $parameters['rede_id'],
            $parameters['unidade_id'], $parameters['plano_id'], $parameters['numero'],
            $parameters['created_by']);
        $parameters['id'] = $id;

        $statement = $this->pdo()->prepare(
            'UPDATE contratos SET data_inicio = :data_inicio, data_fim = :data_fim,'
            . ' renovacao_automatica = :renovacao_automatica, dia_vencimento = :dia_vencimento,'
            . ' valor_mensal = :valor_mensal, status = :status, observacoes = :observacoes,'
            . ' updated_by = :updated_by WHERE id = :id'
        );
        $statement->execute($parameters);
    }

    public function updateStatus(int $id, string $status): void
    {
        $statement = $this->pdo()->prepare('UPDATE contratos SET status = :status WHERE id = :id');
        $statement->execute(['status' => $status, 'id' => $id]);
    }

    private function parameters(array $data): array
    {
        return [
            'public_id' => $data['public_id'] ?? '',
            'aluno_id' => (int) $data['aluno_id'],
            'rede_id' => (int) $data['rede_id'],
            'unidade_id' => (int) $data['unidade_id'],
            'plano_id' => (int) $data['plano_id'],
            'numero' => $data['numero'],
            'data_inicio' => $data['data_inicio'],
            'data_fim' => $data['data_fim'] ?: null,
            'renovacao_automatica' => !empty($data['renovacao_automatica']) ? 1 : 0,
            'dia_vencimento' => $data['dia_vencimento'] !== '' && $data['dia_vencimento'] !== null
                ? (int) $data['dia_vencimento']
                : null,
            'valor_mensal' => $data['valor_mensal'],
            'status' => $data['status'] ?? 'ATIVO',
            'observacoes' => $data['observacoes'] ?: null,
            'created_by' => $data['created_by'] ?? null,
            'updated_by' => $data['updated_by'] ?? null,
        ];
    }
}

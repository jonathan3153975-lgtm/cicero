<?php

declare(strict_types=1);

namespace App\Repositories;

final class MatriculaRepository extends Repository
{
    private const ACTIVE_STATUSES = ['ATIVA', 'SUSPENSA'];

    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT m.id, m.public_id, m.aluno_id, m.rede_id, m.unidade_id, m.contrato_id,'
            . ' m.plano_unidade_preco_id, m.data_matricula, m.data_inicio, m.data_encerramento,'
            . ' m.motivo_encerramento, m.status, m.observacoes,'
            . ' p.nome AS aluno_nome, pl.nome AS plano_nome, u.nome AS unidade_nome,'
            . ' pp.plano_id, pp.valor_mensal'
            . ' FROM matriculas m'
            . ' INNER JOIN alunos a ON a.id = m.aluno_id'
            . ' INNER JOIN pessoas p ON p.id = a.pessoa_id'
            . ' INNER JOIN plano_unidade_precos pp ON pp.id = m.plano_unidade_preco_id'
            . ' INNER JOIN planos pl ON pl.id = pp.plano_id'
            . ' INNER JOIN unidades u ON u.id = m.unidade_id'
            . ' WHERE m.id = :id LIMIT 1'
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
        $sql = 'SELECT m.id, m.aluno_id, m.rede_id, m.unidade_id, m.contrato_id,'
            . ' m.data_matricula, m.data_inicio, m.data_encerramento, m.status,'
            . ' p.nome AS aluno_nome, pl.nome AS plano_nome, u.nome AS unidade_nome'
            . ' FROM matriculas m'
            . ' INNER JOIN alunos a ON a.id = m.aluno_id'
            . ' INNER JOIN pessoas p ON p.id = a.pessoa_id'
            . ' INNER JOIN plano_unidade_precos pp ON pp.id = m.plano_unidade_preco_id'
            . ' INNER JOIN planos pl ON pl.id = pp.plano_id'
            . ' INNER JOIN unidades u ON u.id = m.unidade_id';
        $where = [];
        $parameters = [];

        if ($unidadeId !== null) {
            $where[] = 'm.unidade_id = :unidade_id';
            $parameters['unidade_id'] = $unidadeId;
        } elseif ($regiaoId !== null) {
            $where[] = 'm.unidade_id IN (SELECT unidade_id FROM regiao_unidade WHERE regiao_id = :regiao_id)';
            $parameters['regiao_id'] = $regiaoId;
        } elseif ($redeId !== null) {
            $where[] = 'm.rede_id = :rede_id';
            $parameters['rede_id'] = $redeId;
        }

        if ($search !== '') {
            $where[] = '(p.nome LIKE :search_nome OR p.cpf LIKE :search_cpf)';
            $term = '%' . $search . '%';
            $parameters['search_nome'] = $term;
            $parameters['search_cpf'] = $term;
        }

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY m.id DESC LIMIT 200';
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function findActiveByAlunoUnidade(int $alunoId, int $unidadeId): ?array
    {
        $placeholders = implode(', ', array_fill(0, count(self::ACTIVE_STATUSES), '?'));
        $statement = $this->pdo()->prepare(
            'SELECT id, aluno_id, unidade_id, status FROM matriculas'
            . ' WHERE aluno_id = ? AND unidade_id = ? AND status IN (' . $placeholders . ') LIMIT 1'
        );
        $statement->execute(array_merge([$alunoId, $unidadeId], self::ACTIVE_STATUSES));
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function create(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO matriculas (public_id, aluno_id, rede_id, unidade_id, contrato_id,'
            . ' plano_unidade_preco_id, data_matricula, data_inicio, data_encerramento,'
            . ' motivo_encerramento, status, observacoes, created_by, updated_by)'
            . ' VALUES (:public_id, :aluno_id, :rede_id, :unidade_id, :contrato_id,'
            . ' :plano_unidade_preco_id, :data_matricula, :data_inicio, :data_encerramento,'
            . ' :motivo_encerramento, :status, :observacoes, :created_by, :updated_by)'
        );
        $statement->execute($this->parameters($data));

        return (int) $this->pdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $parameters = $this->parameters($data);
        unset(
            $parameters['public_id'],
            $parameters['aluno_id'],
            $parameters['rede_id'],
            $parameters['unidade_id'],
            $parameters['contrato_id'],
            $parameters['plano_unidade_preco_id'],
            $parameters['created_by']
        );
        $parameters['id'] = $id;

        $statement = $this->pdo()->prepare(
            'UPDATE matriculas SET data_matricula = :data_matricula, data_inicio = :data_inicio,'
            . ' data_encerramento = :data_encerramento, motivo_encerramento = :motivo_encerramento,'
            . ' status = :status, observacoes = :observacoes, updated_by = :updated_by'
            . ' WHERE id = :id'
        );
        $statement->execute($parameters);
    }

    private function parameters(array $data): array
    {
        return [
            'public_id' => $data['public_id'] ?? '',
            'aluno_id' => (int) $data['aluno_id'],
            'rede_id' => (int) $data['rede_id'],
            'unidade_id' => (int) $data['unidade_id'],
            'contrato_id' => $data['contrato_id'] !== null ? (int) $data['contrato_id'] : null,
            'plano_unidade_preco_id' => (int) $data['plano_unidade_preco_id'],
            'data_matricula' => $data['data_matricula'],
            'data_inicio' => $data['data_inicio'],
            'data_encerramento' => $data['data_encerramento'] ?: null,
            'motivo_encerramento' => $data['motivo_encerramento'] ?: null,
            'status' => $data['status'] ?? 'ATIVA',
            'observacoes' => $data['observacoes'] ?: null,
            'created_by' => $data['created_by'] ?? null,
            'updated_by' => $data['updated_by'] ?? null,
        ];
    }
}

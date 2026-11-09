<?php

declare(strict_types=1);

namespace App\Repositories;

final class ReservaRepository extends Repository
{
    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT r.id, r.rede_id, r.unidade_id, r.aluno_id, r.turma_horario_id, r.data,'
            . ' r.status, r.observacao, t.nome AS turma_nome, th.dia_semana,'
            . ' th.hora_inicio, th.hora_fim, p.nome AS aluno_nome'
            . ' FROM reservas r'
            . ' INNER JOIN turma_horarios th ON th.id = r.turma_horario_id'
            . ' INNER JOIN turmas t ON t.id = th.turma_id'
            . ' INNER JOIN alunos a ON a.id = r.aluno_id'
            . ' INNER JOIN pessoas p ON p.id = a.pessoa_id'
            . ' WHERE r.id = :id LIMIT 1'
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
        $sql = 'SELECT r.id, r.rede_id, r.unidade_id, r.aluno_id, r.turma_horario_id, r.data,'
            . ' r.status, r.observacao, t.nome AS turma_nome, th.hora_inicio, th.hora_fim,'
            . ' p.nome AS aluno_nome, u.nome AS unidade_nome'
            . ' FROM reservas r'
            . ' INNER JOIN turma_horarios th ON th.id = r.turma_horario_id'
            . ' INNER JOIN turmas t ON t.id = th.turma_id'
            . ' INNER JOIN alunos a ON a.id = r.aluno_id'
            . ' INNER JOIN pessoas p ON p.id = a.pessoa_id'
            . ' INNER JOIN unidades u ON u.id = r.unidade_id';
        $where = [];
        $parameters = [];

        if ($unidadeId !== null) {
            $where[] = 'r.unidade_id = :unidade_id';
            $parameters['unidade_id'] = $unidadeId;
        } elseif ($regiaoId !== null) {
            $where[] = 'r.unidade_id IN (SELECT unidade_id FROM regiao_unidade WHERE regiao_id = :regiao_id)';
            $parameters['regiao_id'] = $regiaoId;
        } elseif ($redeId !== null) {
            $where[] = 'r.rede_id = :rede_id';
            $parameters['rede_id'] = $redeId;
        }

        if ($search !== '') {
            $where[] = 'p.nome LIKE :search_nome';
            $parameters['search_nome'] = '%' . $search . '%';
        }

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY r.data DESC, r.id DESC LIMIT 200';
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function create(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO reservas (rede_id, unidade_id, aluno_id, turma_horario_id, data,'
            . ' status, observacao, created_by, updated_by)'
            . ' VALUES (:rede_id, :unidade_id, :aluno_id, :turma_horario_id, :data,'
            . ' :status, :observacao, :created_by, :updated_by)'
        );
        $statement->execute([
            'rede_id' => (int) $data['rede_id'],
            'unidade_id' => (int) $data['unidade_id'],
            'aluno_id' => (int) $data['aluno_id'],
            'turma_horario_id' => (int) $data['turma_horario_id'],
            'data' => $data['data'],
            'status' => $data['status'] ?? 'CONFIRMADA',
            'observacao' => $data['observacao'] ?: null,
            'created_by' => $data['created_by'] ?? null,
            'updated_by' => $data['updated_by'] ?? null,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function updateStatus(int $id, string $status): void
    {
        $statement = $this->pdo()->prepare('UPDATE reservas SET status = :status WHERE id = :id');
        $statement->execute(['status' => $status, 'id' => $id]);
    }

    public function countConfirmed(int $turmaHorarioId, string $data): int
    {
        $statement = $this->pdo()->prepare(
            'SELECT COUNT(*) FROM reservas WHERE turma_horario_id = :turma_horario_id'
            . ' AND data = :data AND status = \'CONFIRMADA\''
        );
        $statement->execute(['turma_horario_id' => $turmaHorarioId, 'data' => $data]);

        return (int) $statement->fetchColumn();
    }

    public function existsActive(int $alunoId, int $turmaHorarioId, string $data): bool
    {
        $statement = $this->pdo()->prepare(
            'SELECT 1 FROM reservas WHERE aluno_id = :aluno_id AND turma_horario_id = :turma_horario_id'
            . ' AND data = :data AND status = \'CONFIRMADA\' LIMIT 1'
        );
        $statement->execute([
            'aluno_id' => $alunoId,
            'turma_horario_id' => $turmaHorarioId,
            'data' => $data,
        ]);

        return $statement->fetchColumn() !== false;
    }
}

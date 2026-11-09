<?php

declare(strict_types=1);

namespace App\Repositories;

final class FrequenciaRepository extends Repository
{
    public function forScope(
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        string $search = ''
    ): array {
        $sql = 'SELECT f.id, f.rede_id, f.unidade_id, f.aluno_id, f.turma_id, f.turma_horario_id,'
            . ' f.data, f.presenca, f.origem, p.nome AS aluno_nome, t.nome AS turma_nome,'
            . ' u.nome AS unidade_nome'
            . ' FROM frequencias f'
            . ' INNER JOIN alunos a ON a.id = f.aluno_id'
            . ' INNER JOIN pessoas p ON p.id = a.pessoa_id'
            . ' INNER JOIN turmas t ON t.id = f.turma_id'
            . ' INNER JOIN unidades u ON u.id = f.unidade_id';
        $where = [];
        $parameters = [];

        if ($unidadeId !== null) {
            $where[] = 'f.unidade_id = :unidade_id';
            $parameters['unidade_id'] = $unidadeId;
        } elseif ($regiaoId !== null) {
            $where[] = 'f.unidade_id IN (SELECT unidade_id FROM regiao_unidade WHERE regiao_id = :regiao_id)';
            $parameters['regiao_id'] = $regiaoId;
        } elseif ($redeId !== null) {
            $where[] = 'f.rede_id = :rede_id';
            $parameters['rede_id'] = $redeId;
        }

        if ($search !== '') {
            $where[] = 'p.nome LIKE :search_nome';
            $parameters['search_nome'] = '%' . $search . '%';
        }

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY f.data DESC, f.id DESC LIMIT 200';
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function create(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO frequencias (rede_id, unidade_id, aluno_id, turma_id, turma_horario_id,'
            . ' data, presenca, origem, registrado_por)'
            . ' VALUES (:rede_id, :unidade_id, :aluno_id, :turma_id, :turma_horario_id,'
            . ' :data, :presenca, :origem, :registrado_por)'
        );
        $statement->execute([
            'rede_id' => (int) $data['rede_id'],
            'unidade_id' => (int) $data['unidade_id'],
            'aluno_id' => (int) $data['aluno_id'],
            'turma_id' => (int) $data['turma_id'],
            'turma_horario_id' => $data['turma_horario_id'] !== null
                ? (int) $data['turma_horario_id']
                : null,
            'data' => $data['data'],
            'presenca' => !empty($data['presenca']) ? 1 : 0,
            'origem' => $data['origem'] ?? 'MANUAL',
            'registrado_por' => $data['registrado_por'] ?? null,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function exists(int $alunoId, int $turmaId, string $data, ?int $turmaHorarioId): bool
    {
        $sql = 'SELECT 1 FROM frequencias WHERE aluno_id = :aluno_id AND turma_id = :turma_id'
            . ' AND data = :data';
        $parameters = ['aluno_id' => $alunoId, 'turma_id' => $turmaId, 'data' => $data];

        if ($turmaHorarioId !== null) {
            $sql .= ' AND turma_horario_id = :turma_horario_id';
            $parameters['turma_horario_id'] = $turmaHorarioId;
        } else {
            $sql .= ' AND turma_horario_id IS NULL';
        }

        $sql .= ' LIMIT 1';
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchColumn() !== false;
    }
}

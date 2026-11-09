<?php

declare(strict_types=1);

namespace App\Repositories;

final class TurmaHorarioRepository extends Repository
{
    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT th.id, th.turma_id, th.dia_semana, th.hora_inicio, th.hora_fim,'
            . ' th.professor_pessoa_id, th.status, t.nome AS turma_nome, t.rede_id, t.unidade_id'
            . ' FROM turma_horarios th'
            . ' INNER JOIN turmas t ON t.id = th.turma_id'
            . ' WHERE th.id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function forTurma(int $turmaId): array
    {
        $statement = $this->pdo()->prepare(
            'SELECT th.id, th.turma_id, th.dia_semana, th.hora_inicio, th.hora_fim,'
            . ' th.professor_pessoa_id, th.status, p.nome AS professor_nome'
            . ' FROM turma_horarios th'
            . ' LEFT JOIN pessoas p ON p.id = th.professor_pessoa_id'
            . ' WHERE th.turma_id = :turma_id ORDER BY th.dia_semana ASC, th.hora_inicio ASC'
        );
        $statement->execute(['turma_id' => $turmaId]);

        return $statement->fetchAll();
    }

    public function create(int $turmaId, array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO turma_horarios (turma_id, dia_semana, hora_inicio, hora_fim,'
            . ' professor_pessoa_id, status)'
            . ' VALUES (:turma_id, :dia_semana, :hora_inicio, :hora_fim, :professor_pessoa_id, \'ATIVO\')'
        );
        $statement->execute([
            'turma_id' => $turmaId,
            'dia_semana' => (int) $data['dia_semana'],
            'hora_inicio' => $data['hora_inicio'],
            'hora_fim' => $data['hora_fim'],
            'professor_pessoa_id' => $data['professor_pessoa_id'] !== null
                ? (int) $data['professor_pessoa_id']
                : null,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function deactivate(int $id): void
    {
        $statement = $this->pdo()->prepare(
            'UPDATE turma_horarios SET status = \'INATIVO\' WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
    }

    public function hasOverlap(
        int $turmaId,
        int $diaSemana,
        string $horaInicio,
        string $horaFim,
        ?int $excludeId = null
    ): bool {
        $sql = 'SELECT 1 FROM turma_horarios WHERE turma_id = :turma_id'
            . ' AND dia_semana = :dia_semana AND status = \'ATIVO\''
            . ' AND hora_inicio < :hora_fim AND hora_fim > :hora_inicio';

        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
        }

        $sql .= ' LIMIT 1';
        $statement = $this->pdo()->prepare($sql);
        $parameters = [
            'turma_id' => $turmaId,
            'dia_semana' => $diaSemana,
            'hora_inicio' => $horaInicio,
            'hora_fim' => $horaFim,
        ];

        if ($excludeId !== null) {
            $parameters['exclude_id'] = $excludeId;
        }

        $statement->execute($parameters);

        return $statement->fetchColumn() !== false;
    }

    public function hasProfessorOverlap(
        int $professorPessoaId,
        int $diaSemana,
        string $horaInicio,
        string $horaFim,
        ?int $excludeId = null
    ): bool {
        $sql = 'SELECT 1 FROM turma_horarios WHERE professor_pessoa_id = :professor_id'
            . ' AND dia_semana = :dia_semana AND status = \'ATIVO\''
            . ' AND hora_inicio < :hora_fim AND hora_fim > :hora_inicio';

        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
        }

        $sql .= ' LIMIT 1';
        $statement = $this->pdo()->prepare($sql);
        $parameters = [
            'professor_id' => $professorPessoaId,
            'dia_semana' => $diaSemana,
            'hora_inicio' => $horaInicio,
            'hora_fim' => $horaFim,
        ];

        if ($excludeId !== null) {
            $parameters['exclude_id'] = $excludeId;
        }

        $statement->execute($parameters);

        return $statement->fetchColumn() !== false;
    }
}

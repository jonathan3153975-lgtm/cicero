<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;
use App\Repositories\TurmaHorarioRepository;
use App\Repositories\TurmaRepository;
use App\Repositories\UnidadeRepository;
use App\Validators\Validator;

final class TurmaService
{
    private const STATUSES = ['ATIVA', 'INATIVA'];

    public function __construct(
        private readonly TurmaRepository $turmas,
        private readonly TurmaHorarioRepository $horarios,
        private readonly UnidadeRepository $unidades
    ) {
    }

    public function create(
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        array $data
    ): int {
        [$targetRedeId, $targetUnidadeId] = $this->resolveUnidade($data, $redeId, $unidadeId, $regiaoId);
        $data = $this->normalize($data, $targetRedeId, $targetUnidadeId, $actorId);
        $this->validate($data);

        return $this->turmas->create($data);
    }

    public function update(
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        int $id,
        array $data
    ): void {
        $turma = $this->assertCanAccess($id, $redeId, $unidadeId, $regiaoId);
        [$targetRedeId, $targetUnidadeId] = $this->resolveUnidade(
            $data,
            $redeId,
            $unidadeId,
            $regiaoId,
            $turma
        );
        $data = $this->normalize($data, $targetRedeId, $targetUnidadeId, $actorId);
        $this->validate($data);

        $this->turmas->update($id, $data);
    }

    public function assertCanAccess(
        int $turmaId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): array {
        $turma = $this->turmas->findById($turmaId);

        if ($turma === null) {
            throw new ValidationException(['turma' => ['Turma inexistente.']]);
        }

        if ($redeId === null && $unidadeId === null && $regiaoId === null) {
            return $turma;
        }

        if ($unidadeId !== null && (int) $turma['unidade_id'] === $unidadeId) {
            return $turma;
        }

        if (
            $unidadeId === null
            && $regiaoId !== null
            && $this->unidades->isInRegiao((int) $turma['unidade_id'], $regiaoId)
        ) {
            return $turma;
        }

        if (
            $unidadeId === null
            && $regiaoId === null
            && $redeId !== null
            && (int) $turma['rede_id'] === $redeId
        ) {
            return $turma;
        }

        throw new ForbiddenException('Turma fora do seu contexto.');
    }

    public function forScope(
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        string $search = ''
    ): array {
        return $this->turmas->forScope($redeId, $unidadeId, $regiaoId, $search);
    }

    public function horariosForTurma(int $turmaId): array
    {
        return $this->horarios->forTurma($turmaId);
    }

    public function addHorario(
        int $turmaId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        array $data
    ): int {
        $this->assertCanAccess($turmaId, $redeId, $unidadeId, $regiaoId);
        $dia = (int) ($data['dia_semana'] ?? 0);
        $inicio = trim((string) ($data['hora_inicio'] ?? ''));
        $fim = trim((string) ($data['hora_fim'] ?? ''));
        $professorId = !empty($data['professor_pessoa_id'])
            ? (int) $data['professor_pessoa_id']
            : null;

        $errors = [];

        if ($dia < 1 || $dia > 7) {
            $errors['dia_semana'][] = 'Informe um dia da semana entre 1 e 7.';
        }

        if (!preg_match('/^\d{2}:\d{2}$/', $inicio) || !preg_match('/^\d{2}:\d{2}$/', $fim)) {
            $errors['hora_inicio'][] = 'Informe horários no formato HH:MM.';
        } elseif ($inicio >= $fim) {
            $errors['hora_fim'][] = 'O horário final deve ser posterior ao inicial.';
        } elseif ($this->horarios->hasOverlap($turmaId, $dia, $inicio, $fim)) {
            $errors['hora_inicio'][] = 'Já existe um horário sobreposto nesta turma.';
        } elseif (
            $professorId !== null
            && $this->horarios->hasProfessorOverlap($professorId, $dia, $inicio, $fim)
        ) {
            $errors['professor_pessoa_id'][] = 'O professor já possui horário sobreposto.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return $this->horarios->create($turmaId, [
            'dia_semana' => $dia,
            'hora_inicio' => $inicio,
            'hora_fim' => $fim,
            'professor_pessoa_id' => $professorId,
        ]);
    }

    public function removeHorario(
        int $turmaId,
        int $horarioId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): void {
        $this->assertCanAccess($turmaId, $redeId, $unidadeId, $regiaoId);
        $horario = $this->horarios->findById($horarioId);

        if ($horario === null || (int) $horario['turma_id'] !== $turmaId) {
            throw new ValidationException(['horario' => ['Horário inexistente para esta turma.']]);
        }

        $this->horarios->deactivate($horarioId);
    }

    private function resolveUnidade(
        array $data,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        ?array $turma = null
    ): array {
        $targetUnidadeId = $unidadeId ?? (int) ($data['unidade_id'] ?? 0);

        if ($targetUnidadeId <= 0 && $turma !== null) {
            $targetUnidadeId = (int) $turma['unidade_id'];
        }

        if ($targetUnidadeId <= 0) {
            throw new ValidationException(['unidade_id' => ['Selecione a unidade.']]);
        }

        $unidade = $this->unidades->findById($targetUnidadeId);

        if ($unidade === null) {
            throw new ValidationException(['unidade_id' => ['Unidade inexistente.']]);
        }

        $targetRedeId = (int) $unidade['rede_id'];

        if ($redeId !== null && $targetRedeId !== $redeId) {
            throw new ValidationException(['unidade_id' => ['A unidade não pertence à sua rede.']]);
        }

        if ($regiaoId !== null && !$this->unidades->isInRegiao($targetUnidadeId, $regiaoId)) {
            throw new ValidationException(['unidade_id' => ['A unidade não pertence à sua região.']]);
        }

        return [$targetRedeId, $targetUnidadeId];
    }

    private function normalize(array $data, int $redeId, int $unidadeId, int $actorId): array
    {
        return [
            'rede_id' => $redeId,
            'unidade_id' => $unidadeId,
            'modalidade_id' => !empty($data['modalidade_id']) ? (int) $data['modalidade_id'] : null,
            'nome' => trim((string) ($data['nome'] ?? '')),
            'capacidade' => trim((string) ($data['capacidade'] ?? '')),
            'sala' => trim((string) ($data['sala'] ?? '')),
            'status' => strtoupper(trim((string) ($data['status'] ?? 'ATIVA'))),
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ];
    }

    private function validate(array $data): void
    {
        $validator = (new Validator($data))
            ->required('nome')
            ->maxLength('nome', 120)
            ->in('status', self::STATUSES);
        $errors = $validator->errors();

        if ($data['capacidade'] !== '' && (!ctype_digit($data['capacidade']) || (int) $data['capacidade'] < 0)) {
            $errors['capacidade'][] = 'Informe uma capacidade válida.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }
    }
}

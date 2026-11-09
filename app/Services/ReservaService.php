<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;
use App\Repositories\ReservaRepository;
use App\Repositories\TurmaHorarioRepository;
use App\Repositories\UnidadeRepository;
use DateTimeImmutable;

final class ReservaService
{
    public function __construct(
        private readonly AlunoService $alunos,
        private readonly TurmaService $turmas,
        private readonly TurmaHorarioRepository $horarios,
        private readonly ReservaRepository $reservas,
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
        $alunoId = (int) ($data['aluno_id'] ?? 0);
        $horarioId = (int) ($data['turma_horario_id'] ?? 0);
        $dataReserva = trim((string) ($data['data'] ?? ''));
        $aluno = $this->alunos->assertCanAccess($alunoId, $redeId, $unidadeId, $regiaoId);
        $horario = $this->horarios->findById($horarioId);

        if ($horario === null) {
            throw new ValidationException(['turma_horario_id' => ['Horário inexistente.']]);
        }

        $turma = $this->turmas->assertCanAccess(
            (int) $horario['turma_id'],
            $redeId,
            $unidadeId,
            $regiaoId
        );

        if ((int) $aluno['unidade_id'] !== (int) $turma['unidade_id']) {
            throw new ValidationException(['aluno_id' => ['O aluno não pertence à unidade da turma.']]);
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataReserva) !== 1) {
            throw new ValidationException(['data' => ['Informe a data no formato AAAA-MM-DD.']]);
        }

        $weekday = (int) (new DateTimeImmutable($dataReserva))->format('N');

        if ($weekday !== (int) $horario['dia_semana']) {
            throw new ValidationException(['data' => ['A data não corresponde ao dia da semana do horário.']]);
        }

        if ($this->reservas->existsActive($alunoId, $horarioId, $dataReserva)) {
            throw new ValidationException(['data' => ['Já existe reserva ativa para este aluno/horário/data.']]);
        }

        if (
            $turma['capacidade'] !== null
            && $this->reservas->countConfirmed($horarioId, $dataReserva) >= (int) $turma['capacidade']
        ) {
            throw new ValidationException(['turma_horario_id' => ['Capacidade da turma esgotada para esta data.']]);
        }

        return $this->reservas->create([
            'rede_id' => (int) $aluno['rede_id'],
            'unidade_id' => (int) $aluno['unidade_id'],
            'aluno_id' => $alunoId,
            'turma_horario_id' => $horarioId,
            'data' => $dataReserva,
            'status' => 'CONFIRMADA',
            'observacao' => trim((string) ($data['observacao'] ?? '')),
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);
    }

    public function cancel(
        int $reservaId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): void {
        $this->assertCanAccess($reservaId, $redeId, $unidadeId, $regiaoId);
        $this->reservas->updateStatus($reservaId, 'CANCELADA');
    }

    public function assertCanAccess(
        int $reservaId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): array {
        $reserva = $this->reservas->findById($reservaId);

        if ($reserva === null) {
            throw new ValidationException(['reserva' => ['Reserva inexistente.']]);
        }

        if ($redeId === null && $unidadeId === null && $regiaoId === null) {
            return $reserva;
        }

        if ($unidadeId !== null && (int) $reserva['unidade_id'] === $unidadeId) {
            return $reserva;
        }

        if (
            $unidadeId === null
            && $regiaoId !== null
            && $this->unidades->isInRegiao((int) $reserva['unidade_id'], $regiaoId)
        ) {
            return $reserva;
        }

        if (
            $unidadeId === null
            && $regiaoId === null
            && $redeId !== null
            && (int) $reserva['rede_id'] === $redeId
        ) {
            return $reserva;
        }

        throw new ForbiddenException('Reserva fora do seu contexto.');
    }

    public function forScope(
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        string $search = ''
    ): array {
        return $this->reservas->forScope($redeId, $unidadeId, $regiaoId, $search);
    }
}

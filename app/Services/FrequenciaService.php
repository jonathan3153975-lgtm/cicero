<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ValidationException;
use App\Repositories\FrequenciaRepository;
use App\Repositories\TurmaHorarioRepository;

final class FrequenciaService
{
    public function __construct(
        private readonly AlunoService $alunos,
        private readonly TurmaService $turmas,
        private readonly TurmaHorarioRepository $horarios,
        private readonly FrequenciaRepository $frequencias
    ) {
    }

    public function register(
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        array $data
    ): int {
        $alunoId = (int) ($data['aluno_id'] ?? 0);
        $turmaId = (int) ($data['turma_id'] ?? 0);
        $horarioId = !empty($data['turma_horario_id']) ? (int) $data['turma_horario_id'] : null;
        $dataPresenca = trim((string) ($data['data'] ?? ''));

        $aluno = $this->alunos->assertCanAccess($alunoId, $redeId, $unidadeId, $regiaoId);
        $turma = $this->turmas->assertCanAccess($turmaId, $redeId, $unidadeId, $regiaoId);

        if ((int) $aluno['unidade_id'] !== (int) $turma['unidade_id']) {
            throw new ValidationException(['aluno_id' => ['O aluno não pertence à unidade da turma.']]);
        }

        if ($horarioId !== null) {
            $horario = $this->horarios->findById($horarioId);

            if ($horario === null || (int) $horario['turma_id'] !== $turmaId) {
                throw new ValidationException(['turma_horario_id' => ['Horário não pertence à turma.']]);
            }
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataPresenca) !== 1) {
            throw new ValidationException(['data' => ['Informe a data no formato AAAA-MM-DD.']]);
        }

        if ($this->frequencias->exists($alunoId, $turmaId, $dataPresenca, $horarioId)) {
            throw new ValidationException(['data' => ['Já existe frequência registrada para este aluno/turma/data.']]);
        }

        return $this->frequencias->create([
            'rede_id' => (int) $aluno['rede_id'],
            'unidade_id' => (int) $aluno['unidade_id'],
            'aluno_id' => $alunoId,
            'turma_id' => $turmaId,
            'turma_horario_id' => $horarioId,
            'data' => $dataPresenca,
            'presenca' => !empty($data['presenca']),
            'origem' => strtoupper(trim((string) ($data['origem'] ?? 'MANUAL'))),
            'registrado_por' => $actorId,
        ]);
    }

    public function forScope(
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        string $search = ''
    ): array {
        return $this->frequencias->forScope($redeId, $unidadeId, $regiaoId, $search);
    }
}

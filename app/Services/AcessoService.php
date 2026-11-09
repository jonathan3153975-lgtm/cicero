<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;
use App\Repositories\DispositivoAcessoRepository;
use App\Repositories\EventoAcessoRepository;
use App\Repositories\UnidadeRepository;
use App\Validators\Validator;

final class AcessoService
{
    public function __construct(
        private readonly DispositivoAcessoRepository $dispositivos,
        private readonly EventoAcessoRepository $eventos,
        private readonly AlunoService $alunos,
        private readonly UnidadeRepository $unidades
    ) {
    }

    public function createDevice(
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        array $data
    ): int {
        [$targetRedeId, $targetUnidadeId] = $this->resolveUnidade($data, $redeId, $unidadeId, $regiaoId);
        $data = $this->normalizeDevice($data);
        $this->validateDevice($data);

        return $this->dispositivos->create([
            'rede_id' => $targetRedeId,
            'unidade_id' => $targetUnidadeId,
            'nome' => $data['nome'],
            'tipo' => $data['tipo'],
            'identificador' => $data['identificador'],
            'direcao' => $data['direcao'],
            'status' => $data['status'],
        ]);
    }

    public function updateDevice(
        int $id,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        array $data
    ): void {
        $device = $this->assertDeviceAccess($id, $redeId, $unidadeId, $regiaoId);
        $data = $this->normalizeDevice($data);
        $this->validateDevice($data);

        $this->dispositivos->update($id, $data);
    }

    public function recordEvent(
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        array $data
    ): int {
        $device = $this->assertDeviceAccess(
            (int) ($data['dispositivo_id'] ?? 0),
            $redeId,
            $unidadeId,
            $regiaoId
        );

        $alunoId = !empty($data['aluno_id']) ? (int) $data['aluno_id'] : null;
        $pessoaId = !empty($data['pessoa_id']) ? (int) $data['pessoa_id'] : null;

        if ($alunoId !== null) {
            $aluno = $this->alunos->assertCanAccess($alunoId, $redeId, $unidadeId, $regiaoId);

            if ((int) $aluno['unidade_id'] !== (int) $device['unidade_id']) {
                throw new ValidationException(['aluno_id' => ['O aluno não pertence à unidade do dispositivo.']]);
            }

            $pessoaId = (int) $aluno['pessoa_id'];
        }

        if ($pessoaId === null) {
            throw new ValidationException(['pessoa_id' => ['Informe a pessoa ou o aluno do evento.']]);
        }

        $tipo = strtoupper(trim((string) ($data['tipo'] ?? 'ENTRADA')));
        $ocorridoEm = trim((string) ($data['ocorrido_em'] ?? date('Y-m-d H:i:s')));

        if (!in_array($tipo, ['ENTRADA', 'SAIDA', 'TENTATIVA'], true)) {
            throw new ValidationException(['tipo' => ['Tipo de evento inválido.']]);
        }

        return $this->eventos->create([
            'dispositivo_id' => (int) $device['id'],
            'rede_id' => (int) $device['rede_id'],
            'unidade_id' => (int) $device['unidade_id'],
            'pessoa_id' => $pessoaId,
            'aluno_id' => $alunoId,
            'tipo' => $tipo,
            'ocorrido_em' => $ocorridoEm,
            'autorizado' => !empty($data['autorizado']),
            'motivo_negacao' => trim((string) ($data['motivo_negacao'] ?? '')),
            'payload_json' => $data['payload_json'] ?? null,
        ]);
    }

    public function assertDeviceAccess(
        int $id,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): array {
        $device = $this->dispositivos->findById($id);

        if ($device === null) {
            throw new ValidationException(['dispositivo' => ['Dispositivo inexistente.']]);
        }

        if ($redeId === null && $unidadeId === null && $regiaoId === null) {
            return $device;
        }

        if ($unidadeId !== null && (int) $device['unidade_id'] === $unidadeId) {
            return $device;
        }

        if (
            $unidadeId === null
            && $regiaoId !== null
            && $this->unidades->isInRegiao((int) $device['unidade_id'], $regiaoId)
        ) {
            return $device;
        }

        if (
            $unidadeId === null
            && $regiaoId === null
            && $redeId !== null
            && (int) $device['rede_id'] === $redeId
        ) {
            return $device;
        }

        throw new ForbiddenException('Dispositivo fora do seu contexto.');
    }

    public function devicesForScope(?int $redeId, ?int $unidadeId, ?int $regiaoId): array
    {
        return $this->dispositivos->forScope($redeId, $unidadeId, $regiaoId);
    }

    public function eventsForScope(?int $redeId, ?int $unidadeId, ?int $regiaoId): array
    {
        return $this->eventos->forScope($redeId, $unidadeId, $regiaoId);
    }

    private function resolveUnidade(
        array $data,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): array {
        $targetUnidadeId = $unidadeId ?? (int) ($data['unidade_id'] ?? 0);

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

    private function normalizeDevice(array $data): array
    {
        return [
            'nome' => trim((string) ($data['nome'] ?? '')),
            'tipo' => strtoupper(trim((string) ($data['tipo'] ?? 'CATRACA'))),
            'identificador' => trim((string) ($data['identificador'] ?? '')),
            'direcao' => strtoupper(trim((string) ($data['direcao'] ?? 'AMBOS'))),
            'status' => strtoupper(trim((string) ($data['status'] ?? 'ATIVO'))),
        ];
    }

    private function validateDevice(array $data): void
    {
        $validator = (new Validator($data))
            ->required('nome')
            ->maxLength('nome', 120)
            ->required('identificador')
            ->maxLength('identificador', 120)
            ->in('direcao', ['ENTRADA', 'SAIDA', 'AMBOS'])
            ->in('status', ['ATIVO', 'INATIVO']);

        if ($validator->fails()) {
            throw new ValidationException($validator->errors());
        }
    }
}

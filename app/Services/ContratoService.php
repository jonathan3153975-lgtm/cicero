<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;
use App\Repositories\ContratoRepository;
use App\Repositories\UnidadeRepository;
use App\Validators\Validator;

final class ContratoService
{
    private const STATUSES = ['ATIVO', 'SUSPENSO', 'CANCELADO', 'ENCERRADO', 'TRANSFERIDO'];

    public function __construct(
        private readonly ContratoRepository $contratos,
        private readonly UnidadeRepository $unidades
    ) {
    }

    public function update(
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        int $id,
        array $data
    ): void {
        $contrato = $this->assertCanAccess($id, $redeId, $unidadeId, $regiaoId);
        $data = $this->normalize($data, $contrato);
        $this->validate($data);

        $this->contratos->update($id, array_merge($contrato, $data, [
            'updated_by' => $actorId,
        ]));
    }

    public function assertCanAccess(
        int $id,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): array {
        $contrato = $this->contratos->findById($id);

        if ($contrato === null) {
            throw new ValidationException(['contrato' => ['Contrato inexistente.']]);
        }

        if ($redeId === null && $unidadeId === null && $regiaoId === null) {
            return $contrato;
        }

        if ($unidadeId !== null && (int) $contrato['unidade_id'] === $unidadeId) {
            return $contrato;
        }

        if (
            $unidadeId === null
            && $regiaoId !== null
            && $this->unidades->isInRegiao((int) $contrato['unidade_id'], $regiaoId)
        ) {
            return $contrato;
        }

        if (
            $unidadeId === null
            && $regiaoId === null
            && $redeId !== null
            && (int) $contrato['rede_id'] === $redeId
        ) {
            return $contrato;
        }

        throw new ForbiddenException('Contrato fora do seu contexto.');
    }

    public function forScope(
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        string $search = ''
    ): array {
        return $this->contratos->forScope($redeId, $unidadeId, $regiaoId, $search);
    }

    private function normalize(array $data, array $contrato): array
    {
        return [
            'data_inicio' => trim((string) ($data['data_inicio'] ?? $contrato['data_inicio'])),
            'data_fim' => trim((string) ($data['data_fim'] ?? $contrato['data_fim'] ?? '')),
            'renovacao_automatica' => !empty($data['renovacao_automatica']),
            'dia_vencimento' => trim((string) ($data['dia_vencimento'] ?? $contrato['dia_vencimento'] ?? '')),
            'valor_mensal' => str_replace(',', '.', trim((string) ($data['valor_mensal'] ?? $contrato['valor_mensal']))),
            'status' => strtoupper(trim((string) ($data['status'] ?? $contrato['status']))),
            'observacoes' => trim((string) ($data['observacoes'] ?? $contrato['observacoes'] ?? '')),
        ];
    }

    private function validate(array $data): void
    {
        $validator = (new Validator($data))->in('status', self::STATUSES);
        $errors = $validator->errors();

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['data_inicio']) !== 1) {
            $errors['data_inicio'][] = 'Informe a data no formato AAAA-MM-DD.';
        }

        if ($data['data_fim'] !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['data_fim']) !== 1) {
            $errors['data_fim'][] = 'Informe a data no formato AAAA-MM-DD.';
        }

        if ($data['data_fim'] !== '' && $data['data_fim'] < $data['data_inicio']) {
            $errors['data_fim'][] = 'A data final deve ser posterior ao início.';
        }

        if ($data['dia_vencimento'] !== '') {
            $day = (int) $data['dia_vencimento'];

            if ($day < 1 || $day > 31) {
                $errors['dia_vencimento'][] = 'Informe um dia de vencimento entre 1 e 31.';
            }
        }

        if (!is_numeric($data['valor_mensal']) || (float) $data['valor_mensal'] < 0) {
            $errors['valor_mensal'][] = 'Informe um valor válido.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }
    }
}

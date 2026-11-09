<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;
use App\Repositories\PlanoPrecoRepository;
use App\Repositories\PlanoRepository;
use App\Repositories\UnidadeRepository;
use App\Validators\Validator;

final class PlanoPrecoService
{
    public function __construct(
        private readonly PlanoPrecoRepository $precos,
        private readonly PlanoRepository $planos,
        private readonly UnidadeRepository $unidades
    ) {
    }

    public function create(
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        int $planoId,
        array $data
    ): int {
        $plano = $this->planos->findById($planoId);

        if ($plano === null) {
            throw new ValidationException(['plano' => ['Plano inexistente.']]);
        }

        if ($redeId !== null && $plano['rede_id'] !== null && (int) $plano['rede_id'] !== $redeId) {
            throw new ForbiddenException('Plano fora da sua rede.');
        }

        $targetUnidadeId = $unidadeId ?? (int) ($data['unidade_id'] ?? 0);

        if ($targetUnidadeId <= 0) {
            throw new ValidationException(['unidade_id' => ['Selecione a unidade.']]);
        }

        $unidade = $this->unidades->findById($targetUnidadeId);

        if ($unidade === null) {
            throw new ValidationException(['unidade_id' => ['Unidade inexistente.']]);
        }

        if ($unidadeId !== null && (int) $unidade['id'] !== $unidadeId) {
            throw new ForbiddenException('Unidade fora do seu contexto.');
        }

        if ($regiaoId !== null && !$this->unidades->isInRegiao($targetUnidadeId, $regiaoId)) {
            throw new ForbiddenException('Unidade fora da sua região.');
        }

        if ($redeId !== null && (int) $unidade['rede_id'] !== $redeId) {
            throw new ForbiddenException('Unidade fora da sua rede.');
        }

        $data = $this->normalize($data);
        $this->validate($data);

        if ($this->precos->findByPlanoUnidadeVigencia($planoId, $targetUnidadeId, $data['vigencia_inicio']) !== null) {
            throw new ValidationException(['vigencia_inicio' => ['Já existe um preço para esta vigência.']]);
        }

        return $this->precos->create(array_merge($data, [
            'plano_id' => $planoId,
            'rede_id' => (int) $unidade['rede_id'],
            'unidade_id' => $targetUnidadeId,
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]));
    }

    public function forPlanoForScope(
        int $planoId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): array {
        return $this->precos->forPlanoForScope($planoId, $redeId, $unidadeId, $regiaoId);
    }

    private function normalize(array $data): array
    {
        return [
            'vigencia_inicio' => trim((string) ($data['vigencia_inicio'] ?? '')),
            'vigencia_fim' => trim((string) ($data['vigencia_fim'] ?? '')),
            'valor_matricula' => str_replace(',', '.', trim((string) ($data['valor_matricula'] ?? '0'))),
            'valor_mensal' => str_replace(',', '.', trim((string) ($data['valor_mensal'] ?? '0'))),
            'valor_adesao' => str_replace(',', '.', trim((string) ($data['valor_adesao'] ?? ''))),
            'taxa_administrativa' => str_replace(',', '.', trim((string) ($data['taxa_administrativa'] ?? ''))),
            'status' => strtoupper(trim((string) ($data['status'] ?? 'ATIVO'))),
        ];
    }

    private function validate(array $data): void
    {
        $validator = (new Validator($data))->in('status', ['ATIVO', 'INATIVO']);
        $errors = $validator->errors();

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['vigencia_inicio']) !== 1) {
            $errors['vigencia_inicio'][] = 'Informe a data no formato AAAA-MM-DD.';
        }

        if (
            $data['vigencia_fim'] !== ''
            && preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['vigencia_fim']) !== 1
        ) {
            $errors['vigencia_fim'][] = 'Informe a data no formato AAAA-MM-DD.';
        }

        if ($data['vigencia_fim'] !== '' && $data['vigencia_fim'] < $data['vigencia_inicio']) {
            $errors['vigencia_fim'][] = 'A vigência final deve ser posterior ao início.';
        }

        foreach (['valor_matricula', 'valor_mensal'] as $field) {
            if (!is_numeric($data[$field]) || (float) $data[$field] < 0) {
                $errors[$field][] = 'Informe um valor válido.';
            }
        }

        foreach (['valor_adesao', 'taxa_administrativa'] as $field) {
            if ($data[$field] !== '' && (!is_numeric($data[$field]) || (float) $data[$field] < 0)) {
                $errors[$field][] = 'Informe um valor válido.';
            }
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }
    }
}

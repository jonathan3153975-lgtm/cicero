<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ForbiddenException;
use App\Repositories\RedeRepository;
use App\Repositories\RegiaoRepository;
use App\Repositories\UnidadeRepository;
use App\Repositories\VinculoRepository;

final class TenantContextService
{
    public function __construct(
        private readonly VinculoRepository $vinculos,
        private readonly UnidadeRepository $unidades,
        private readonly RedeRepository $redes,
        private readonly RegiaoRepository $regioes
    ) {
    }

    public function contextsForUser(int $usuarioId): array
    {
        $contexts = [];
        $hasPlatform = false;

        foreach ($this->vinculos->profileAssignmentsForUser($usuarioId) as $assignment) {
            $redeId = $assignment['rede_id'] !== null ? (int) $assignment['rede_id'] : null;
            $unidadeId = $assignment['unidade_id'] !== null ? (int) $assignment['unidade_id'] : null;
            $regiaoId = $assignment['regiao_id'] !== null ? (int) $assignment['regiao_id'] : null;

            if ($redeId === null && $unidadeId === null && $regiaoId === null) {
                $hasPlatform = true;

                continue;
            }

            if ($unidadeId !== null) {
                $this->addUnitContext($contexts, $redeId, $unidadeId);

                continue;
            }

            if ($regiaoId !== null) {
                $this->addRegionContext($contexts, $redeId, $regiaoId);

                foreach ($this->unidades->forRegiao($regiaoId) as $unidade) {
                    $this->addUnitContext($contexts, (int) $unidade['rede_id'], (int) $unidade['id']);
                }

                continue;
            }

            if ($redeId !== null) {
                $this->addRedeContext($contexts, $redeId);
            }
        }

        if ($hasPlatform) {
            $contexts['plataforma'] = [
                'key' => 'plataforma',
                'tipo' => 'PLATAFORMA',
                'rede_id' => null,
                'unidade_id' => null,
                'regiao_id' => null,
                'label' => 'Plataforma',
                'rede_nome' => null,
                'unidade_nome' => null,
            ];

            foreach ($this->redes->allActive() as $rede) {
                $this->addRedeContext($contexts, (int) $rede['id']);
            }

            foreach ($this->unidades->allActive() as $unidade) {
                $this->addUnitContext($contexts, (int) $unidade['rede_id'], (int) $unidade['id']);
            }
        }

        usort($contexts, function (array $left, array $right): int {
            if ($left['tipo'] === 'PLATAFORMA') {
                return -1;
            }

            if ($right['tipo'] === 'PLATAFORMA') {
                return 1;
            }

            return strcmp((string) $left['label'], (string) $right['label']);
        });

        return array_values($contexts);
    }

    public function resolve(int $usuarioId, mixed $redeId, mixed $unidadeId, mixed $regiaoId = null): array
    {
        $normalizedRede = $this->normalize($redeId);
        $normalizedUnidade = $this->normalize($unidadeId);
        $normalizedRegiao = $this->normalize($regiaoId);

        foreach ($this->contextsForUser($usuarioId) as $context) {
            if (
                $context['rede_id'] === $normalizedRede
                && $context['unidade_id'] === $normalizedUnidade
                && ($context['regiao_id'] ?? null) === $normalizedRegiao
            ) {
                return $context;
            }
        }

        throw new ForbiddenException('Contexto de rede/unidade/região inválido para este usuário.');
    }

    public function defaultContext(int $usuarioId): ?array
    {
        return $this->contextsForUser($usuarioId)[0] ?? null;
    }

    private function addUnitContext(array &$contexts, ?int $redeId, int $unidadeId): void
    {
        $unidade = $this->unidades->findById($unidadeId);

        if ($unidade === null) {
            return;
        }

        $redeId ??= (int) $unidade['rede_id'];
        $rede = $this->redes->findById($redeId);

        if ($rede === null) {
            return;
        }

        $key = sprintf('rede:%d:unidade:%d', $redeId, $unidadeId);

        if (isset($contexts[$key])) {
            return;
        }

        $contexts[$key] = [
            'key' => $key,
            'tipo' => 'UNIDADE',
            'rede_id' => $redeId,
            'unidade_id' => $unidadeId,
            'regiao_id' => null,
            'label' => $rede['nome_fantasia'] . ' · ' . $unidade['nome'],
            'rede_nome' => $rede['nome_fantasia'],
            'unidade_nome' => $unidade['nome'],
        ];
    }

    private function addRegionContext(array &$contexts, ?int $redeId, int $regiaoId): void
    {
        $regiao = $this->regioes->findById($regiaoId);

        if ($regiao === null) {
            return;
        }

        $redeId ??= (int) $regiao['rede_id'];
        $rede = $this->redes->findById($redeId);

        if ($rede === null) {
            return;
        }

        $key = sprintf('rede:%d:regiao:%d', $redeId, $regiaoId);

        if (isset($contexts[$key])) {
            return;
        }

        $contexts[$key] = [
            'key' => $key,
            'tipo' => 'REGIAO',
            'rede_id' => $redeId,
            'unidade_id' => null,
            'regiao_id' => $regiaoId,
            'label' => $rede['nome_fantasia'] . ' · ' . $regiao['nome'],
            'rede_nome' => $rede['nome_fantasia'],
            'unidade_nome' => null,
            'regiao_nome' => $regiao['nome'],
        ];
    }

    private function addRedeContext(array &$contexts, int $redeId): void
    {
        $rede = $this->redes->findById($redeId);

        if ($rede === null) {
            return;
        }

        $key = sprintf('rede:%d', $redeId);

        if (isset($contexts[$key])) {
            return;
        }

        $contexts[$key] = [
            'key' => $key,
            'tipo' => 'REDE',
            'rede_id' => $redeId,
            'unidade_id' => null,
            'regiao_id' => null,
            'label' => $rede['nome_fantasia'],
            'rede_nome' => $rede['nome_fantasia'],
            'unidade_nome' => null,
        ];
    }

    private function normalize(mixed $value): ?int
    {
        if ($value === null || $value === '' || $value === '0' || $value === 0) {
            return null;
        }

        return (int) $value;
    }
}

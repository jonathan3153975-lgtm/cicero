<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ForbiddenException;
use App\Repositories\PerfilRepository;
use App\Repositories\PermissaoRepository;
use App\Repositories\UnidadeRepository;
use App\Repositories\VinculoRepository;

final class AuthorizationService
{
    public function __construct(
        private readonly VinculoRepository $vinculos,
        private readonly PermissaoRepository $permissoes,
        private readonly PerfilRepository $perfis,
        private readonly UnidadeRepository $unidades
    ) {
    }

    public function permissionsForContext(
        int $usuarioId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId = null
    ): array {
        $permissions = [];

        foreach ($this->vinculos->profileAssignmentsForUser($usuarioId) as $assignment) {
            if (!$this->scopeMatches($assignment, $redeId, $unidadeId, $regiaoId)) {
                continue;
            }

            foreach ($this->permissoes->forProfile((int) $assignment['perfil_id']) as $slug) {
                $permissions[$slug] = true;
            }
        }

        foreach ($this->vinculos->explicitPermissionsForUser($usuarioId) as $explicit) {
            if (!$this->scopeMatches($explicit, $redeId, $unidadeId, $regiaoId)) {
                continue;
            }

            $effect = strtoupper((string) $explicit['efeito']);
            $slug = (string) $explicit['slug'];

            if ($effect === 'NEGAR') {
                unset($permissions[$slug]);

                continue;
            }

            $permissions[$slug] = true;
        }

        $result = array_keys($permissions);
        sort($result);

        return $result;
    }

    public function can(
        int $usuarioId,
        string $permission,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId = null
    ): bool {
        return in_array(
            $permission,
            $this->permissionsForContext($usuarioId, $redeId, $unidadeId, $regiaoId),
            true
        );
    }

    public function assertCan(
        int $usuarioId,
        string $permission,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId = null
    ): void {
        if (!$this->can($usuarioId, $permission, $redeId, $unidadeId, $regiaoId)) {
            throw new ForbiddenException('Sem permissão para executar esta operação.');
        }
    }

    public function canGrantProfile(
        int $actorId,
        int $profileId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId = null
    ): bool {
        $target = $this->perfis->findById($profileId);

        if ($target === null) {
            return false;
        }

        if ($target['rede_id'] !== null) {
            if ($redeId === null || (int) $target['rede_id'] !== $redeId) {
                return false;
            }
        }

        $actorLevel = $this->highestLevelForContext($actorId, $redeId, $unidadeId, $regiaoId);
        $targetLevel = (int) $target['nivel'];

        if ($targetLevel > $actorLevel) {
            return false;
        }

        if ($actorLevel < 100 && $targetLevel >= $actorLevel) {
            return false;
        }

        $actorPermissions = $this->permissionsForContext($actorId, $redeId, $unidadeId, $regiaoId);

        foreach ($this->permissoes->forProfile($profileId) as $permission) {
            if (!in_array($permission, $actorPermissions, true)) {
                return false;
            }
        }

        return true;
    }

    public function highestLevelForContext(
        int $usuarioId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId = null
    ): int {
        $level = 0;

        foreach ($this->vinculos->profileAssignmentsForUser($usuarioId) as $assignment) {
            if (!$this->scopeMatches($assignment, $redeId, $unidadeId, $regiaoId)) {
                continue;
            }

            $level = max($level, (int) $assignment['nivel']);
        }

        return $level;
    }

    private function scopeMatches(
        array $scope,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId = null
    ): bool {
        $scopeRede = $scope['rede_id'] !== null ? (int) $scope['rede_id'] : null;
        $scopeUnidade = $scope['unidade_id'] !== null ? (int) $scope['unidade_id'] : null;
        $scopeRegiao = $scope['regiao_id'] !== null ? (int) $scope['regiao_id'] : null;

        if ($scopeRede === null && $scopeUnidade === null && $scopeRegiao === null) {
            return true;
        }

        if ($scopeUnidade !== null) {
            return $scopeUnidade === $unidadeId;
        }

        if ($scopeRegiao !== null) {
            if ($regiaoId !== null) {
                return $scopeRegiao === $regiaoId;
            }

            if ($unidadeId === null) {
                return false;
            }

            return $this->unidades->isInRegiao($unidadeId, $scopeRegiao);
        }

        if ($scopeRede !== null) {
            return $scopeRede === $redeId;
        }

        return false;
    }
}

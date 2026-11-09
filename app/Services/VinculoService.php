<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ForbiddenException;
use App\Repositories\PerfilRepository;
use App\Repositories\RedeRepository;
use App\Repositories\RegiaoRepository;
use App\Repositories\UnidadeRepository;
use App\Repositories\VinculoRepository;
use RuntimeException;

final class VinculoService
{
    public function __construct(
        private readonly VinculoRepository $vinculos,
        private readonly UnidadeRepository $unidades,
        private readonly RegiaoRepository $regioes,
        private readonly RedeRepository $redes,
        private readonly PerfilRepository $perfis,
        private readonly AuthorizationService $authorization
    ) {
    }

    public function assignProfileAs(
        int $actorId,
        int $usuarioId,
        int $perfilId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): int {
        [$redeId, $unidadeId, $regiaoId] = $this->normalizeScope($redeId, $unidadeId, $regiaoId);

        if (!$this->authorization->canGrantProfile($actorId, $perfilId, $redeId, $unidadeId, $regiaoId)) {
            throw new ForbiddenException('Não é permitido atribuir um perfil acima do seu nível ou com permissões que você não possui.');
        }

        return $this->assignProfile($usuarioId, $perfilId, $redeId, $unidadeId, $regiaoId);
    }

    public function revokeAs(
        int $actorId,
        int $assignmentId,
        ?int $currentRedeId,
        ?int $currentUnidadeId,
        ?int $currentRegiaoId = null
    ): void {
        $assignment = $this->vinculos->findAssignmentById($assignmentId);

        if ($assignment === null) {
            throw new RuntimeException('Vínculo inexistente.');
        }

        if (!$this->assignmentVisible($assignment, $currentRedeId, $currentUnidadeId, $currentRegiaoId)) {
            throw new ForbiddenException('Vínculo fora do seu escopo de atuação.');
        }

        $profile = $this->perfis->findById((int) $assignment['perfil_id']);

        if ($profile === null) {
            throw new RuntimeException('Perfil do vínculo não encontrado.');
        }

        $actorLevel = $this->authorization->highestLevelForContext(
            $actorId,
            $currentRedeId,
            $currentUnidadeId,
            $currentRegiaoId
        );

        if ($actorLevel < 100 && (int) $profile['nivel'] >= $actorLevel) {
            throw new ForbiddenException('Não é permitido revogar um perfil de nível igual ou superior ao seu.');
        }

        $this->vinculos->revokeAssignment($assignmentId);
    }

    public function assignProfile(
        int $usuarioId,
        int $perfilId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): int {
        [$redeId, $unidadeId, $regiaoId] = $this->normalizeScope($redeId, $unidadeId, $regiaoId);

        if ($redeId !== null && !$this->vinculos->hasRedeMembership($usuarioId, $redeId)) {
            throw new RuntimeException('Usuário não possui vínculo ativo com a rede.');
        }

        if ($unidadeId !== null && !$this->vinculos->hasUnidadeMembership($usuarioId, $unidadeId)) {
            throw new RuntimeException('Usuário não possui vínculo ativo com a unidade.');
        }

        return $this->vinculos->assignProfile($usuarioId, $perfilId, $redeId, $unidadeId, $regiaoId);
    }

    private function assignmentVisible(array $assignment, ?int $redeId, ?int $unidadeId, ?int $regiaoId = null): bool
    {
        if ($redeId === null && $unidadeId === null && $regiaoId === null) {
            return true;
        }

        if ($regiaoId !== null) {
            if ($assignment['regiao_id'] !== null && (int) $assignment['regiao_id'] === $regiaoId) {
                return true;
            }

            if (
                $assignment['unidade_id'] !== null
                && $this->unidades->isInRegiao((int) $assignment['unidade_id'], $regiaoId)
            ) {
                return true;
            }

            return false;
        }

        if ($unidadeId !== null) {
            return $assignment['unidade_id'] !== null && (int) $assignment['unidade_id'] === $unidadeId;
        }

        return $assignment['rede_id'] !== null && (int) $assignment['rede_id'] === $redeId;
    }

    private function normalizeScope(?int $redeId, ?int $unidadeId, ?int $regiaoId): array
    {
        if ($unidadeId !== null && $regiaoId !== null) {
            throw new RuntimeException('O vínculo não pode ter unidade e região simultaneamente.');
        }

        if ($unidadeId !== null) {
            $unidade = $this->unidades->findById($unidadeId);

            if ($unidade === null) {
                throw new RuntimeException('Unidade inexistente.');
            }

            if ($redeId !== null && (int) $unidade['rede_id'] !== $redeId) {
                throw new RuntimeException('A unidade não pertence à rede informada.');
            }

            return [(int) $unidade['rede_id'], $unidadeId, null];
        }

        if ($regiaoId !== null) {
            $regiao = $this->regioes->findById($regiaoId);

            if ($regiao === null) {
                throw new RuntimeException('Região inexistente.');
            }

            if ($redeId !== null && (int) $regiao['rede_id'] !== $redeId) {
                throw new RuntimeException('A região não pertence à rede informada.');
            }

            return [(int) $regiao['rede_id'], null, $regiaoId];
        }

        if ($redeId !== null && $this->redes->findById($redeId) === null) {
            throw new RuntimeException('Rede inexistente.');
        }

        return [$redeId, null, null];
    }
}

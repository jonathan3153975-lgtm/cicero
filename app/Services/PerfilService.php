<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;
use App\Repositories\PerfilRepository;
use App\Repositories\PermissaoRepository;
use App\Validators\Validator;

final class PerfilService
{
    private const ESCOPOS = ['PLATAFORMA', 'REDE', 'REGIAO', 'UNIDADE'];
    private const STATUSES = ['ATIVO', 'INATIVO'];

    public function __construct(
        private readonly PerfilRepository $perfis,
        private readonly PermissaoRepository $permissoes,
        private readonly AuthorizationService $authorization
    ) {
    }

    public function create(
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        array $data,
        array $permissionSlugs,
        ?int $regiaoId = null
    ): int {
        $data = $this->normalize($data);
        $this->validate($data);

        $actorLevel = $this->actorLevel($actorId, $redeId, $unidadeId, $regiaoId);

        if ($actorLevel < 100 && $redeId === null) {
            throw new ForbiddenException('Perfis personalizados exigem uma rede de contexto.');
        }

        $this->assertEscopoAllowed($actorLevel, $data['escopo']);

        if ($this->perfis->findBySlug($data['slug']) !== null) {
            throw new ValidationException(['slug' => ['Já existe um perfil com este identificador.']]);
        }

        $permissionIds = $this->authorizePermissions(
            $actorId,
            $redeId,
            $unidadeId,
            $regiaoId,
            $data['nivel'],
            $permissionSlugs
        );

        $id = $this->perfis->create($redeId, $data);
        $this->perfis->syncPermissions($id, $permissionIds);

        return $id;
    }

    public function update(
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        int $id,
        array $data,
        array $permissionSlugs,
        ?int $regiaoId = null
    ): void {
        $this->assertCanManage($actorId, $id, $redeId, $unidadeId, $regiaoId);

        $data = $this->normalize($data);
        $this->validate($data);

        $actorLevel = $this->actorLevel($actorId, $redeId, $unidadeId, $regiaoId);
        $this->assertEscopoAllowed($actorLevel, $data['escopo']);

        $duplicate = $this->perfis->findBySlug($data['slug']);

        if ($duplicate !== null && (int) $duplicate['id'] !== $id) {
            throw new ValidationException(['slug' => ['Já existe um perfil com este identificador.']]);
        }

        $permissionIds = $this->authorizePermissions(
            $actorId,
            $redeId,
            $unidadeId,
            $regiaoId,
            $data['nivel'],
            $permissionSlugs
        );

        $this->perfis->update($id, $data);
        $this->perfis->syncPermissions($id, $permissionIds);
    }

    public function actorLevel(
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId = null
    ): int {
        return $this->authorization->highestLevelForContext($actorId, $redeId, $unidadeId, $regiaoId);
    }

    public function maxLevelForActor(
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId = null
    ): int {
        $actorLevel = $this->actorLevel($actorId, $redeId, $unidadeId, $regiaoId);

        return $actorLevel >= 100 ? 100 : max(1, $actorLevel - 1);
    }

    public function allowedEscopos(
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId = null
    ): array {
        $actorLevel = $this->actorLevel($actorId, $redeId, $unidadeId, $regiaoId);

        if ($actorLevel >= 100) {
            return self::ESCOPOS;
        }

        if ($actorLevel >= 80) {
            return ['REGIAO', 'UNIDADE'];
        }

        if ($actorLevel >= 60) {
            return ['UNIDADE'];
        }

        return [];
    }

    public function visibleProfiles(
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId = null
    ): array {
        $actorLevel = $this->actorLevel($actorId, $redeId, $unidadeId, $regiaoId);
        $profiles = $this->perfis->allForRede($redeId, $actorLevel >= 100);
        $visible = [];

        foreach ($profiles as $profile) {
            if ((int) $profile['nivel'] > $actorLevel) {
                continue;
            }

            $profile['can_edit'] = $this->canEditProfile($actorLevel, $profile, $redeId);
            $visible[] = $profile;
        }

        return $visible;
    }

    public function manageableProfiles(
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId = null
    ): array {
        return array_values(array_filter(
            $this->visibleProfiles($actorId, $redeId, $unidadeId, $regiaoId),
            static fn (array $profile): bool => (bool) $profile['can_edit']
        ));
    }

    public function grantableProfiles(
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId = null
    ): array {
        return array_values(array_filter(
            $this->visibleProfiles($actorId, $redeId, $unidadeId, $regiaoId),
            fn (array $profile): bool => $this->authorization->canGrantProfile(
                $actorId,
                (int) $profile['id'],
                $redeId,
                $unidadeId,
                $regiaoId
            )
        ));
    }

    public function assertCanManage(
        int $actorId,
        int $perfilId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId = null
    ): array {
        $profile = $this->perfis->findById($perfilId);

        if ($profile === null) {
            throw new ValidationException(['perfil' => ['Perfil inexistente.']]);
        }

        $actorLevel = $this->actorLevel($actorId, $redeId, $unidadeId, $regiaoId);

        if ($actorLevel < 100) {
            if ((int) $profile['nivel'] > $actorLevel) {
                throw new ForbiddenException('Não é permitido gerenciar um perfil de nível superior ao seu.');
            }

            if ((int) $profile['sistema'] === 1) {
                throw new ForbiddenException('Perfis de sistema só podem ser editados pelo Superadmin.');
            }

            if ($profile['rede_id'] === null) {
                throw new ForbiddenException('Perfis globais só podem ser editados pelo Superadmin.');
            }

            if ((int) $profile['rede_id'] !== $redeId) {
                throw new ForbiddenException('Perfil fora da sua rede.');
            }

            if ((int) $profile['nivel'] >= $actorLevel) {
                throw new ForbiddenException('Não é permitido editar um perfil de nível igual ou superior ao seu.');
            }
        }

        return $profile;
    }

    private function canEditProfile(int $actorLevel, array $profile, ?int $redeId): bool
    {
        if ($actorLevel >= 100) {
            return true;
        }

        if ((int) $profile['sistema'] === 1 || $profile['rede_id'] === null) {
            return false;
        }

        return (int) $profile['rede_id'] === $redeId && (int) $profile['nivel'] < $actorLevel;
    }

    private function authorizePermissions(
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        int $level,
        array $permissionSlugs
    ): array {
        $actorLevel = $this->actorLevel($actorId, $redeId, $unidadeId, $regiaoId);

        if ($actorLevel < 100 && $level >= $actorLevel) {
            throw new ForbiddenException('Não é permitido criar ou editar perfil de nível igual ou superior ao seu.');
        }

        $map = $this->permissoes->idsBySlugs($permissionSlugs);

        foreach ($permissionSlugs as $slug) {
            if (!isset($map[$slug])) {
                throw new ValidationException(['permissoes' => ['Permissão inexistente: ' . $slug]]);
            }
        }

        $actorPermissions = $this->authorization->permissionsForContext($actorId, $redeId, $unidadeId, $regiaoId);

        foreach ($permissionSlugs as $slug) {
            if (!in_array($slug, $actorPermissions, true)) {
                throw new ForbiddenException('Não é permitido conceder permissões que você não possui.');
            }
        }

        return array_values($map);
    }

    private function assertEscopoAllowed(int $actorLevel, string $escopo): void
    {
        if ($actorLevel >= 100) {
            return;
        }

        $allowed = $actorLevel >= 80 ? ['REGIAO', 'UNIDADE'] : ['UNIDADE'];

        if (!in_array($escopo, $allowed, true)) {
            throw new ForbiddenException('Escopo de perfil não permitido para o seu nível.');
        }
    }

    private function normalize(array $data): array
    {
        return [
            'slug' => strtoupper(trim((string) ($data['slug'] ?? ''))),
            'nome' => trim((string) ($data['nome'] ?? '')),
            'nivel' => (int) ($data['nivel'] ?? 0),
            'escopo' => strtoupper(trim((string) ($data['escopo'] ?? 'UNIDADE'))),
            'status' => strtoupper(trim((string) ($data['status'] ?? 'ATIVO'))),
        ];
    }

    private function validate(array $data): void
    {
        $validator = (new Validator($data))
            ->required('slug')
            ->maxLength('slug', 80)
            ->required('nome')
            ->maxLength('nome', 120)
            ->in('escopo', self::ESCOPOS)
            ->in('status', self::STATUSES);

        $errors = $validator->errors();

        if (preg_match('/^[A-Z0-9_]{2,80}$/', $data['slug']) !== 1) {
            $errors['slug'][] = 'Use apenas letras maiúsculas, números e sublinhado.';
        }

        if ($data['nivel'] < 1 || $data['nivel'] > 100) {
            $errors['nivel'][] = 'O nível deve estar entre 1 e 100.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }
    }
}

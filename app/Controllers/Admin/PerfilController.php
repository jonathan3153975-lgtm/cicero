<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;
use App\Repositories\PermissaoRepository;
use App\Services\AuditService;
use App\Services\PerfilService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

final class PerfilController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly PermissaoRepository $permissoes,
        private readonly PerfilService $service
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        $perfis = $this->service->visibleProfiles(
            $this->actorId(),
            $this->contextRedeId(),
            $this->contextUnidadeId(),
            $this->contextRegiaoId()
        );

        foreach ($perfis as &$perfil) {
            $perfil['permission_count'] = count($this->permissoes->forProfile((int) $perfil['id']));
        }
        unset($perfil);

        return $this->render('admin.perfis.index', [
            'title' => 'Perfis',
            'perfis' => $perfis,
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->render('admin.perfis.form', array_merge($this->formState(), [
            'title' => 'Novo perfil',
            'item' => null,
            'selected' => [],
            'permissionGroups' => $this->permissionGroups(),
            'maxLevel' => $this->service->maxLevelForActor(
                $this->actorId(),
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            ),
            'allowedEscopos' => $this->service->allowedEscopos(
                $this->actorId(),
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            ),
        ]));
    }

    public function store(Request $request): Response
    {
        $data = $request->all();
        $slugs = $this->permissionSlugs($request);

        try {
            $id = $this->service->create(
                $this->actorId(),
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $data,
                $slugs,
                $this->contextRegiaoId()
            );
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/perfis/novo', $exception->getErrors(), $data);
        } catch (ForbiddenException $exception) {
            $this->flashError($exception->getMessage());

            return $this->redirect('/admin/perfis/novo');
        }

        $this->auditExplicit($request, 'perfil.criar', 'perfil', $id, null, [
            'dados' => $data,
            'permissoes' => $slugs,
        ], null, null);
        $this->flashSuccess('Perfil criado com sucesso.');

        return $this->redirect('/admin/perfis');
    }

    public function edit(Request $request): Response
    {
        $item = $this->service->assertCanManage(
            $this->actorId(),
            (int) $request->routeParam('id'),
            $this->contextRedeId(),
            $this->contextUnidadeId(),
            $this->contextRegiaoId()
        );

        return $this->render('admin.perfis.form', array_merge($this->formState(), [
            'title' => 'Editar perfil',
            'item' => $item,
            'selected' => $this->permissoes->forProfile((int) $item['id']),
            'permissionGroups' => $this->permissionGroups(),
            'maxLevel' => $this->service->maxLevelForActor(
                $this->actorId(),
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            ),
            'allowedEscopos' => $this->service->allowedEscopos(
                $this->actorId(),
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            ),
        ]));
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->routeParam('id');
        $before = $this->service->assertCanManage(
            $this->actorId(),
            $id,
            $this->contextRedeId(),
            $this->contextUnidadeId(),
            $this->contextRegiaoId()
        );
        $beforePermissions = $this->permissoes->forProfile($id);
        $data = $request->all();
        $slugs = $this->permissionSlugs($request);

        try {
            $this->service->update(
                $this->actorId(),
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $id,
                $data,
                $slugs,
                $this->contextRegiaoId()
            );
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/perfis/' . $id . '/editar', $exception->getErrors(), $data);
        } catch (ForbiddenException $exception) {
            $this->flashError($exception->getMessage());

            return $this->redirect('/admin/perfis/' . $id . '/editar');
        }

        $this->auditExplicit($request, 'perfil.editar', 'perfil', $id, [
            'dados' => $before,
            'permissoes' => $beforePermissions,
        ], [
            'dados' => $data,
            'permissoes' => $slugs,
        ], null, null);
        $this->flashSuccess('Perfil atualizado com sucesso.');

        return $this->redirect('/admin/perfis');
    }

    private function permissionSlugs(Request $request): array
    {
        $slugs = $request->input('permissoes', []);

        if (!is_array($slugs)) {
            return [];
        }

        return array_values(array_filter($slugs, static fn ($slug): bool => is_string($slug) && $slug !== ''));
    }

    private function permissionGroups(): array
    {
        $groups = [];

        foreach ($this->permissoes->all() as $permission) {
            $groups[(string) $permission['modulo']][] = $permission;
        }

        return $groups;
    }
}

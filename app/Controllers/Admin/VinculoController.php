<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Exceptions\ForbiddenException;
use App\Repositories\RedeRepository;
use App\Repositories\RegiaoRepository;
use App\Repositories\UnidadeRepository;
use App\Repositories\UsuarioRepository;
use App\Repositories\VinculoRepository;
use App\Services\AuditService;
use App\Services\PerfilService;
use App\Services\VinculoService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;
use RuntimeException;

final class VinculoController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly VinculoRepository $vinculos,
        private readonly UsuarioRepository $usuarios,
        private readonly RedeRepository $redes,
        private readonly RegiaoRepository $regioes,
        private readonly UnidadeRepository $unidades,
        private readonly PerfilService $perfilService,
        private readonly VinculoService $service
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        return $this->render('admin.vinculos.index', [
            'title' => 'Vínculos',
            'vinculos' => $this->vinculos->allAssignmentsForScope(
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            ),
        ]);
    }

    public function create(Request $request): Response
    {
        $redeId = $this->contextRedeId();
        $unidadeId = $this->contextUnidadeId();
        $regiaoId = $this->contextRegiaoId();

        return $this->render('admin.vinculos.form', array_merge($this->formState(), [
            'title' => 'Novo vínculo',
            'usuarios' => $this->usuarios->forScope($redeId, $unidadeId, $regiaoId),
            'perfis' => $this->perfilService->grantableProfiles($this->actorId(), $redeId, $unidadeId, $regiaoId),
            'redes' => $this->isPlatformContext() ? $this->redes->all() : array_values(array_filter([
                $this->redes->findById((int) $redeId),
            ])),
            'regioes' => $unidadeId !== null
                ? []
                : ($regiaoId !== null
                    ? array_values(array_filter([$this->regioes->findById($regiaoId)]))
                    : ($redeId !== null ? $this->regioes->forRede($redeId) : $this->regioes->all())),
            'unidades' => $unidadeId !== null
                ? array_values(array_filter([$this->unidades->findById($unidadeId)]))
                : ($regiaoId !== null
                    ? $this->unidades->forRegiao($regiaoId)
                    : ($redeId !== null ? $this->unidades->forRede($redeId) : $this->unidades->all())),
            'isPlatform' => $this->isPlatformContext(),
            'currentRedeId' => $redeId,
            'currentUnidadeId' => $unidadeId,
            'currentRegiaoId' => $regiaoId,
        ]));
    }

    public function store(Request $request): Response
    {
        $data = $request->all();
        $usuarioId = (int) ($data['usuario_id'] ?? 0);
        $perfilId = (int) ($data['perfil_id'] ?? 0);

        if ($usuarioId <= 0 || $perfilId <= 0) {
            return $this->backWithErrors('/admin/vinculos/novo', [
                'usuario_id' => $usuarioId <= 0 ? ['Selecione o usuário.'] : [],
                'perfil_id' => $perfilId <= 0 ? ['Selecione o perfil.'] : [],
            ], $data);
        }

        [$redeId, $unidadeId, $regiaoId] = $this->resolveScope($request);

        try {
            $id = $this->service->assignProfileAs(
                $this->actorId(),
                $usuarioId,
                $perfilId,
                $redeId,
                $unidadeId,
                $regiaoId
            );
        } catch (ForbiddenException $exception) {
            $this->flashError($exception->getMessage());

            return $this->redirect('/admin/vinculos/novo');
        } catch (RuntimeException $exception) {
            return $this->backWithErrors('/admin/vinculos/novo', [
                'vinculo' => [$exception->getMessage()],
            ], $data);
        }

        $this->audit($request, 'vinculo.criar', 'usuario_perfil', $id, null, [
            'usuario_id' => $usuarioId,
            'perfil_id' => $perfilId,
            'rede_id' => $redeId,
            'unidade_id' => $unidadeId,
            'regiao_id' => $regiaoId,
        ], $redeId, $unidadeId);
        $this->flashSuccess('Vínculo criado com sucesso.');

        return $this->redirect('/admin/vinculos');
    }

    public function revoke(Request $request): Response
    {
        $id = (int) $request->routeParam('id');
        $before = $this->vinculos->findAssignmentById($id);

        try {
            $this->service->revokeAs(
                $this->actorId(),
                $id,
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            );
        } catch (ForbiddenException $exception) {
            $this->flashError($exception->getMessage());

            return $this->redirect('/admin/vinculos');
        } catch (RuntimeException $exception) {
            $this->flashError($exception->getMessage());

            return $this->redirect('/admin/vinculos');
        }

        $this->audit($request, 'vinculo.revogar', 'usuario_perfil', $id, $before, ['status' => 'INATIVO']);
        $this->flashSuccess('Vínculo revogado com sucesso.');

        return $this->redirect('/admin/vinculos');
    }

    private function resolveScope(Request $request): array
    {
        if ($this->contextUnidadeId() !== null) {
            return [$this->contextRedeId(), $this->contextUnidadeId(), null];
        }

        $redeId = $this->contextRedeId() ?? $this->nullableInt($request->input('rede_id'));
        $unidadeId = $this->nullableInt($request->input('unidade_id'));
        $regiaoId = $this->contextRegiaoId() ?? $this->nullableInt($request->input('regiao_id'));

        if ($unidadeId !== null) {
            return [$redeId, $unidadeId, null];
        }

        if ($regiaoId !== null) {
            return [$redeId, null, $regiaoId];
        }

        return [$redeId, null, null];
    }

    private function nullableInt(mixed $value): ?int
    {
        $value = (int) $value;

        return $value > 0 ? $value : null;
    }
}

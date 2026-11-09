<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\ModalidadeRepository;
use App\Repositories\PlanoRepository;
use App\Repositories\RedeRepository;
use App\Services\AuditService;
use App\Services\PlanoService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

final class PlanoController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly PlanoRepository $planos,
        private readonly PlanoService $service,
        private readonly ModalidadeRepository $modalidades,
        private readonly RedeRepository $redes
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        return $this->render('admin.planos.index', [
            'title' => 'Planos',
            'planos' => $this->service->allForScope($this->contextRedeId(), $this->isPlatformContext()),
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->render('admin.planos.form', array_merge($this->formState(), [
            'title' => 'Novo plano',
            'item' => null,
            'selectedModalities' => [],
            'modalidades' => $this->modalidadeOptions(null),
            'redes' => $this->redeOptions(),
            'isPlatform' => $this->isPlatformContext(),
            'currentRedeId' => $this->contextRedeId(),
        ]));
    }

    public function store(Request $request): Response
    {
        $data = $request->all();
        $redeId = $this->targetRedeId($request->input('rede_id'));
        $modalidadeIds = $this->modalidadeIds($request);

        try {
            $id = $this->service->create($this->actorId(), $redeId, $data, $modalidadeIds);
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/planos/novo', $exception->getErrors(), $data);
        }

        $this->audit($request, 'plano.criar', 'plano', $id, null, $data, $redeId);
        $this->flashSuccess('Plano criado com sucesso.');

        return $this->redirect('/admin/planos');
    }

    public function edit(Request $request): Response
    {
        $item = $this->findScoped((int) $request->routeParam('id'));

        return $this->render('admin.planos.form', array_merge($this->formState(), [
            'title' => 'Editar plano',
            'item' => $item,
            'selectedModalities' => $this->planos->modalidadeIdsForPlano((int) $item['id']),
            'modalidades' => $this->modalidadeOptions($item['rede_id'] !== null ? (int) $item['rede_id'] : null),
            'redes' => $this->redeOptions(),
            'isPlatform' => $this->isPlatformContext(),
            'currentRedeId' => $this->contextRedeId(),
        ]));
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->routeParam('id');
        $before = $this->findScoped($id);
        $data = $request->all();
        $modalidadeIds = $this->modalidadeIds($request);

        try {
            $this->service->update(
                $this->actorId(),
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId(),
                $id,
                $data,
                $modalidadeIds
            );
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/planos/' . $id . '/editar', $exception->getErrors(), $data);
        }

        $this->audit($request, 'plano.editar', 'plano', $id, $before, $data, $before['rede_id'] !== null ? (int) $before['rede_id'] : null);
        $this->flashSuccess('Plano atualizado com sucesso.');

        return $this->redirect('/admin/planos');
    }

    private function findScoped(int $id): array
    {
        $plano = $this->planos->findById($id);

        if ($plano === null) {
            throw new NotFoundException('Plano não encontrado.');
        }

        $this->service->assertCanAccess(
            $id,
            $this->contextRedeId(),
            $this->contextUnidadeId(),
            $this->contextRegiaoId()
        );

        return $plano;
    }

    private function targetRedeId(mixed $input): ?int
    {
        if (!$this->isPlatformContext()) {
            return $this->contextRedeId();
        }

        $value = (int) $input;

        return $value > 0 ? $value : null;
    }

    private function redeOptions(): array
    {
        return $this->isPlatformContext() ? $this->redes->all() : array_values(array_filter([
            $this->redes->findById((int) $this->contextRedeId()),
        ]));
    }

    private function modalidadeOptions(?int $redeId): array
    {
        return $this->modalidades->allActiveForRede($redeId, $this->isPlatformContext() && $redeId === null);
    }

    private function modalidadeIds(Request $request): array
    {
        $ids = $request->input('modalidades', []);

        if (!is_array($ids)) {
            return [];
        }

        return array_values(array_filter(array_map('intval', $ids)));
    }
}

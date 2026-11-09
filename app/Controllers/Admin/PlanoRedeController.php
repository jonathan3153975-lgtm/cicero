<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\PlanoRedeRepository;
use App\Services\AuditService;
use App\Services\PlanoRedeService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

final class PlanoRedeController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly PlanoRedeRepository $planos,
        private readonly PlanoRedeService $service
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        return $this->render('admin.planos-rede.index', [
            'title' => 'Planos de rede',
            'planos' => $this->planos->all(),
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->render('admin.planos-rede.form', array_merge($this->formState(), [
            'title' => 'Novo plano de rede',
            'item' => null,
        ]));
    }

    public function store(Request $request): Response
    {
        $data = $request->all();

        try {
            $id = $this->service->create($this->actorId(), $data);
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/planos-rede/novo', $exception->getErrors(), $data);
        }

        $this->audit($request, 'plano_rede.criar', 'plano_rede', $id, null, $data);
        $this->flashSuccess('Plano de rede criado com sucesso.');

        return $this->redirect('/admin/planos-rede');
    }

    public function edit(Request $request): Response
    {
        $item = $this->findItem((int) $request->routeParam('id'));

        return $this->render('admin.planos-rede.form', array_merge($this->formState(), [
            'title' => 'Editar plano de rede',
            'item' => $item,
        ]));
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->routeParam('id');
        $before = $this->findItem($id);
        $data = $request->all();

        try {
            $this->service->update($this->actorId(), $id, $data);
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/planos-rede/' . $id . '/editar', $exception->getErrors(), $data);
        }

        $this->audit($request, 'plano_rede.editar', 'plano_rede', $id, $before, $data);
        $this->flashSuccess('Plano de rede atualizado com sucesso.');

        return $this->redirect('/admin/planos-rede');
    }

    private function findItem(int $id): array
    {
        $item = $this->planos->findById($id);

        if ($item === null) {
            throw new NotFoundException('Plano de rede não encontrado.');
        }

        return $item;
    }
}

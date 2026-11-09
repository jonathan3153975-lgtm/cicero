<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Exceptions\ValidationException;
use App\Repositories\ContratoRepository;
use App\Services\AuditService;
use App\Services\ContratoService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

final class ContratoController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly ContratoRepository $contratos,
        private readonly ContratoService $service
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('busca', ''));

        return $this->render('admin.contratos.index', [
            'title' => 'Contratos',
            'contratos' => $this->service->forScope(
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId(),
                $search
            ),
            'search' => $search,
        ]);
    }

    public function edit(Request $request): Response
    {
        $item = $this->findScoped((int) $request->routeParam('id'));

        return $this->render('admin.contratos.form', array_merge($this->formState(), [
            'title' => 'Editar contrato',
            'item' => $item,
        ]));
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->routeParam('id');
        $before = $this->findScoped($id);
        $data = $request->all();

        try {
            $this->service->update(
                $this->actorId(),
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId(),
                $id,
                $data
            );
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/contratos/' . $id . '/editar', $exception->getErrors(), $data);
        }

        $this->audit(
            $request,
            'contrato.editar',
            'contrato',
            $id,
            $before,
            $data,
            (int) $before['rede_id'],
            (int) $before['unidade_id']
        );
        $this->flashSuccess('Contrato atualizado com sucesso.');

        return $this->redirect('/admin/contratos');
    }

    private function findScoped(int $id): array
    {
        return $this->service->assertCanAccess(
            $id,
            $this->contextRedeId(),
            $this->contextUnidadeId(),
            $this->contextRegiaoId()
        );
    }
}

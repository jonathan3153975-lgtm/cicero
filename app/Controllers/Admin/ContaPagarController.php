<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Exceptions\ValidationException;
use App\Repositories\CentroCustoRepository;
use App\Repositories\ContaBancariaRepository;
use App\Repositories\UnidadeRepository;
use App\Services\AuditService;
use App\Services\ContaPagarService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

final class ContaPagarController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly ContaPagarService $service,
        private readonly ContaBancariaRepository $contas,
        private readonly CentroCustoRepository $centros,
        private readonly UnidadeRepository $unidades
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('busca', ''));

        return $this->render('admin.contas-pagar.index', [
            'title' => 'Contas a pagar',
            'contasPagar' => $this->service->forScope(
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId(),
                $search
            ),
            'search' => $search,
            'contas' => $this->contas->forScope(
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            ),
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->render('admin.contas-pagar.form', array_merge($this->formState(), [
            'title' => 'Nova conta a pagar',
            'unidades' => $this->unidades->forScope(
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            ),
            'centros' => $this->centros->forScope(
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            ),
        ]));
    }

    public function store(Request $request): Response
    {
        $data = $request->all();

        try {
            $id = $this->service->create(
                $this->actorId(),
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId(),
                $data
            );
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/contas-pagar/nova', $exception->getErrors(), $data);
        }

        $this->audit($request, 'conta_pagar.criar', 'conta_pagar', $id, null, $data);
        $this->flashSuccess('Conta a pagar criada com sucesso.');

        return $this->redirect('/admin/contas-pagar');
    }

    public function pay(Request $request): Response
    {
        $id = (int) $request->routeParam('id');

        try {
            $this->service->pay(
                $this->actorId(),
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId(),
                $id,
                (string) $request->input('data_pagamento', date('Y-m-d')),
                (int) $request->input('conta_bancaria_id', 0)
            );
        } catch (ValidationException $exception) {
            $this->flashError($exception->getErrors()[array_key_first($exception->getErrors())][0] ?? 'Não foi possível pagar.');
            return $this->redirect('/admin/contas-pagar');
        }

        $this->audit($request, 'conta_pagar.pagar', 'conta_pagar', $id, null, $request->all());
        $this->flashSuccess('Conta marcada como paga.');

        return $this->redirect('/admin/contas-pagar');
    }

    public function cancel(Request $request): Response
    {
        $id = (int) $request->routeParam('id');

        try {
            $this->service->cancel(
                $this->actorId(),
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId(),
                $id
            );
        } catch (ValidationException $exception) {
            $this->flashError($exception->getErrors()[array_key_first($exception->getErrors())][0] ?? 'Não foi possível cancelar.');
            return $this->redirect('/admin/contas-pagar');
        }

        $this->audit($request, 'conta_pagar.cancelar', 'conta_pagar', $id, null, null);
        $this->flashSuccess('Conta cancelada.');

        return $this->redirect('/admin/contas-pagar');
    }
}

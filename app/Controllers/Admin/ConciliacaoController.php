<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Exceptions\ValidationException;
use App\Repositories\ContaBancariaRepository;
use App\Services\AuditService;
use App\Services\ConciliacaoService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

final class ConciliacaoController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly ConciliacaoService $service,
        private readonly ContaBancariaRepository $contas
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        return $this->render('admin.conciliacoes.index', [
            'title' => 'Conciliação bancária',
            'conciliacoes' => $this->service->forScope(
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            ),
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->render('admin.conciliacoes.form', array_merge($this->formState(), [
            'title' => 'Nova conciliação',
            'contas' => $this->contas->forScope(
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
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId(),
                $data
            );
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/conciliacoes/nova', $exception->getErrors(), $data);
        }

        $this->audit($request, 'conciliacao.criar', 'conciliacao', $id, null, $data);
        $this->flashSuccess('Conciliação criada. Importe o extrato para iniciar.');

        return $this->redirect('/admin/conciliacoes/' . $id);
    }

    public function show(Request $request): Response
    {
        $conciliacao = $this->service->assertCanAccess(
            (int) $request->routeParam('id'),
            $this->contextRedeId(),
            $this->contextUnidadeId(),
            $this->contextRegiaoId()
        );

        return $this->render('admin.conciliacoes.show', [
            'title' => 'Conciliação ' . $conciliacao['competencia'],
            'conciliacao' => $conciliacao,
            'itens' => $this->service->items((int) $conciliacao['id']),
        ]);
    }

    public function import(Request $request): Response
    {
        $id = (int) $request->routeParam('id');

        try {
            $conciliacao = $this->service->assertCanAccess(
                $id,
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            );
            $importados = $this->service->importCsv($id, (string) $request->input('csv', ''));
        } catch (ValidationException $exception) {
            $this->flashError($exception->getErrors()[array_key_first($exception->getErrors())][0] ?? 'Falha ao importar.');
            return $this->redirect('/admin/conciliacoes/' . $id);
        }

        $this->audit($request, 'conciliacao.importar', 'conciliacao', $id, null, ['linhas' => $importados]);
        $this->flashSuccess($importados . ' linha(s) importada(s).');

        return $this->redirect('/admin/conciliacoes/' . $conciliacao['id']);
    }

    public function close(Request $request): Response
    {
        $id = (int) $request->routeParam('id');

        try {
            $this->service->close($id, $this->actorId());
        } catch (ValidationException $exception) {
            $this->flashError($exception->getErrors()[array_key_first($exception->getErrors())][0] ?? 'Falha ao fechar.');
            return $this->redirect('/admin/conciliacoes/' . $id);
        }

        $this->audit($request, 'conciliacao.fechar', 'conciliacao', $id, null, null);
        $this->flashSuccess('Conciliação fechada.');

        return $this->redirect('/admin/conciliacoes/' . $id);
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Exceptions\ValidationException;
use App\Repositories\ContaBancariaRepository;
use App\Repositories\FormaPagamentoRepository;
use App\Services\AuditService;
use App\Services\CobrancaService;
use App\Services\PagamentoService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

final class PagamentoController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly PagamentoService $service,
        private readonly CobrancaService $cobrancas,
        private readonly ContaBancariaRepository $contas,
        private readonly FormaPagamentoRepository $formas
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('busca', ''));

        return $this->render('admin.pagamentos.index', [
            'title' => 'Pagamentos',
            'pagamentos' => $this->service->forScope(
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId(),
                $search
            ),
            'search' => $search,
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->render('admin.pagamentos.form', array_merge($this->formState(), [
            'title' => 'Registrar pagamento',
            'cobrancas' => $this->cobrancas->forScope(
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId(),
                '',
                true
            ),
            'contas' => $this->contas->forScope(
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            ),
            'formas' => $this->formas->allActiveForRede(
                $this->contextRedeId(),
                $this->contextRedeId() === null && $this->contextUnidadeId() === null
            ),
            'cobrancaSelecionada' => (int) $request->query('cobranca_id', 0),
        ]));
    }

    public function store(Request $request): Response
    {
        $data = $request->all();

        try {
            $id = $this->service->register(
                $this->actorId(),
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId(),
                $data
            );
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/pagamentos/novo', $exception->getErrors(), $data);
        }

        $this->audit($request, 'pagamento.criar', 'pagamento', $id, null, $data);
        $this->flashSuccess('Pagamento registrado com sucesso.');

        return $this->redirect('/admin/pagamentos');
    }

    public function reverse(Request $request): Response
    {
        $id = (int) $request->routeParam('id');
        $motivo = trim((string) $request->input('motivo', ''));

        try {
            $this->service->reverse(
                $this->actorId(),
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId(),
                $id,
                $motivo
            );
        } catch (ValidationException $exception) {
            $this->flashError($exception->getErrors()[array_key_first($exception->getErrors())][0] ?? 'Não foi possível estornar.');
            return $this->redirect('/admin/pagamentos');
        }

        $this->audit($request, 'pagamento.estornar', 'pagamento', $id, null, ['motivo' => $motivo]);
        $this->flashSuccess('Pagamento estornado com sucesso.');

        return $this->redirect('/admin/pagamentos');
    }
}

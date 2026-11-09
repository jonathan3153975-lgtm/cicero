<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Exceptions\ValidationException;
use App\Repositories\CobrancaRepository;
use App\Repositories\PagamentoRepository;
use App\Services\AlunoService;
use App\Services\AuditService;
use App\Services\CobrancaService;
use App\Services\MatriculaService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

final class CobrancaController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly CobrancaService $service,
        private readonly CobrancaRepository $cobrancas,
        private readonly PagamentoRepository $pagamentos,
        private readonly MatriculaService $matriculas,
        private readonly AlunoService $alunos
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        $this->service->refreshOverdueStatus();
        $search = trim((string) $request->query('busca', ''));
        $onlyOpen = (string) $request->query('abertas', '') === '1';

        return $this->render('admin.cobrancas.index', [
            'title' => 'Cobranças',
            'cobrancas' => $this->service->forScope(
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId(),
                $search,
                $onlyOpen
            ),
            'search' => $search,
            'onlyOpen' => $onlyOpen,
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->render('admin.cobrancas.form', array_merge($this->formState(), [
            'title' => 'Nova cobrança',
            'matriculas' => $this->matriculas->forScope(
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            ),
            'alunos' => $this->alunos->forScope(
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            ),
        ]));
    }

    public function store(Request $request): Response
    {
        $data = $request->all();
        $modo = (string) ($data['modo'] ?? 'manual');

        try {
            if ($modo === 'matricula') {
                $this->matriculas->assertCanAccess(
                    (int) ($data['matricula_id'] ?? 0),
                    $this->contextRedeId(),
                    $this->contextUnidadeId(),
                    $this->contextRegiaoId()
                );
                $id = $this->service->generateForMatricula(
                    (int) ($data['matricula_id'] ?? 0),
                    (string) ($data['competencia'] ?? ''),
                    (string) ($data['data_vencimento'] ?? '')
                );
            } else {
                $id = $this->service->createManual(
                    $this->actorId(),
                    $this->contextRedeId(),
                    $this->contextUnidadeId(),
                    $this->contextRegiaoId(),
                    $data
                );
            }
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/cobrancas/nova', $exception->getErrors(), $data);
        }

        $cobranca = $this->cobrancas->findById($id);
        $this->audit(
            $request,
            'cobranca.criar',
            'cobranca',
            $id,
            null,
            $data,
            (int) $cobranca['rede_id'],
            (int) $cobranca['unidade_id']
        );
        $this->flashSuccess('Cobrança criada com sucesso.');

        return $this->redirect('/admin/cobrancas');
    }

    public function show(Request $request): Response
    {
        $cobranca = $this->service->assertCanAccess(
            (int) $request->routeParam('id'),
            $this->contextRedeId(),
            $this->contextUnidadeId(),
            $this->contextRegiaoId()
        );

        return $this->render('admin.cobrancas.show', [
            'title' => 'Cobrança ' . $cobranca['numero'],
            'cobranca' => $cobranca,
            'itens' => $this->cobrancas->items((int) $cobranca['id']),
            'pago' => $this->pagamentos->sumAllocatedForCobranca((int) $cobranca['id']),
        ]);
    }
}

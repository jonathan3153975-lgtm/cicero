<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Exceptions\ValidationException;
use App\Repositories\ContaBancariaRepository;
use App\Repositories\UnidadeRepository;
use App\Services\AuditService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

final class ContaBancariaController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly ContaBancariaRepository $contas,
        private readonly UnidadeRepository $unidades
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        return $this->render('admin.contas-bancarias.index', array_merge($this->formState(), [
            'title' => 'Contas bancárias',
            'contas' => $this->contas->forScope(
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            ),
            'unidades' => $this->unidades->forScope(
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            ),
        ]));
    }

    public function store(Request $request): Response
    {
        $data = $request->all();
        $unidadeId = (int) ($data['unidade_id'] ?? 0);
        $unidade = $unidadeId > 0 ? $this->unidades->findById($unidadeId) : null;
        $banco = trim((string) ($data['banco'] ?? ''));
        $allowedUnits = array_map(
            static fn (array $unit): int => (int) $unit['id'],
            $this->unidades->forScope(
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            )
        );

        $errors = [];

        if ($unidade === null || !in_array((int) $unidade['id'], $allowedUnits, true)) {
            $errors['unidade_id'] = ['Selecione uma unidade válida.'];
        }

        if ($banco === '') {
            $errors['banco'] = ['Informe o nome do banco ou caixa.'];
        }

        if ($errors !== []) {
            return $this->backWithErrors('/admin/contas-bancarias', $errors, $data);
        }

        $saldoInicial = $this->parseCurrency((string) ($data['saldo_inicial'] ?? '0'));

        $id = $this->contas->create([
            'rede_id' => (int) $unidade['rede_id'],
            'unidade_id' => (int) $unidade['id'],
            'banco' => $banco,
            'agencia' => trim((string) ($data['agencia'] ?? '')),
            'conta' => trim((string) ($data['conta'] ?? '')),
            'tipo' => strtoupper(trim((string) ($data['tipo'] ?? 'CORRENTE'))),
            'saldo_inicial' => round($saldoInicial, 2),
            'status' => 'ATIVO',
        ]);

        $this->audit($request, 'conta_bancaria.criar', 'conta_bancaria', $id, null, $data);
        $this->flashSuccess('Conta bancária criada com sucesso.');

        return $this->redirect('/admin/contas-bancarias');
    }

    private function parseCurrency(string $value): float
    {
        // Remove espaços
        $value = trim($value);
        
        // Se já é um número válido (formato US), retorna direto
        if ($value === '' || is_numeric($value)) {
            return (float) $value;
        }
        
        // Formato brasileiro: 1.234,56 -> remove pontos de milhar, troca vírgula por ponto
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
        
        return (float) $value;
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;
use App\Repositories\CobrancaRepository;
use App\Repositories\ContaBancariaRepository;
use App\Repositories\EstornoRepository;
use App\Repositories\FormaPagamentoRepository;
use App\Repositories\MovimentoCaixaRepository;
use App\Repositories\PagamentoRepository;
use App\Repositories\UnidadeRepository;
use App\Support\Database;
use App\Support\PublicId;

final class PagamentoService
{
    public function __construct(
        private readonly PagamentoRepository $pagamentos,
        private readonly CobrancaRepository $cobrancas,
        private readonly CobrancaService $cobrancaService,
        private readonly ContaBancariaRepository $contas,
        private readonly FormaPagamentoRepository $formas,
        private readonly MovimentoCaixaRepository $movimentos,
        private readonly EstornoRepository $estornos,
        private readonly UnidadeRepository $unidades,
        private readonly Database $database
    ) {
    }

    public function register(
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        array $data
    ): int {
        $cobranca = $this->cobrancaService->assertCanAccess(
            (int) ($data['cobranca_id'] ?? 0),
            $redeId,
            $unidadeId,
            $regiaoId
        );

        if (!in_array($cobranca['status'], ['ABERTA', 'VENCIDA', 'PARCIAL'], true)) {
            throw new ValidationException(['cobranca_id' => ['Esta cobrança não está aberta para pagamento.']]);
        }

        $valor = round((float) ($data['valor'] ?? 0), 2);
        $saldo = round((float) $cobranca['valor_total'] - $this->pagamentos->sumAllocatedForCobranca((int) $cobranca['id']), 2);

        if ($valor <= 0) {
            throw new ValidationException(['valor' => ['Informe um valor maior que zero.']]);
        }

        if (round($valor - $saldo, 2) > 0) {
            throw new ValidationException(['valor' => ['O valor informado excede o saldo da cobrança.']]);
        }

        $conta = $this->contas->findById((int) ($data['conta_bancaria_id'] ?? 0));

        if ($conta === null || $conta['status'] !== 'ATIVO') {
            throw new ValidationException(['conta_bancaria_id' => ['Conta bancária inválida ou inativa.']]);
        }

        if ((int) $conta['unidade_id'] !== (int) $cobranca['unidade_id']) {
            throw new ValidationException(['conta_bancaria_id' => ['A conta não pertence à unidade da cobrança.']]);
        }

        $forma = $this->formas->findById((int) ($data['forma_pagamento_id'] ?? 0));

        if ($forma === null || $forma['status'] !== 'ATIVO') {
            throw new ValidationException(['forma_pagamento_id' => ['Forma de pagamento inválida ou inativa.']]);
        }

        if ($forma['rede_id'] !== null && (int) $forma['rede_id'] !== (int) $cobranca['rede_id']) {
            throw new ValidationException(['forma_pagamento_id' => ['Forma de pagamento fora da rede.']]);
        }

        $dataPagamento = $this->normalizeDate($data['data_pagamento'] ?? date('Y-m-d'), 'data_pagamento');
        $dataCompensacao = null;

        if (trim((string) ($data['data_compensacao'] ?? '')) !== '') {
            $dataCompensacao = $this->normalizeDate($data['data_compensacao'], 'data_compensacao');
        }

        return $this->database->transaction(function () use (
            $actorId,
            $cobranca,
            $valor,
            $conta,
            $forma,
            $dataPagamento,
            $dataCompensacao
        ): int {
            $pagamentoId = $this->pagamentos->create([
                'public_id' => PublicId::generate(),
                'rede_id' => (int) $cobranca['rede_id'],
                'unidade_id' => (int) $cobranca['unidade_id'],
                'aluno_id' => (int) $cobranca['aluno_id'],
                'cobranca_id' => (int) $cobranca['id'],
                'conta_bancaria_id' => (int) $conta['id'],
                'forma_pagamento_id' => (int) $forma['id'],
                'valor' => $valor,
                'data_pagamento' => $dataPagamento,
                'data_compensacao' => $dataCompensacao,
                'status' => 'CONFIRMADO',
                'transacao_id' => trim((string) ($data['transacao_id'] ?? '')) ?: null,
                'criado_por' => $actorId,
            ]);

            $this->pagamentos->addAllocation($pagamentoId, (int) $cobranca['id'], $valor);

            $this->movimentos->create([
                'rede_id' => (int) $cobranca['rede_id'],
                'unidade_id' => (int) $cobranca['unidade_id'],
                'conta_bancaria_id' => (int) $conta['id'],
                'tipo' => 'ENTRADA',
                'origem' => 'PAGAMENTO',
                'valor' => $valor,
                'data' => $dataPagamento,
                'descricao' => 'Pagamento da cobrança ' . (string) $cobranca['numero'],
                'referencia_id' => $pagamentoId,
                'referencia_tipo' => 'pagamento',
            ]);

            $this->syncCobrancaStatus((int) $cobranca['id']);

            return $pagamentoId;
        });
    }

    public function reverse(
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        int $pagamentoId,
        string $motivo
    ): void {
        $pagamento = $this->assertCanAccess($pagamentoId, $redeId, $unidadeId, $regiaoId);

        if ($pagamento['status'] !== 'CONFIRMADO') {
            throw new ValidationException(['pagamento' => ['Somente pagamentos confirmados podem ser estornados.']]);
        }

        $this->database->transaction(function () use ($actorId, $pagamento, $motivo): void {
            $this->pagamentos->markReversed((int) $pagamento['id']);
            $this->estornos->create(
                (int) $pagamento['id'],
                (float) $pagamento['valor'],
                trim($motivo) !== '' ? trim($motivo) : null,
                date('Y-m-d'),
                $actorId
            );

            $this->movimentos->create([
                'rede_id' => (int) $pagamento['rede_id'],
                'unidade_id' => (int) $pagamento['unidade_id'],
                'conta_bancaria_id' => (int) $pagamento['conta_bancaria_id'],
                'tipo' => 'SAIDA',
                'origem' => 'ESTORNO',
                'valor' => (float) $pagamento['valor'],
                'data' => date('Y-m-d'),
                'descricao' => 'Estorno do pagamento #' . (int) $pagamento['id'],
                'referencia_id' => (int) $pagamento['id'],
                'referencia_tipo' => 'pagamento',
            ]);

            if ($pagamento['cobranca_id'] !== null) {
                $this->syncCobrancaStatus((int) $pagamento['cobranca_id']);
            }
        });
    }

    public function assertCanAccess(
        int $id,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): array {
        $pagamento = $this->pagamentos->findById($id);

        if ($pagamento === null) {
            throw new ValidationException(['pagamento' => ['Pagamento inexistente.']]);
        }

        if ($redeId === null && $unidadeId === null && $regiaoId === null) {
            return $pagamento;
        }

        if ($unidadeId !== null && (int) $pagamento['unidade_id'] === $unidadeId) {
            return $pagamento;
        }

        if (
            $unidadeId === null
            && $regiaoId !== null
            && $this->unidades->isInRegiao((int) $pagamento['unidade_id'], $regiaoId)
        ) {
            return $pagamento;
        }

        if (
            $unidadeId === null
            && $regiaoId === null
            && $redeId !== null
            && (int) $pagamento['rede_id'] === $redeId
        ) {
            return $pagamento;
        }

        throw new ForbiddenException('Pagamento fora do seu contexto.');
    }

    public function forScope(
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        string $search = ''
    ): array {
        return $this->pagamentos->forScope($redeId, $unidadeId, $regiaoId, $search);
    }

    private function syncCobrancaStatus(int $cobrancaId): void
    {
        $cobranca = $this->cobrancas->findById($cobrancaId);

        if ($cobranca === null) {
            return;
        }

        $pago = $this->pagamentos->sumAllocatedForCobranca($cobrancaId);
        $total = (float) $cobranca['valor_total'];

        if ($pago + 0.01 >= $total) {
            $this->cobrancas->updateStatus($cobrancaId, 'PAGA');
            return;
        }

        if ($pago > 0) {
            $this->cobrancas->updateStatus($cobrancaId, 'PARCIAL');
            return;
        }

        $status = $cobranca['data_vencimento'] < date('Y-m-d') ? 'VENCIDA' : 'ABERTA';
        $this->cobrancas->updateStatus($cobrancaId, $status);
    }

    private function normalizeDate(mixed $value, string $field): string
    {
        $date = trim((string) $value);
        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d', $date);

        if ($parsed === false || $parsed->format('Y-m-d') !== $date) {
            throw new ValidationException([$field => ['Informe uma data válida.']]);
        }

        return $date;
    }
}

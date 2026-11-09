<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;
use App\Repositories\CentroCustoRepository;
use App\Repositories\ContaBancariaRepository;
use App\Repositories\ContaPagarRepository;
use App\Repositories\MovimentoCaixaRepository;
use App\Repositories\UnidadeRepository;
use App\Support\Database;
use App\Support\PublicId;

final class ContaPagarService
{
    public function __construct(
        private readonly ContaPagarRepository $contasPagar,
        private readonly ContaBancariaRepository $contas,
        private readonly CentroCustoRepository $centros,
        private readonly MovimentoCaixaRepository $movimentos,
        private readonly UnidadeRepository $unidades,
        private readonly Database $database
    ) {
    }

    public function create(
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        array $data
    ): int {
        $unidadeAlvoId = (int) ($data['unidade_id'] ?? $unidadeId ?? 0);
        $unidade = $unidadeAlvoId > 0 ? $this->unidades->findById($unidadeAlvoId) : null;

        if ($unidade === null) {
            throw new ValidationException(['unidade_id' => ['Selecione uma unidade válida.']]);
        }

        $this->assertUnidadeInScope($unidade, $redeId, $unidadeId, $regiaoId);

        $descricao = trim((string) ($data['descricao'] ?? ''));
        $competencia = trim((string) ($data['competencia'] ?? ''));
        $vencimento = $this->normalizeDate($data['data_vencimento'] ?? '', 'data_vencimento');
        $valor = round((float) ($data['valor'] ?? 0), 2);
        $emissao = null;

        if (trim((string) ($data['data_emissao'] ?? '')) !== '') {
            $emissao = $this->normalizeDate($data['data_emissao'], 'data_emissao');
        }

        if ($descricao === '') {
            throw new ValidationException(['descricao' => ['Informe a descrição da conta.']]);
        }

        if (!preg_match('/^\d{4}-\d{2}$/', $competencia)) {
            throw new ValidationException(['competencia' => ['Informe a competência no formato AAAA-MM.']]);
        }

        if ($valor <= 0) {
            throw new ValidationException(['valor' => ['Informe um valor maior que zero.']]);
        }

        $centroId = ($data['centro_custo_id'] ?? '') !== '' ? (int) $data['centro_custo_id'] : null;

        if ($centroId !== null) {
            $centro = $this->centros->findById($centroId);

            if ($centro === null || (int) $centro['unidade_id'] !== (int) $unidade['id']) {
                throw new ValidationException(['centro_custo_id' => ['Centro de custo inválido para a unidade.']]);
            }
        }

        $id = $this->contasPagar->create([
            'public_id' => PublicId::generate(),
            'rede_id' => (int) $unidade['rede_id'],
            'unidade_id' => (int) $unidade['id'],
            'fornecedor_id' => null,
            'centro_custo_id' => $centroId,
            'descricao' => $descricao,
            'competencia' => $competencia,
            'data_emissao' => $emissao,
            'data_vencimento' => $vencimento,
            'valor' => $valor,
            'status' => 'ABERTA',
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);

        return $id;
    }

    public function pay(
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        int $id,
        string $dataPagamento,
        int $contaBancariaId
    ): void {
        $contaPagar = $this->assertCanAccess($id, $redeId, $unidadeId, $regiaoId);

        if ($contaPagar['status'] === 'PAGA') {
            throw new ValidationException(['conta' => ['Esta conta já está paga.']]);
        }

        if ($contaPagar['status'] === 'CANCELADA') {
            throw new ValidationException(['conta' => ['Esta conta está cancelada.']]);
        }

        $conta = $this->contas->findById($contaBancariaId);

        if ($conta === null || $conta['status'] !== 'ATIVO') {
            throw new ValidationException(['conta_bancaria_id' => ['Conta bancária inválida ou inativa.']]);
        }

        if ((int) $conta['unidade_id'] !== (int) $contaPagar['unidade_id']) {
            throw new ValidationException(['conta_bancaria_id' => ['A conta não pertence à unidade da despesa.']]);
        }

        $dataPagamento = $this->normalizeDate($dataPagamento, 'data_pagamento');

        $this->database->transaction(function () use (
            $actorId,
            $contaPagar,
            $conta,
            $dataPagamento
        ): void {
            $this->contasPagar->markPaid((int) $contaPagar['id'], $dataPagamento, (int) $conta['id'], $actorId);

            $this->movimentos->create([
                'rede_id' => (int) $contaPagar['rede_id'],
                'unidade_id' => (int) $contaPagar['unidade_id'],
                'conta_bancaria_id' => (int) $conta['id'],
                'tipo' => 'SAIDA',
                'origem' => 'CONTA_PAGAR',
                'valor' => (float) $contaPagar['valor'],
                'data' => $dataPagamento,
                'descricao' => 'Pagamento: ' . (string) $contaPagar['descricao'],
                'referencia_id' => (int) $contaPagar['id'],
                'referencia_tipo' => 'conta_pagar',
            ]);
        });
    }

    public function cancel(
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        int $id
    ): void {
        $contaPagar = $this->assertCanAccess($id, $redeId, $unidadeId, $regiaoId);

        if ($contaPagar['status'] === 'PAGA') {
            throw new ValidationException(['conta' => ['Contas pagas não podem ser canceladas.']]);
        }

        $this->contasPagar->updateStatus($id, 'CANCELADA', $actorId);
    }

    public function assertCanAccess(
        int $id,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): array {
        $conta = $this->contasPagar->findById($id);

        if ($conta === null) {
            throw new ValidationException(['conta' => ['Conta a pagar inexistente.']]);
        }

        $this->assertUnidadeInScope($conta, $redeId, $unidadeId, $regiaoId);

        return $conta;
    }

    public function forScope(
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        string $search = ''
    ): array {
        return $this->contasPagar->forScope($redeId, $unidadeId, $regiaoId, $search);
    }

    private function assertUnidadeInScope(
        array $unidade,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): void {
        if ($redeId === null && $unidadeId === null && $regiaoId === null) {
            return;
        }

        $unidadeValue = (int) ($unidade['unidade_id'] ?? $unidade['id']);

        if ($unidadeId !== null && $unidadeValue === $unidadeId) {
            return;
        }

        if (
            $unidadeId === null
            && $regiaoId !== null
            && $this->unidades->isInRegiao($unidadeValue, $regiaoId)
        ) {
            return;
        }

        if (
            $unidadeId === null
            && $regiaoId === null
            && $redeId !== null
            && (int) $unidade['rede_id'] === $redeId
        ) {
            return;
        }

        throw new ForbiddenException('Conta fora do seu contexto.');
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

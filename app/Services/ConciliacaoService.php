<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;
use App\Repositories\ConciliacaoRepository;
use App\Repositories\ContaBancariaRepository;
use App\Repositories\MovimentoCaixaRepository;
use App\Repositories\UnidadeRepository;
use App\Support\Database;

final class ConciliacaoService
{
    public function __construct(
        private readonly ConciliacaoRepository $conciliacoes,
        private readonly ContaBancariaRepository $contas,
        private readonly MovimentoCaixaRepository $movimentos,
        private readonly UnidadeRepository $unidades,
        private readonly Database $database
    ) {
    }

    public function create(
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        array $data
    ): int {
        $conta = $this->contas->findById((int) ($data['conta_bancaria_id'] ?? 0));

        if ($conta === null || $conta['status'] !== 'ATIVO') {
            throw new ValidationException(['conta_bancaria_id' => ['Conta bancária inválida ou inativa.']]);
        }

        $this->assertUnidadeInScope(
            (int) $conta['unidade_id'],
            (int) $conta['rede_id'],
            $redeId,
            $unidadeId,
            $regiaoId
        );

        $competencia = trim((string) ($data['competencia'] ?? ''));

        if (!preg_match('/^\d{4}-\d{2}$/', $competencia)) {
            throw new ValidationException(['competencia' => ['Informe a competência no formato AAAA-MM.']]);
        }

        return $this->conciliacoes->create([
            'rede_id' => (int) $conta['rede_id'],
            'unidade_id' => (int) $conta['unidade_id'],
            'conta_bancaria_id' => (int) $conta['id'],
            'competencia' => $competencia,
            'status' => 'ABERTA',
            'importado_em' => date('Y-m-d H:i:s'),
        ]);
    }

    public function importCsv(int $id, string $csv): int
    {
        $conciliacao = $this->findOrFail($id);
        $linhas = $this->parseCsv($csv);

        if ($linhas === []) {
            throw new ValidationException(['arquivo' => ['Nenhuma linha válida encontrada no arquivo.']]);
        }

        return $this->database->transaction(function () use ($conciliacao, $linhas): int {
            $itens = $this->conciliacoes->items((int) $conciliacao['id']);
            $usados = [];

            foreach ($itens as $item) {
                if ($item['movimento_caixa_id'] !== null) {
                    $usados[(int) $item['movimento_caixa_id']] = true;
                }
            }

            $movimentos = $this->movimentos->forContaPeriodo(
                (int) $conciliacao['conta_bancaria_id'],
                (int) $conciliacao['competencia'] . '-01',
                date('Y-m-t', strtotime($conciliacao['competencia'] . '-01'))
            );

            $importados = 0;

            foreach ($linhas as $linha) {
                $movimentoId = null;
                $diferenca = 0.0;

                foreach ($movimentos as $movimento) {
                    $movimentoIdAtual = (int) $movimento['id'];

                    if (isset($usados[$movimentoIdAtual])) {
                        continue;
                    }

                    if (
                        $movimento['data'] === $linha['data']
                        && $movimento['tipo'] === $linha['tipo']
                        && abs((float) $movimento['valor'] - $linha['valor']) < 0.01
                    ) {
                        $movimentoId = $movimentoIdAtual;
                        $usados[$movimentoIdAtual] = true;
                        break;
                    }
                }

                $this->conciliacoes->addItem([
                    'conciliacao_id' => (int) $conciliacao['id'],
                    'data' => $linha['data'],
                    'descricao' => $linha['descricao'],
                    'valor' => $linha['valor'],
                    'tipo' => $linha['tipo'],
                    'movimento_caixa_id' => $movimentoId,
                    'status' => $movimentoId !== null ? 'CONCILIADO' : 'PENDENTE',
                    'diferenca' => $diferenca,
                ]);

                $importados++;
            }

            return $importados;
        });
    }

    public function close(int $id, int $actorId): void
    {
        $conciliacao = $this->findOrFail($id);
        $itens = $this->conciliacoes->items($id);

        foreach ($itens as $item) {
            if ($item['status'] !== 'CONCILIADO') {
                throw new ValidationException([
                    'conciliacao' => ['Existem itens pendentes. Concilie todos os itens antes de fechar.'],
                ]);
            }
        }

        $this->conciliacoes->close($id, $actorId);
    }

    public function assertCanAccess(
        int $id,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): array {
        $conciliacao = $this->conciliacoes->findById($id);

        if ($conciliacao === null) {
            throw new ValidationException(['conciliacao' => ['Conciliação inexistente.']]);
        }

        $this->assertUnidadeInScope(
            (int) $conciliacao['unidade_id'],
            (int) $conciliacao['rede_id'],
            $redeId,
            $unidadeId,
            $regiaoId
        );

        return $conciliacao;
    }

    public function forScope(?int $redeId, ?int $unidadeId, ?int $regiaoId): array
    {
        return $this->conciliacoes->forScope($redeId, $unidadeId, $regiaoId);
    }

    public function items(int $id): array
    {
        return $this->conciliacoes->items($id);
    }

    private function findOrFail(int $id): array
    {
        $conciliacao = $this->conciliacoes->findById($id);

        if ($conciliacao === null) {
            throw new ValidationException(['conciliacao' => ['Conciliação inexistente.']]);
        }

        return $conciliacao;
    }

    private function parseCsv(string $csv): array
    {
        $linhas = preg_split('/\r\n|\r|\n/', trim($csv)) ?: [];
        $resultado = [];
        $first = true;

        foreach ($linhas as $linha) {
            $linha = trim($linha);

            if ($linha === '') {
                continue;
            }

            $delimiter = str_contains($linha, ';') ? ';' : ',';
            $cols = array_map('trim', explode($delimiter, $linha));

            if ($first && (stripos($cols[0], 'data') !== false || stripos($cols[0], 'date') !== false)) {
                $first = false;
                continue;
            }

            $first = false;

            if (count($cols) < 3) {
                continue;
            }

            $data = $this->parseDate($cols[0]);
            $descricao = $cols[1] !== '' ? $cols[1] : null;
            $valor = $this->parseValor($cols[2] ?? '');
            $tipo = strtoupper($cols[3] ?? 'ENTRADA');

            if ($tipo !== 'ENTRADA' && $tipo !== 'SAIDA') {
                $tipo = $valor < 0 ? 'SAIDA' : 'ENTRADA';
            }

            $valor = abs($valor);

            if ($data === null || $valor <= 0) {
                continue;
            }

            $resultado[] = [
                'data' => $data,
                'descricao' => $descricao,
                'valor' => $valor,
                'tipo' => $tipo,
            ];
        }

        return $resultado;
    }

    private function parseDate(string $value): ?string
    {
        foreach (['Y-m-d', 'd/m/Y'] as $format) {
            $parsed = \DateTimeImmutable::createFromFormat($format, $value);

            if ($parsed !== false && $parsed->format($format) === $value) {
                return $parsed->format('Y-m-d');
            }
        }

        return null;
    }

    private function parseValor(string $value): float
    {
        $value = str_replace(['R$', ' ', '.'], '', $value);
        $value = str_replace(',', '.', $value);

        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function assertUnidadeInScope(
        int $unidadeValue,
        int $redeValue,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): void {
        if ($redeId === null && $unidadeId === null && $regiaoId === null) {
            return;
        }

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

        if ($unidadeId === null && $regiaoId === null && $redeId !== null && $redeValue === $redeId) {
            return;
        }

        throw new ForbiddenException('Conciliação fora do seu contexto.');
    }
}

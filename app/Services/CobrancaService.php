<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;
use App\Repositories\CobrancaRepository;
use App\Repositories\MatriculaRepository;
use App\Repositories\UnidadeRepository;
use App\Support\Database;
use App\Support\PublicId;

final class CobrancaService
{
    private const TIPOS = ['MENSALIDADE', 'SERVICO', 'MATERIAL', 'OUTRO'];

    public function __construct(
        private readonly CobrancaRepository $cobrancas,
        private readonly MatriculaRepository $matriculas,
        private readonly AlunoService $alunos,
        private readonly UnidadeRepository $unidades,
        private readonly Database $database
    ) {
    }

    public function generateForMatricula(int $matriculaId, string $competencia, string $vencimento): int
    {
        $matricula = $this->matriculas->findById($matriculaId);

        if ($matricula === null) {
            throw new ValidationException(['matricula_id' => ['Matrícula inexistente.']]);
        }

        if (!in_array($matricula['status'], ['ATIVA', 'SUSPENSA'], true)) {
            throw new ValidationException(['matricula_id' => ['A matrícula não está ativa.']]);
        }

        $competencia = $this->normalizeCompetencia($competencia);
        $vencimento = $this->normalizeDate($vencimento, 'data_vencimento');

        if ($this->cobrancas->existsForMatriculaCompetencia($matriculaId, $competencia)) {
            throw new ValidationException([
                'competencia' => ['Já existe uma mensalidade para esta matrícula nesta competência.'],
            ]);
        }

        $valor = (float) $matricula['valor_mensal'];

        if ($valor <= 0) {
            throw new ValidationException(['matricula_id' => ['A matrícula não possui valor mensal.']]);
        }

        return $this->database->transaction(function () use (
            $matricula,
            $competencia,
            $vencimento,
            $valor
        ): int {
            $publicId = PublicId::generate();
            $id = $this->cobrancas->create([
                'public_id' => $publicId,
                'rede_id' => (int) $matricula['rede_id'],
                'unidade_id' => (int) $matricula['unidade_id'],
                'aluno_id' => (int) $matricula['aluno_id'],
                'matricula_id' => (int) $matricula['id'],
                'contrato_id' => $matricula['contrato_id'],
                'competencia' => $competencia,
                'numero' => $this->number($competencia, $publicId),
                'tipo' => 'MENSALIDADE',
                'data_emissao' => date('Y-m-d'),
                'data_vencimento' => $vencimento,
                'valor_original' => $valor,
                'valor_total' => $valor,
                'status' => 'ABERTA',
            ]);

            $this->cobrancas->addItem(
                $id,
                'Mensalidade ' . (string) $matricula['plano_nome'] . ' - ' . $competencia,
                1,
                $valor,
                $valor,
                (int) $matricula['plano_id']
            );

            return $id;
        });
    }

    public function createManual(
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        array $data
    ): int {
        $aluno = $this->alunos->assertCanAccess(
            (int) ($data['aluno_id'] ?? 0),
            $redeId,
            $unidadeId,
            $regiaoId
        );

        $competencia = $this->normalizeCompetencia($data['competencia'] ?? '');
        $vencimento = $this->normalizeDate($data['data_vencimento'] ?? '', 'data_vencimento');
        $valor = round((float) ($data['valor'] ?? 0), 2);
        $desconto = round((float) ($data['valor_desconto'] ?? 0), 2);
        $tipo = strtoupper(trim((string) ($data['tipo'] ?? 'MENSALIDADE')));

        if ($valor <= 0) {
            throw new ValidationException(['valor' => ['Informe um valor maior que zero.']]);
        }

        if ($desconto < 0 || $desconto > $valor) {
            throw new ValidationException(['valor_desconto' => ['Desconto inválido.']]);
        }

        if (!in_array($tipo, self::TIPOS, true)) {
            throw new ValidationException(['tipo' => ['Tipo de cobrança inválido.']]);
        }

        $total = round($valor - $desconto, 2);
        $descricao = trim((string) ($data['descricao'] ?? ''));

        if ($descricao === '') {
            $descricao = 'Cobrança ' . $tipo . ' - ' . $competencia;
        }

        $matricula = $this->matriculas->findActiveByAlunoUnidade(
            (int) $aluno['id'],
            (int) $aluno['unidade_id']
        );

        return $this->database->transaction(function () use (
            $actorId,
            $aluno,
            $competencia,
            $vencimento,
            $valor,
            $desconto,
            $total,
            $tipo,
            $descricao,
            $matricula
        ): int {
            $publicId = PublicId::generate();
            $id = $this->cobrancas->create([
                'public_id' => $publicId,
                'rede_id' => (int) $aluno['rede_id'],
                'unidade_id' => (int) $aluno['unidade_id'],
                'aluno_id' => (int) $aluno['id'],
                'matricula_id' => $matricula !== null ? (int) $matricula['id'] : null,
                'contrato_id' => null,
                'competencia' => $competencia,
                'numero' => $this->number($competencia, $publicId),
                'tipo' => $tipo,
                'data_emissao' => date('Y-m-d'),
                'data_vencimento' => $vencimento,
                'valor_original' => $valor,
                'valor_desconto' => $desconto,
                'valor_total' => $total,
                'status' => 'ABERTA',
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            $this->cobrancas->addItem($id, $descricao, 1, $valor, $total);

            return $id;
        });
    }

    public function assertCanAccess(
        int $id,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): array {
        $cobranca = $this->cobrancas->findById($id);

        if ($cobranca === null) {
            throw new ValidationException(['cobranca' => ['Cobrança inexistente.']]);
        }

        if ($redeId === null && $unidadeId === null && $regiaoId === null) {
            return $cobranca;
        }

        if ($unidadeId !== null && (int) $cobranca['unidade_id'] === $unidadeId) {
            return $cobranca;
        }

        if (
            $unidadeId === null
            && $regiaoId !== null
            && $this->unidades->isInRegiao((int) $cobranca['unidade_id'], $regiaoId)
        ) {
            return $cobranca;
        }

        if (
            $unidadeId === null
            && $regiaoId === null
            && $redeId !== null
            && (int) $cobranca['rede_id'] === $redeId
        ) {
            return $cobranca;
        }

        throw new ForbiddenException('Cobrança fora do seu contexto.');
    }

    public function forScope(
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        string $search = '',
        bool $onlyOpen = false
    ): array {
        return $this->cobrancas->forScope($redeId, $unidadeId, $regiaoId, $search, $onlyOpen);
    }

    public function refreshOverdueStatus(): void
    {
        $this->cobrancas->refreshOverdueStatus();
    }

    private function normalizeCompetencia(mixed $value): string
    {
        $competencia = trim((string) $value);

        if (!preg_match('/^\d{4}-\d{2}$/', $competencia)) {
            throw new ValidationException(['competencia' => ['Informe a competência no formato AAAA-MM.']]);
        }

        return $competencia;
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

    private function number(string $competencia, string $publicId): string
    {
        return 'COB-' . str_replace('-', '', $competencia) . '-' . strtoupper(substr($publicId, -6));
    }
}

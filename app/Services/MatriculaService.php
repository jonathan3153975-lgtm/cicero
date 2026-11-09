<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;
use App\Repositories\AlunoRepository;
use App\Repositories\ContratoRepository;
use App\Repositories\MatriculaRepository;
use App\Repositories\PlanoPrecoRepository;
use App\Repositories\PlanoRepository;
use App\Repositories\UnidadeRepository;
use App\Support\Database;
use App\Support\PublicId;
use App\Validators\Validator;
use DateTimeImmutable;
use PDO;

final class MatriculaService
{
    private const STATUSES = ['ATIVA', 'SUSPENSA', 'CANCELADA', 'ENCERRADA', 'TRANSFERIDA'];

    public function __construct(
        private readonly AlunoService $alunos,
        private readonly AlunoRepository $alunoRepository,
        private readonly PlanoPrecoRepository $precos,
        private readonly PlanoRepository $planos,
        private readonly ContratoRepository $contratos,
        private readonly MatriculaRepository $matriculas,
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
        $alunoId = (int) ($data['aluno_id'] ?? 0);
        $aluno = $this->alunos->assertCanAccess($alunoId, $redeId, $unidadeId, $regiaoId);

        $precoId = (int) ($data['plano_unidade_preco_id'] ?? 0);
        $preco = $this->precos->findById($precoId);

        if ($preco === null) {
            throw new ValidationException(['plano_unidade_preco_id' => ['Preço de plano inexistente.']]);
        }

        if ($preco['status'] !== 'ATIVO') {
            throw new ValidationException(['plano_unidade_preco_id' => ['Este preço não está ativo.']]);
        }

        if ((int) $preco['unidade_id'] !== (int) $aluno['unidade_id']) {
            throw new ValidationException(['plano_unidade_preco_id' => ['O preço não pertence à unidade do aluno.']]);
        }

        if ((int) $preco['rede_id'] !== (int) $aluno['rede_id']) {
            throw new ValidationException(['plano_unidade_preco_id' => ['O preço não pertence à rede do aluno.']]);
        }

        $plano = $this->planos->findById((int) $preco['plano_id']);

        if ($plano === null || $plano['status'] !== 'ATIVO') {
            throw new ValidationException(['plano_unidade_preco_id' => ['Plano inativo ou inexistente.']]);
        }

        if ($plano['rede_id'] !== null && (int) $plano['rede_id'] !== (int) $aluno['rede_id']) {
            throw new ValidationException(['plano_unidade_preco_id' => ['Plano fora da rede do aluno.']]);
        }

        $dataMatricula = $this->normalizeDate($data['data_matricula'] ?? date('Y-m-d'), 'data_matricula');
        $dataInicio = $this->normalizeDate($data['data_inicio'] ?? $dataMatricula, 'data_inicio');

        if ($preco['vigencia_inicio'] > $dataInicio) {
            throw new ValidationException(['data_inicio' => ['A vigência do preço inicia após a data de início.']]);
        }

        if ($preco['vigencia_fim'] !== null && $preco['vigencia_fim'] < $dataInicio) {
            throw new ValidationException(['data_inicio' => ['A vigência do preço terminou antes da data de início.']]);
        }

        if ($this->matriculas->findActiveByAlunoUnidade($alunoId, (int) $aluno['unidade_id']) !== null) {
            throw new ValidationException(['aluno_id' => ['O aluno já possui matrícula ativa nesta unidade.']]);
        }

        $status = strtoupper(trim((string) ($data['status'] ?? 'ATIVA')));

        if (!in_array($status, self::STATUSES, true)) {
            throw new ValidationException(['status' => ['Status de matrícula inválido.']]);
        }

        $dataFim = null;

        if ($plano['duracao_meses'] !== null && (int) $plano['duracao_meses'] > 0) {
            $dataFim = (new DateTimeImmutable($dataInicio))
                ->modify('+' . (int) $plano['duracao_meses'] . ' months')
                ->format('Y-m-d');
        }

        return $this->database->transaction(function (PDO $pdo) use (
            $actorId,
            $aluno,
            $preco,
            $plano,
            $data,
            $dataMatricula,
            $dataInicio,
            $dataFim,
            $status
        ): int {
            $contratoId = $this->contratos->create([
                'public_id' => PublicId::generate(),
                'aluno_id' => (int) $aluno['id'],
                'rede_id' => (int) $aluno['rede_id'],
                'unidade_id' => (int) $aluno['unidade_id'],
                'plano_id' => (int) $plano['id'],
                'numero' => $this->contractNumber(),
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
                'renovacao_automatica' => !empty($data['renovacao_automatica']),
                'dia_vencimento' => !empty($data['dia_vencimento']) ? (int) $data['dia_vencimento'] : 10,
                'valor_mensal' => $preco['valor_mensal'],
                'status' => 'ATIVO',
                'observacoes' => trim((string) ($data['observacoes'] ?? '')),
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            return $this->matriculas->create([
                'public_id' => PublicId::generate(),
                'aluno_id' => (int) $aluno['id'],
                'rede_id' => (int) $aluno['rede_id'],
                'unidade_id' => (int) $aluno['unidade_id'],
                'contrato_id' => $contratoId,
                'plano_unidade_preco_id' => (int) $preco['id'],
                'data_matricula' => $dataMatricula,
                'data_inicio' => $dataInicio,
                'data_encerramento' => null,
                'motivo_encerramento' => null,
                'status' => $status,
                'observacoes' => trim((string) ($data['observacoes'] ?? '')),
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);
        });
    }

    public function update(
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        int $id,
        array $data
    ): void {
        $matricula = $this->assertCanAccess($id, $redeId, $unidadeId, $regiaoId);
        $status = strtoupper(trim((string) ($data['status'] ?? $matricula['status'])));
        $validator = (new Validator(['status' => $status]))->in('status', self::STATUSES);
        $errors = $validator->errors();

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $dataEncerramento = trim((string) ($data['data_encerramento'] ?? ''));

        if ($dataEncerramento === '' && in_array($status, ['CANCELADA', 'ENCERRADA', 'TRANSFERIDA'], true)) {
            $dataEncerramento = date('Y-m-d');
        }

        if ($dataEncerramento !== '') {
            $this->normalizeDate($dataEncerramento, 'data_encerramento');
        }

        $this->database->transaction(function () use (
            $matricula,
            $actorId,
            $status,
            $dataEncerramento,
            $data
        ): void {
            $this->matriculas->update((int) $matricula['id'], [
                'public_id' => $matricula['public_id'],
                'aluno_id' => (int) $matricula['aluno_id'],
                'rede_id' => (int) $matricula['rede_id'],
                'unidade_id' => (int) $matricula['unidade_id'],
                'contrato_id' => $matricula['contrato_id'],
                'plano_unidade_preco_id' => (int) $matricula['plano_unidade_preco_id'],
                'data_matricula' => (string) ($data['data_matricula'] ?? $matricula['data_matricula']),
                'data_inicio' => (string) ($data['data_inicio'] ?? $matricula['data_inicio']),
                'data_encerramento' => $dataEncerramento,
                'motivo_encerramento' => trim((string) ($data['motivo_encerramento'] ?? '')),
                'status' => $status,
                'observacoes' => trim((string) ($data['observacoes'] ?? '')),
                'updated_by' => $actorId,
            ]);

            if ($matricula['contrato_id'] !== null) {
                $contractStatus = match ($status) {
                    'ATIVA' => 'ATIVO',
                    'SUSPENSA' => 'SUSPENSO',
                    'CANCELADA' => 'CANCELADO',
                    'ENCERRADA' => 'ENCERRADO',
                    'TRANSFERIDA' => 'TRANSFERIDO',
                };

                $this->contratos->updateStatus((int) $matricula['contrato_id'], $contractStatus);
            }
        });
    }

    public function assertCanAccess(
        int $matriculaId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): array {
        $matricula = $this->matriculas->findById($matriculaId);

        if ($matricula === null) {
            throw new ValidationException(['matricula' => ['Matrícula inexistente.']]);
        }

        if ($redeId === null && $unidadeId === null && $regiaoId === null) {
            return $matricula;
        }

        if ($unidadeId !== null && (int) $matricula['unidade_id'] === $unidadeId) {
            return $matricula;
        }

        if (
            $unidadeId === null
            && $regiaoId !== null
            && $this->unidades->isInRegiao((int) $matricula['unidade_id'], $regiaoId)
        ) {
            return $matricula;
        }

        if (
            $unidadeId === null
            && $regiaoId === null
            && $redeId !== null
            && (int) $matricula['rede_id'] === $redeId
        ) {
            return $matricula;
        }

        throw new ForbiddenException('Matrícula fora do seu contexto.');
    }

    public function forScope(
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        string $search = ''
    ): array {
        return $this->matriculas->forScope($redeId, $unidadeId, $regiaoId, $search);
    }

    private function normalizeDate(mixed $value, string $field): string
    {
        $value = trim((string) $value);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            throw new ValidationException([$field => ['Informe a data no formato AAAA-MM-DD.']]);
        }

        return $value;
    }

    private function contractNumber(): string
    {
        return 'CT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }
}

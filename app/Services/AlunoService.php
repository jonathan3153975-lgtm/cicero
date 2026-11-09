<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;
use App\Repositories\AlunoRepository;
use App\Repositories\AlunoResponsavelRepository;
use App\Repositories\PessoaRepository;
use App\Repositories\UnidadeRepository;
use App\Support\Database;
use App\Support\PublicId;
use App\Validators\Validator;
use PDO;

final class AlunoService
{
    private const STATUSES = ['ATIVO', 'INATIVO', 'BLOQUEADO'];

    public function __construct(
        private readonly AlunoRepository $alunos,
        private readonly AlunoResponsavelRepository $responsaveis,
        private readonly PessoaService $pessoas,
        private readonly PessoaRepository $pessoaRepository,
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
        [$targetRedeId, $targetUnidadeId] = $this->resolveUnidade($data, $redeId, $unidadeId, $regiaoId);
        $data = $this->normalizeAluno($data, $targetRedeId, $targetUnidadeId, $actorId);
        $this->validateAluno($data);

        $existingPessoaId = (int) ($data['pessoa_id'] ?? 0);
        $pessoaId = $existingPessoaId;

        if ($pessoaId > 0) {
            $this->pessoas->assertCanAccess($actorId, $pessoaId, $redeId, $unidadeId, $regiaoId);

            if ($this->alunos->findByPessoaId($pessoaId) !== null) {
                throw new ValidationException(['pessoa_id' => ['Esta pessoa já possui cadastro de aluno.']]);
            }
        }

        return $this->database->transaction(function (PDO $pdo) use (
            $actorId,
            $targetRedeId,
            $targetUnidadeId,
            $data,
            &$pessoaId
        ): int {
            if ($pessoaId <= 0) {
                $pessoaId = $this->pessoas->create($data, $targetRedeId, $actorId);
            }

            $alunoData = array_merge($data, [
                'public_id' => PublicId::generate(),
                'pessoa_id' => $pessoaId,
                'rede_id' => $targetRedeId,
                'unidade_id' => $targetUnidadeId,
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            return $this->alunos->create($alunoData);
        });
    }

    public function update(
        int $alunoId,
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        array $data
    ): void {
        $aluno = $this->assertCanAccess($alunoId, $redeId, $unidadeId, $regiaoId);
        [$targetRedeId, $targetUnidadeId] = $this->resolveUnidade($data, $redeId, $unidadeId, $regiaoId, $aluno);

        $data = $this->normalizeAluno($data, $targetRedeId, $targetUnidadeId, $actorId);
        $this->validateAluno($data);

        $personRedeId = $redeId ?? (int) $aluno['rede_id'];

        $this->database->transaction(function () use (
            $aluno,
            $actorId,
            $personRedeId,
            $redeId,
            $unidadeId,
            $regiaoId,
            $data,
            $targetRedeId,
            $targetUnidadeId
        ): void {
            $this->pessoas->update(
                (int) $aluno['pessoa_id'],
                $data,
                $personRedeId,
                $unidadeId,
                $regiaoId,
                $actorId
            );

            $this->alunos->update((int) $aluno['id'], array_merge($data, [
                'rede_id' => $targetRedeId,
                'unidade_id' => $targetUnidadeId,
                'updated_by' => $actorId,
            ]));
        });
    }

    public function addResponsavel(
        int $alunoId,
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        array $data
    ): int {
        $aluno = $this->assertCanAccess($alunoId, $redeId, $unidadeId, $regiaoId);
        $responsavelPessoaId = (int) ($data['responsavel_pessoa_id'] ?? 0);

        if ($responsavelPessoaId > 0) {
            $this->pessoas->assertCanAccess($actorId, $responsavelPessoaId, $redeId, $unidadeId, $regiaoId);
        }

        if ($responsavelPessoaId === 0) {
            $responsavelPessoaId = $this->pessoas->create($data, (int) $aluno['rede_id'], $actorId);
        }

        if ($responsavelPessoaId === (int) $aluno['pessoa_id']) {
            throw new ValidationException(['responsavel_pessoa_id' => ['O aluno não pode ser responsável por si mesmo.']]);
        }

        $parentesco = trim((string) ($data['parentesco'] ?? ''));

        return $this->responsaveis->add(
            $alunoId,
            $responsavelPessoaId,
            $parentesco !== '' ? $parentesco : null,
            !empty($data['responsavel_financeiro']),
            !empty($data['autorizado_buscar'])
        );
    }

    public function removeResponsavel(
        int $alunoId,
        int $responsavelId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): void {
        $this->assertCanAccess($alunoId, $redeId, $unidadeId, $regiaoId);
        $responsavel = $this->responsaveis->findById($responsavelId);

        if ($responsavel === null || (int) $responsavel['aluno_id'] !== $alunoId) {
            throw new ValidationException(['responsavel' => ['Responsável inexistente para este aluno.']]);
        }

        $this->responsaveis->deactivate($responsavelId);
    }

    public function assertCanAccess(
        int $alunoId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): array {
        $aluno = $this->alunos->findById($alunoId);

        if ($aluno === null) {
            throw new ValidationException(['aluno' => ['Aluno inexistente.']]);
        }

        if ($redeId === null && $unidadeId === null && $regiaoId === null) {
            return $aluno;
        }

        if ($unidadeId !== null && (int) $aluno['unidade_id'] === $unidadeId) {
            return $aluno;
        }

        if (
            $unidadeId === null
            && $regiaoId !== null
            && $this->unidades->isInRegiao((int) $aluno['unidade_id'], $regiaoId)
        ) {
            return $aluno;
        }

        if (
            $unidadeId === null
            && $regiaoId === null
            && $redeId !== null
            && (int) $aluno['rede_id'] === $redeId
        ) {
            return $aluno;
        }

        throw new ForbiddenException('Aluno fora do seu contexto.');
    }

    public function forScope(
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        string $search = ''
    ): array {
        return $this->alunos->forScope($redeId, $unidadeId, $regiaoId, $search);
    }

    private function resolveUnidade(
        array $data,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        ?array $aluno = null
    ): array {
        $targetUnidadeId = $unidadeId ?? (int) ($data['unidade_id'] ?? 0);

        if ($targetUnidadeId <= 0 && $aluno !== null) {
            $targetUnidadeId = (int) $aluno['unidade_id'];
        }

        if ($targetUnidadeId <= 0) {
            throw new ValidationException(['unidade_id' => ['Selecione a unidade.']]);
        }

        $unidade = $this->unidades->findById($targetUnidadeId);

        if ($unidade === null) {
            throw new ValidationException(['unidade_id' => ['Unidade inexistente.']]);
        }

        $targetRedeId = (int) $unidade['rede_id'];

        if ($redeId !== null && $targetRedeId !== $redeId) {
            throw new ValidationException(['unidade_id' => ['A unidade não pertence à sua rede.']]);
        }

        if ($regiaoId !== null && !$this->unidades->isInRegiao($targetUnidadeId, $regiaoId)) {
            throw new ValidationException(['unidade_id' => ['A unidade não pertence à sua região.']]);
        }

        return [$targetRedeId, $targetUnidadeId];
    }

    private function normalizeAluno(
        array $data,
        int $redeId,
        int $unidadeId,
        int $actorId
    ): array {
        return array_merge($data, [
            'pessoa_id' => (int) ($data['pessoa_id'] ?? 0),
            'rede_id' => $redeId,
            'unidade_id' => $unidadeId,
            'codigo' => trim((string) ($data['codigo'] ?? '')),
            'data_entrada' => trim((string) ($data['data_entrada'] ?? '')),
            'como_conheceu' => trim((string) ($data['como_conheceu'] ?? '')),
            'indicado_por_pessoa_id' => ($data['indicado_por_pessoa_id'] ?? null) ?: null,
            'status' => strtoupper(trim((string) ($data['status'] ?? 'ATIVO'))),
            'observacoes' => trim((string) ($data['observacoes'] ?? '')),
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);
    }

    private function validateAluno(array $data): void
    {
        $validator = (new Validator($data))
            ->in('status', self::STATUSES)
            ->maxLength('codigo', 40)
            ->maxLength('como_conheceu', 80);

        $errors = $validator->errors();

        if (
            $data['data_entrada'] !== ''
            && preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['data_entrada']) !== 1
        ) {
            $errors['data_entrada'][] = 'Informe a data no formato AAAA-MM-DD.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }
    }
}

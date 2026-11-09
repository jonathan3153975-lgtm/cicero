<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;
use App\Repositories\PessoaContatoRepository;
use App\Repositories\PessoaEnderecoRepository;
use App\Repositories\PessoaRepository;
use App\Support\Database;
use App\Support\PublicId;
use App\Validators\Validator;
use PDO;

final class PessoaService
{
    private const TIPOS = ['PF', 'PJ'];
    private const STATUSES = ['ATIVO', 'INATIVO', 'BLOQUEADO'];

    public function __construct(
        private readonly PessoaRepository $pessoas,
        private readonly PessoaContatoRepository $contatos,
        private readonly PessoaEnderecoRepository $enderecos,
        private readonly Database $database
    ) {
    }

    public function create(array $data, ?int $redeId, ?int $actorId): int
    {
        $data = $this->normalize($data);
        $data['rede_id'] = $redeId;
        $data['public_id'] = PublicId::generate();
        $data['created_by'] = $actorId;
        $data['updated_by'] = $actorId;

        $this->validate($data);

        if ($data['cpf'] !== '' && $this->pessoas->findByCpf($data['cpf']) !== null) {
            throw new ValidationException(['cpf' => ['Já existe uma pessoa com este CPF.']]);
        }

        if ($data['cnpj'] !== '' && $this->pessoas->findByCnpj($data['cnpj']) !== null) {
            throw new ValidationException(['cnpj' => ['Já existe uma pessoa com este CNPJ.']]);
        }

        return $this->database->transaction(function (PDO $pdo) use ($data): int {
            $pessoaId = $this->pessoas->create($data);
            $this->syncContatosEndereco($pessoaId, $data);

            return $pessoaId;
        });
    }

    public function update(
        int $id,
        array $data,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        int $actorId
    ): void {
        $this->assertCanAccess($actorId, $id, $redeId, $unidadeId, $regiaoId);

        $data = $this->normalize($data);
        $data['rede_id'] = $redeId;
        $data['updated_by'] = $actorId;

        $this->validate($data);

        $existingCpf = $data['cpf'] !== '' ? $this->pessoas->findByCpf($data['cpf']) : null;

        if ($existingCpf !== null && (int) $existingCpf['id'] !== $id) {
            throw new ValidationException(['cpf' => ['Já existe uma pessoa com este CPF.']]);
        }

        $existingCnpj = $data['cnpj'] !== '' ? $this->pessoas->findByCnpj($data['cnpj']) : null;

        if ($existingCnpj !== null && (int) $existingCnpj['id'] !== $id) {
            throw new ValidationException(['cnpj' => ['Já existe uma pessoa com este CNPJ.']]);
        }

        $this->database->transaction(function () use ($id, $data): void {
            $this->pessoas->update($id, $data);
            $this->syncContatosEndereco($id, $data);
        });
    }

    public function assertCanAccess(
        int $actorId,
        int $pessoaId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): array {
        $pessoa = $this->pessoas->findById($pessoaId);

        if ($pessoa === null) {
            throw new ValidationException(['pessoa' => ['Pessoa inexistente.']]);
        }

        if ($redeId === null && $unidadeId === null && $regiaoId === null) {
            return $pessoa;
        }

        if ((int) ($pessoa['created_by'] ?? 0) === $actorId) {
            return $pessoa;
        }

        if (
            $unidadeId !== null
            && $this->pessoas->isLinkedToAlunoInScope($pessoaId, $redeId, $unidadeId, null)
        ) {
            return $pessoa;
        }

        if (
            $regiaoId !== null
            && $this->pessoas->isLinkedToAlunoInScope($pessoaId, $redeId, null, $regiaoId)
        ) {
            return $pessoa;
        }

        if (
            $redeId !== null
            && $unidadeId === null
            && $regiaoId === null
            && (
                (int) ($pessoa['rede_id'] ?? 0) === $redeId
                || $this->pessoas->isLinkedToAlunoInScope($pessoaId, $redeId, null, null)
            )
        ) {
            return $pessoa;
        }

        throw new ForbiddenException('Pessoa fora do seu contexto.');
    }

    public function forScope(
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        ?int $actorId,
        string $search = ''
    ): array {
        return $this->pessoas->forScope($redeId, $unidadeId, $regiaoId, $actorId, $search);
    }

    private function syncContatosEndereco(int $pessoaId, array $data): void
    {
        $this->contatos->syncPrimary(
            $pessoaId,
            $data['email_principal'] ?: null,
            $data['telefone_principal'] ?: null
        );
        $this->enderecos->syncPrimary($pessoaId, $data);
    }

    private function normalize(array $data): array
    {
        $cpf = preg_replace('/\D/', '', (string) ($data['cpf'] ?? '')) ?? '';
        $cnpj = preg_replace('/\D/', '', (string) ($data['cnpj'] ?? '')) ?? '';

        return [
            'tipo' => strtoupper(trim((string) ($data['tipo'] ?? 'PF'))),
            'nome' => trim((string) ($data['nome'] ?? '')),
            'nome_social' => trim((string) ($data['nome_social'] ?? '')),
            'cpf' => $cpf,
            'cnpj' => $cnpj,
            'data_nascimento' => trim((string) ($data['data_nascimento'] ?? '')),
            'sexo' => trim((string) ($data['sexo'] ?? '')),
            'estado_civil' => trim((string) ($data['estado_civil'] ?? '')),
            'email_principal' => mb_strtolower(trim((string) ($data['email_principal'] ?? ''))),
            'telefone_principal' => trim((string) ($data['telefone_principal'] ?? '')),
            'status' => strtoupper(trim((string) ($data['status'] ?? 'ATIVO'))),
            'observacoes' => trim((string) ($data['observacoes'] ?? '')),
            'cep' => trim((string) ($data['cep'] ?? '')),
            'logradouro' => trim((string) ($data['logradouro'] ?? '')),
            'numero' => trim((string) ($data['numero'] ?? '')),
            'complemento' => trim((string) ($data['complemento'] ?? '')),
            'bairro' => trim((string) ($data['bairro'] ?? '')),
            'cidade' => trim((string) ($data['cidade'] ?? '')),
            'estado' => strtoupper(trim((string) ($data['estado'] ?? ''))),
        ];
    }

    private function validate(array $data): void
    {
        $validator = (new Validator($data))
            ->required('nome')
            ->maxLength('nome', 180)
            ->maxLength('nome_social', 180)
            ->email('email_principal')
            ->maxLength('email_principal', 180)
            ->maxLength('telefone_principal', 30)
            ->in('tipo', self::TIPOS)
            ->in('status', self::STATUSES)
            ->cpf('cpf')
            ->cnpj('cnpj');

        $errors = $validator->errors();

        if ($data['tipo'] === 'PJ' && $data['cnpj'] === '') {
            $errors['cnpj'][] = 'Informe o CNPJ para pessoa jurídica.';
        }

        if ($data['estado'] !== '' && mb_strlen($data['estado']) !== 2) {
            $errors['estado'][] = 'Informe a sigla do estado com 2 letras.';
        }

        if (
            $data['data_nascimento'] !== ''
            && preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['data_nascimento']) !== 1
        ) {
            $errors['data_nascimento'][] = 'Informe a data no formato AAAA-MM-DD.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }
    }
}

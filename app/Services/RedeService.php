<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ValidationException;
use App\Repositories\PlanoRedeRepository;
use App\Repositories\RedeRepository;
use App\Validators\Validator;

final class RedeService
{
    private const STATUSES = ['ATIVA', 'SUSPENSA', 'EM_IMPLANTACAO', 'CANCELADA'];

    public function __construct(
        private readonly RedeRepository $redes,
        private readonly PlanoRedeRepository $planosRede
    ) {
    }

    public function create(array $data): int
    {
        $data = $this->normalize($data);
        $this->validate($data);
        $data = $this->applyPlan($data);

        if ($this->redes->findByCnpj($data['cnpj']) !== null) {
            throw new ValidationException(['cnpj' => ['Já existe uma rede com este CNPJ.']]);
        }

        return $this->redes->create($data);
    }

    public function update(int $id, array $data): void
    {
        $data = $this->normalize($data);
        $this->validate($data);
        $data = $this->applyPlan($data);

        $existing = $this->redes->findByCnpj($data['cnpj']);

        if ($existing !== null && (int) $existing['id'] !== $id) {
            throw new ValidationException(['cnpj' => ['Já existe uma rede com este CNPJ.']]);
        }

        $this->redes->update($id, $data);
    }

    private function normalize(array $data): array
    {
        return [
            'razao_social' => trim((string) ($data['razao_social'] ?? '')),
            'nome_fantasia' => trim((string) ($data['nome_fantasia'] ?? '')),
            'cnpj' => preg_replace('/\D/', '', (string) ($data['cnpj'] ?? '')) ?? '',
            'email' => trim((string) ($data['email'] ?? '')),
            'telefone' => trim((string) ($data['telefone'] ?? '')),
            'cep' => trim((string) ($data['cep'] ?? '')),
            'logradouro' => trim((string) ($data['logradouro'] ?? '')),
            'numero' => trim((string) ($data['numero'] ?? '')),
            'complemento' => trim((string) ($data['complemento'] ?? '')),
            'bairro' => trim((string) ($data['bairro'] ?? '')),
            'cidade' => trim((string) ($data['cidade'] ?? '')),
            'estado' => strtoupper(trim((string) ($data['estado'] ?? ''))),
            'status' => strtoupper(trim((string) ($data['status'] ?? 'ATIVA'))),
            'plano_contratado' => trim((string) ($data['plano_contratado'] ?? '')),
            'plano_rede_id' => ($data['plano_rede_id'] ?? '') !== '' ? (int) $data['plano_rede_id'] : null,
        ];
    }

    private function validate(array $data): void
    {
        $validator = (new Validator($data))
            ->required('razao_social')
            ->maxLength('razao_social', 180)
            ->required('nome_fantasia')
            ->maxLength('nome_fantasia', 180)
            ->required('cnpj')
            ->cnpj('cnpj')
            ->email('email')
            ->maxLength('email', 180)
            ->maxLength('estado', 2)
            ->in('status', self::STATUSES);

        $errors = $validator->errors();
        $cep = preg_replace('/\D/', '', $data['cep']) ?? '';

        if ($data['cep'] !== '' && strlen($cep) !== 8) {
            $errors['cep'][] = 'Informe um CEP válido.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }
    }

    private function applyPlan(array $data): array
    {
        if ($data['plano_rede_id'] === null) {
            return $data;
        }

        $plano = $this->planosRede->findById((int) $data['plano_rede_id']);

        if ($plano === null) {
            throw new ValidationException(['plano_rede_id' => ['Plano de rede inválido.']]);
        }

        $data['plano_contratado'] = (string) $plano['nome'];

        return $data;
    }
}

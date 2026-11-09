<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ValidationException;
use App\Repositories\RedeRepository;
use App\Validators\Validator;

final class RedeService
{
    private const STATUSES = ['ATIVA', 'SUSPENSA', 'EM_IMPLANTACAO', 'CANCELADA'];

    public function __construct(private readonly RedeRepository $redes)
    {
    }

    public function create(array $data): int
    {
        $data = $this->normalize($data);
        $this->validate($data);

        if ($this->redes->findByCnpj($data['cnpj']) !== null) {
            throw new ValidationException(['cnpj' => ['Já existe uma rede com este CNPJ.']]);
        }

        return $this->redes->create($data);
    }

    public function update(int $id, array $data): void
    {
        $data = $this->normalize($data);
        $this->validate($data);

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
            ->in('status', self::STATUSES);

        if ($validator->fails()) {
            throw new ValidationException($validator->errors());
        }
    }
}

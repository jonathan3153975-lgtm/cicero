<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ValidationException;
use App\Repositories\RegiaoRepository;
use App\Validators\Validator;

final class RegiaoService
{
    private const STATUSES = ['ATIVO', 'INATIVO'];

    public function __construct(private readonly RegiaoRepository $regioes)
    {
    }

    public function create(int $redeId, array $data): int
    {
        $data = $this->normalize($data);
        $this->validate($data);

        if ($this->regioes->findByRedeAndName($redeId, $data['nome']) !== null) {
            throw new ValidationException(['nome' => ['Já existe uma região com este nome nesta rede.']]);
        }

        return $this->regioes->create($redeId, $data);
    }

    public function update(int $id, int $redeId, array $data): void
    {
        $data = $this->normalize($data);
        $this->validate($data);

        $existing = $this->regioes->findByRedeAndName($redeId, $data['nome']);

        if ($existing !== null && (int) $existing['id'] !== $id) {
            throw new ValidationException(['nome' => ['Já existe uma região com este nome nesta rede.']]);
        }

        $this->regioes->update($id, $data);
    }

    private function normalize(array $data): array
    {
        return [
            'nome' => trim((string) ($data['nome'] ?? '')),
            'status' => strtoupper(trim((string) ($data['status'] ?? 'ATIVO'))),
        ];
    }

    private function validate(array $data): void
    {
        $validator = (new Validator($data))
            ->required('nome')
            ->maxLength('nome', 120)
            ->in('status', self::STATUSES);

        if ($validator->fails()) {
            throw new ValidationException($validator->errors());
        }
    }
}

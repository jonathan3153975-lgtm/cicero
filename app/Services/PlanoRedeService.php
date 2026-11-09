<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ValidationException;
use App\Repositories\PlanoRedeRepository;
use App\Support\HtmlSanitizer;
use App\Validators\Validator;

final class PlanoRedeService
{
    private const STATUSES = ['ATIVO', 'INATIVO'];

    public function __construct(private readonly PlanoRedeRepository $planos)
    {
    }

    public function create(int $actorId, array $data): int
    {
        $data = $this->normalize($data);
        $this->validate($data);

        if ($this->planos->existsName($data['nome'])) {
            throw new ValidationException(['nome' => ['Já existe um plano de rede com este nome.']]);
        }

        $data['created_by'] = $actorId;
        $data['updated_by'] = $actorId;

        return $this->planos->create($data);
    }

    public function update(int $actorId, int $id, array $data): void
    {
        if ($this->planos->findById($id) === null) {
            throw new ValidationException(['plano' => ['Plano de rede inexistente.']]);
        }

        $data = $this->normalize($data);
        $this->validate($data);

        if ($this->planos->existsName($data['nome'], $id)) {
            throw new ValidationException(['nome' => ['Já existe um plano de rede com este nome.']]);
        }

        $data['updated_by'] = $actorId;
        $this->planos->update($id, $data);
    }

    private function normalize(array $data): array
    {
        return [
            'nome' => trim((string) ($data['nome'] ?? '')),
            'descricao' => HtmlSanitizer::richText((string) ($data['descricao'] ?? '')),
            'status' => strtoupper(trim((string) ($data['status'] ?? 'ATIVO'))),
        ];
    }

    private function validate(array $data): void
    {
        $validator = (new Validator($data))
            ->required('nome')
            ->maxLength('nome', 120)
            ->in('status', self::STATUSES)
            ->maxLength('descricao', 60000);

        if ($validator->fails()) {
            throw new ValidationException($validator->errors());
        }
    }
}

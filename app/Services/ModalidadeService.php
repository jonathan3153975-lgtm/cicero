<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;
use App\Repositories\ModalidadeRepository;
use App\Validators\Validator;

final class ModalidadeService
{
    private const STATUSES = ['ATIVO', 'INATIVO'];

    public function __construct(private readonly ModalidadeRepository $modalidades)
    {
    }

    public function create(?int $redeId, array $data): int
    {
        $data = $this->normalize($data);
        $this->validate($data);

        if ($this->modalidades->existsName($redeId, $data['nome'])) {
            throw new ValidationException(['nome' => ['Já existe uma modalidade com este nome.']]);
        }

        return $this->modalidades->create($redeId, $data);
    }

    public function update(
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        int $id,
        array $data
    ): void {
        $modalidade = $this->assertCanAccess($id, $redeId, $unidadeId, $regiaoId);
        $data = $this->normalize($data);
        $this->validate($data);
        $targetRedeId = $modalidade['rede_id'] !== null ? (int) $modalidade['rede_id'] : null;

        if ($this->modalidades->existsName($targetRedeId, $data['nome'], $id)) {
            throw new ValidationException(['nome' => ['Já existe uma modalidade com este nome.']]);
        }

        $this->modalidades->update($id, $data);
    }

    public function assertCanAccess(
        int $id,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): array {
        $modalidade = $this->modalidades->findById($id);

        if ($modalidade === null) {
            throw new ValidationException(['modalidade' => ['Modalidade inexistente.']]);
        }

        $isPlatform = $redeId === null && $unidadeId === null && $regiaoId === null;

        if ($isPlatform) {
            return $modalidade;
        }

        if ($modalidade['rede_id'] !== null && (int) $modalidade['rede_id'] === $redeId) {
            return $modalidade;
        }

        throw new ForbiddenException('Modalidade fora da sua rede.');
    }

    public function forScope(?int $redeId, bool $includeAll = false): array
    {
        return $this->modalidades->forScope($redeId, $includeAll);
    }

    private function normalize(array $data): array
    {
        return [
            'nome' => trim((string) ($data['nome'] ?? '')),
            'descricao' => trim((string) ($data['descricao'] ?? '')),
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

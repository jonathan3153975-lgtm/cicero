<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;
use App\Repositories\ModalidadeRepository;
use App\Repositories\PlanoRepository;
use App\Support\Database;
use App\Support\PublicId;
use App\Validators\Validator;
use PDO;

final class PlanoService
{
    private const TIPOS = ['MENSAL', 'ANUAL', 'PACOTE', 'AVULSO'];
    private const STATUSES = ['ATIVO', 'INATIVO'];

    public function __construct(
        private readonly PlanoRepository $planos,
        private readonly ModalidadeRepository $modalidades,
        private readonly Database $database
    ) {
    }

    public function create(int $actorId, ?int $redeId, array $data, array $modalidadeIds): int
    {
        $data = $this->normalize($data);
        $this->validate($data);
        $this->assertModalidades($redeId, $modalidadeIds);
        $data['public_id'] = PublicId::generate();
        $data['created_by'] = $actorId;
        $data['updated_by'] = $actorId;

        return $this->database->transaction(function (PDO $pdo) use ($redeId, $data, $modalidadeIds): int {
            $id = $this->planos->create($redeId, $data);
            $this->planos->syncModalidades($id, $modalidadeIds);

            return $id;
        });
    }

    public function update(
        int $actorId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        int $id,
        array $data,
        array $modalidadeIds
    ): void {
        $plano = $this->assertCanAccess($id, $redeId, $unidadeId, $regiaoId);
        $data = $this->normalize($data);
        $data['rede_id'] = $plano['rede_id'];
        $this->validate($data);
        $this->assertModalidades($plano['rede_id'] !== null ? (int) $plano['rede_id'] : null, $modalidadeIds);
        $data['updated_by'] = $actorId;

        $this->database->transaction(function () use ($id, $data, $modalidadeIds): void {
            $this->planos->update($id, $data);
            $this->planos->syncModalidades($id, $modalidadeIds);
        });
    }

    public function assertCanAccess(
        int $planoId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): array {
        $plano = $this->planos->findById($planoId);

        if ($plano === null) {
            throw new ValidationException(['plano' => ['Plano inexistente.']]);
        }

        if ($redeId === null && $unidadeId === null && $regiaoId === null) {
            return $plano;
        }

        if ($plano['rede_id'] === null || (int) $plano['rede_id'] !== $redeId) {
            throw new ForbiddenException('Plano fora da sua rede.');
        }

        return $plano;
    }

    public function allForScope(?int $redeId, bool $includeAll = false): array
    {
        return $this->planos->allForRede($redeId, $includeAll);
    }

    private function assertModalidades(?int $redeId, array $modalidadeIds): void
    {
        $allowed = array_column(
            $this->modalidades->allActiveForRede($redeId, $redeId === null),
            'id'
        );
        $allowed = array_map('intval', $allowed);

        foreach (array_unique($modalidadeIds) as $modalidadeId) {
            if (!in_array((int) $modalidadeId, $allowed, true)) {
                throw new ValidationException(['modalidades' => ['Modalidade inválida para esta rede.']]);
            }
        }
    }

    private function normalize(array $data): array
    {
        return [
            'nome' => trim((string) ($data['nome'] ?? '')),
            'descricao' => trim((string) ($data['descricao'] ?? '')),
            'tipo' => strtoupper(trim((string) ($data['tipo'] ?? 'MENSAL'))),
            'duracao_meses' => trim((string) ($data['duracao_meses'] ?? '')),
            'aulas_semana' => trim((string) ($data['aulas_semana'] ?? '')),
            'limite_modalidades' => trim((string) ($data['limite_modalidades'] ?? '')),
            'status' => strtoupper(trim((string) ($data['status'] ?? 'ATIVO'))),
        ];
    }

    private function validate(array $data): void
    {
        $validator = (new Validator($data))
            ->required('nome')
            ->maxLength('nome', 120)
            ->in('tipo', self::TIPOS)
            ->in('status', self::STATUSES);

        $errors = $validator->errors();

        foreach (['duracao_meses', 'aulas_semana', 'limite_modalidades'] as $field) {
            if ($data[$field] !== '' && (!ctype_digit($data[$field]) || (int) $data[$field] < 0)) {
                $errors[$field][] = 'Informe um número válido.';
            }
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories;

final class PlanoRepository extends Repository
{
    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, rede_id, public_id, nome, descricao, tipo, duracao_meses,'
            . ' aulas_semana, limite_modalidades, status'
            . ' FROM planos WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function allForRede(?int $redeId, bool $includeAll = false): array
    {
        $sql = 'SELECT p.id, p.rede_id, p.nome, p.tipo, p.duracao_meses, p.aulas_semana,'
            . ' p.limite_modalidades, p.status,'
            . ' (SELECT COUNT(*) FROM plano_modalidades pm WHERE pm.plano_id = p.id) AS modality_count,'
            . ' (SELECT COUNT(*) FROM plano_unidade_precos pp WHERE pp.plano_id = p.id) AS price_count'
            . ' FROM planos p';

        if ($includeAll) {
            $sql .= ' ORDER BY p.nome ASC';
            $statement = $this->pdo()->query($sql);

            return $statement->fetchAll();
        }

        $sql .= ' WHERE p.rede_id IS NULL OR p.rede_id = :rede_id ORDER BY p.nome ASC';
        $statement = $this->pdo()->prepare($sql);
        $statement->execute(['rede_id' => $redeId]);

        return $statement->fetchAll();
    }

    public function create(?int $redeId, array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO planos (rede_id, public_id, nome, descricao, tipo, duracao_meses,'
            . ' aulas_semana, limite_modalidades, status, created_by, updated_by)'
            . ' VALUES (:rede_id, :public_id, :nome, :descricao, :tipo, :duracao_meses,'
            . ' :aulas_semana, :limite_modalidades, :status, :created_by, :updated_by)'
        );
        $statement->execute($this->parameters($data, $redeId));

        return (int) $this->pdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $parameters = $this->parameters($data, $data['rede_id'] ?? null);
        unset($parameters['public_id'], $parameters['created_by']);
        $parameters['id'] = $id;

        $statement = $this->pdo()->prepare(
            'UPDATE planos SET rede_id = :rede_id, nome = :nome, descricao = :descricao,'
            . ' tipo = :tipo, duracao_meses = :duracao_meses, aulas_semana = :aulas_semana,'
            . ' limite_modalidades = :limite_modalidades, status = :status, updated_by = :updated_by'
            . ' WHERE id = :id'
        );
        $statement->execute($parameters);
    }

    public function syncModalidades(int $planoId, array $modalidadeIds): void
    {
        $delete = $this->pdo()->prepare('DELETE FROM plano_modalidades WHERE plano_id = :plano_id');
        $delete->execute(['plano_id' => $planoId]);

        if ($modalidadeIds === []) {
            return;
        }

        $insert = $this->pdo()->prepare(
            'INSERT INTO plano_modalidades (plano_id, modalidade_id) VALUES (:plano_id, :modalidade_id)'
        );

        foreach (array_unique($modalidadeIds) as $modalidadeId) {
            $insert->execute(['plano_id' => $planoId, 'modalidade_id' => (int) $modalidadeId]);
        }
    }

    public function modalidadeIdsForPlano(int $planoId): array
    {
        $statement = $this->pdo()->prepare(
            'SELECT modalidade_id FROM plano_modalidades WHERE plano_id = :plano_id'
        );
        $statement->execute(['plano_id' => $planoId]);

        return array_map('intval', $statement->fetchAll(\PDO::FETCH_COLUMN));
    }

    private function parameters(array $data, ?int $redeId): array
    {
        return [
            'rede_id' => $redeId,
            'public_id' => $data['public_id'] ?? '',
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?: null,
            'tipo' => $data['tipo'],
            'duracao_meses' => $data['duracao_meses'] !== '' ? (int) $data['duracao_meses'] : null,
            'aulas_semana' => $data['aulas_semana'] !== '' ? (int) $data['aulas_semana'] : null,
            'limite_modalidades' => $data['limite_modalidades'] !== '' ? (int) $data['limite_modalidades'] : null,
            'status' => $data['status'] ?? 'ATIVO',
            'created_by' => $data['created_by'] ?? null,
            'updated_by' => $data['updated_by'] ?? null,
        ];
    }
}

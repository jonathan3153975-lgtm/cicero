<?php

declare(strict_types=1);

namespace App\Repositories;

final class PlanoPrecoRepository extends Repository
{
    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT pp.id, pp.plano_id, pp.rede_id, pp.unidade_id, pp.vigencia_inicio, pp.vigencia_fim,'
            . ' pp.valor_matricula, pp.valor_mensal, pp.valor_adesao, pp.taxa_administrativa, pp.status,'
            . ' p.nome AS plano_nome, p.tipo AS plano_tipo, p.duracao_meses,'
            . ' u.nome AS unidade_nome, r.nome_fantasia AS rede_nome'
            . ' FROM plano_unidade_precos pp'
            . ' INNER JOIN planos p ON p.id = pp.plano_id'
            . ' INNER JOIN unidades u ON u.id = pp.unidade_id'
            . ' INNER JOIN redes r ON r.id = pp.rede_id'
            . ' WHERE pp.id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function forPlanoForScope(
        int $planoId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): array {
        $sql = 'SELECT pp.id, pp.plano_id, pp.rede_id, pp.unidade_id, pp.vigencia_inicio,'
            . ' pp.vigencia_fim, pp.valor_matricula, pp.valor_mensal, pp.valor_adesao,'
            . ' pp.taxa_administrativa, pp.status, u.nome AS unidade_nome'
            . ' FROM plano_unidade_precos pp'
            . ' INNER JOIN unidades u ON u.id = pp.unidade_id'
            . ' WHERE pp.plano_id = :plano_id';
        $parameters = ['plano_id' => $planoId];

        if ($unidadeId !== null) {
            $sql .= ' AND pp.unidade_id = :unidade_id';
            $parameters['unidade_id'] = $unidadeId;
        } elseif ($regiaoId !== null) {
            $sql .= ' AND pp.unidade_id IN (SELECT unidade_id FROM regiao_unidade WHERE regiao_id = :regiao_id)';
            $parameters['regiao_id'] = $regiaoId;
        } elseif ($redeId !== null) {
            $sql .= ' AND pp.rede_id = :rede_id';
            $parameters['rede_id'] = $redeId;
        }

        $sql .= ' ORDER BY u.nome ASC, pp.vigencia_inicio DESC';
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function activeForScope(?int $redeId, ?int $unidadeId, ?int $regiaoId): array
    {
        $sql = 'SELECT pp.id, pp.plano_id, pp.rede_id, pp.unidade_id, pp.vigencia_inicio,'
            . ' pp.vigencia_fim, pp.valor_matricula, pp.valor_mensal, pp.valor_adesao,'
            . ' pp.taxa_administrativa, pp.status, p.nome AS plano_nome, p.tipo AS plano_tipo,'
            . ' p.duracao_meses, u.nome AS unidade_nome'
            . ' FROM plano_unidade_precos pp'
            . ' INNER JOIN planos p ON p.id = pp.plano_id'
            . ' INNER JOIN unidades u ON u.id = pp.unidade_id'
            . ' WHERE pp.status = \'ATIVO\' AND p.status = \'ATIVO\''
            . ' AND (pp.vigencia_fim IS NULL OR pp.vigencia_fim >= CURDATE())';
        $parameters = [];

        if ($unidadeId !== null) {
            $sql .= ' AND pp.unidade_id = :unidade_id';
            $parameters['unidade_id'] = $unidadeId;
        } elseif ($regiaoId !== null) {
            $sql .= ' AND pp.unidade_id IN (SELECT unidade_id FROM regiao_unidade WHERE regiao_id = :regiao_id)';
            $parameters['regiao_id'] = $regiaoId;
        } elseif ($redeId !== null) {
            $sql .= ' AND pp.rede_id = :rede_id';
            $parameters['rede_id'] = $redeId;
        }

        $sql .= ' ORDER BY p.nome ASC, u.nome ASC';
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function findByPlanoUnidadeVigencia(int $planoId, int $unidadeId, string $vigenciaInicio): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id FROM plano_unidade_precos'
            . ' WHERE plano_id = :plano_id AND unidade_id = :unidade_id'
            . ' AND vigencia_inicio = :vigencia_inicio LIMIT 1'
        );
        $statement->execute([
            'plano_id' => $planoId,
            'unidade_id' => $unidadeId,
            'vigencia_inicio' => $vigenciaInicio,
        ]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function create(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO plano_unidade_precos (plano_id, rede_id, unidade_id, vigencia_inicio,'
            . ' vigencia_fim, valor_matricula, valor_mensal, valor_adesao, taxa_administrativa,'
            . ' status, created_by, updated_by)'
            . ' VALUES (:plano_id, :rede_id, :unidade_id, :vigencia_inicio, :vigencia_fim,'
            . ' :valor_matricula, :valor_mensal, :valor_adesao, :taxa_administrativa,'
            . ' :status, :created_by, :updated_by)'
        );
        $statement->execute([
            'plano_id' => (int) $data['plano_id'],
            'rede_id' => (int) $data['rede_id'],
            'unidade_id' => (int) $data['unidade_id'],
            'vigencia_inicio' => $data['vigencia_inicio'],
            'vigencia_fim' => $data['vigencia_fim'] ?: null,
            'valor_matricula' => $data['valor_matricula'],
            'valor_mensal' => $data['valor_mensal'],
            'valor_adesao' => $data['valor_adesao'] !== '' ? $data['valor_adesao'] : null,
            'taxa_administrativa' => $data['taxa_administrativa'] !== '' ? $data['taxa_administrativa'] : null,
            'status' => $data['status'] ?? 'ATIVO',
            'created_by' => $data['created_by'] ?? null,
            'updated_by' => $data['updated_by'] ?? null,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }
}

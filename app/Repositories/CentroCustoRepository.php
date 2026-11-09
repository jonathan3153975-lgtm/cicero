<?php

declare(strict_types=1);

namespace App\Repositories;

final class CentroCustoRepository extends Repository
{
    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, rede_id, unidade_id, codigo, nome, status'
            . ' FROM centros_custo WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function forScope(?int $redeId, ?int $unidadeId, ?int $regiaoId): array
    {
        $sql = 'SELECT cc.id, cc.rede_id, cc.unidade_id, cc.codigo, cc.nome, cc.status,'
            . ' u.nome AS unidade_nome'
            . ' FROM centros_custo cc'
            . ' INNER JOIN unidades u ON u.id = cc.unidade_id';
        $where = ['cc.status = \'ATIVO\''];
        $parameters = [];

        if ($unidadeId !== null) {
            $where[] = 'cc.unidade_id = :unidade_id';
            $parameters['unidade_id'] = $unidadeId;
        } elseif ($regiaoId !== null) {
            $where[] = 'cc.unidade_id IN (SELECT unidade_id FROM regiao_unidade WHERE regiao_id = :regiao_id)';
            $parameters['regiao_id'] = $regiaoId;
        } elseif ($redeId !== null) {
            $where[] = 'cc.rede_id = :rede_id';
            $parameters['rede_id'] = $redeId;
        }

        $sql .= ' WHERE ' . implode(' AND ', $where) . ' ORDER BY u.nome ASC, cc.nome ASC';
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }
}

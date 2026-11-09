<?php

declare(strict_types=1);

namespace App\Repositories;

final class DispositivoAcessoRepository extends Repository
{
    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT d.id, d.rede_id, d.unidade_id, d.nome, d.tipo, d.identificador,'
            . ' d.direcao, d.status, d.ultima_comunicacao_em, u.nome AS unidade_nome'
            . ' FROM dispositivos_acesso d'
            . ' INNER JOIN unidades u ON u.id = d.unidade_id'
            . ' WHERE d.id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function forScope(?int $redeId, ?int $unidadeId, ?int $regiaoId): array
    {
        $sql = 'SELECT d.id, d.rede_id, d.unidade_id, d.nome, d.tipo, d.identificador,'
            . ' d.direcao, d.status, d.ultima_comunicacao_em, u.nome AS unidade_nome'
            . ' FROM dispositivos_acesso d'
            . ' INNER JOIN unidades u ON u.id = d.unidade_id';
        $where = [];
        $parameters = [];

        if ($unidadeId !== null) {
            $where[] = 'd.unidade_id = :unidade_id';
            $parameters['unidade_id'] = $unidadeId;
        } elseif ($regiaoId !== null) {
            $where[] = 'd.unidade_id IN (SELECT unidade_id FROM regiao_unidade WHERE regiao_id = :regiao_id)';
            $parameters['regiao_id'] = $regiaoId;
        } elseif ($redeId !== null) {
            $where[] = 'd.rede_id = :rede_id';
            $parameters['rede_id'] = $redeId;
        }

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY u.nome ASC, d.nome ASC';
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function create(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO dispositivos_acesso (rede_id, unidade_id, nome, tipo, identificador,'
            . ' direcao, status)'
            . ' VALUES (:rede_id, :unidade_id, :nome, :tipo, :identificador, :direcao, :status)'
        );
        $statement->execute([
            'rede_id' => (int) $data['rede_id'],
            'unidade_id' => (int) $data['unidade_id'],
            'nome' => $data['nome'],
            'tipo' => $data['tipo'] ?? 'CATRACA',
            'identificador' => $data['identificador'],
            'direcao' => $data['direcao'] ?? 'AMBOS',
            'status' => $data['status'] ?? 'ATIVO',
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $statement = $this->pdo()->prepare(
            'UPDATE dispositivos_acesso SET nome = :nome, tipo = :tipo, identificador = :identificador,'
            . ' direcao = :direcao, status = :status WHERE id = :id'
        );
        $statement->execute([
            'nome' => $data['nome'],
            'tipo' => $data['tipo'],
            'identificador' => $data['identificador'],
            'direcao' => $data['direcao'],
            'status' => $data['status'],
            'id' => $id,
        ]);
    }
}

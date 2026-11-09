<?php

declare(strict_types=1);

namespace App\Repositories;

final class RegiaoRepository extends Repository
{
    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, rede_id, nome, status FROM regioes WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function forRede(int $redeId): array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, rede_id, nome, status FROM regioes WHERE rede_id = :rede_id ORDER BY nome ASC'
        );
        $statement->execute(['rede_id' => $redeId]);

        return $statement->fetchAll();
    }

    public function forUnidade(int $unidadeId): array
    {
        $statement = $this->pdo()->prepare(
            'SELECT r.id, r.rede_id, r.nome, r.status'
            . ' FROM regioes r INNER JOIN regiao_unidade ru ON ru.regiao_id = r.id'
            . ' WHERE ru.unidade_id = :unidade_id ORDER BY r.nome ASC'
        );
        $statement->execute(['unidade_id' => $unidadeId]);

        return $statement->fetchAll();
    }

    public function linkUnidade(int $regiaoId, int $unidadeId): void
    {
        $statement = $this->pdo()->prepare(
            'INSERT IGNORE INTO regiao_unidade (regiao_id, unidade_id) VALUES (:regiao_id, :unidade_id)'
        );
        $statement->execute(['regiao_id' => $regiaoId, 'unidade_id' => $unidadeId]);
    }

    public function syncUnidade(int $unidadeId, ?int $regiaoId): void
    {
        $delete = $this->pdo()->prepare('DELETE FROM regiao_unidade WHERE unidade_id = :unidade_id');
        $delete->execute(['unidade_id' => $unidadeId]);

        if ($regiaoId === null) {
            return;
        }

        $this->linkUnidade($regiaoId, $unidadeId);
    }

    public function all(): array
    {
        $statement = $this->pdo()->query(
            'SELECT r.id, r.rede_id, r.nome, r.status, rd.nome_fantasia AS rede_nome'
            . ' FROM regioes r INNER JOIN redes rd ON rd.id = r.rede_id'
            . ' ORDER BY rd.nome_fantasia ASC, r.nome ASC'
        );

        return $statement->fetchAll();
    }

    public function findByRedeAndName(int $redeId, string $nome): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, rede_id, nome, status FROM regioes WHERE rede_id = :rede_id AND nome = :nome LIMIT 1'
        );
        $statement->execute(['rede_id' => $redeId, 'nome' => $nome]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function create(int $redeId, array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO regioes (rede_id, nome, status) VALUES (:rede_id, :nome, :status)'
        );
        $statement->execute([
            'rede_id' => $redeId,
            'nome' => $data['nome'],
            'status' => $data['status'] ?? 'ATIVO',
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $statement = $this->pdo()->prepare(
            'UPDATE regioes SET nome = :nome, status = :status WHERE id = :id'
        );
        $statement->execute([
            'nome' => $data['nome'],
            'status' => $data['status'] ?? 'ATIVO',
            'id' => $id,
        ]);
    }
}

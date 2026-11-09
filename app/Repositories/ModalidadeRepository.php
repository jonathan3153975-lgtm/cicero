<?php

declare(strict_types=1);

namespace App\Repositories;

final class ModalidadeRepository extends Repository
{
    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, rede_id, nome, descricao, status FROM modalidades WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function allActiveForRede(?int $redeId, bool $includeAll = false): array
    {
        if ($includeAll) {
            $statement = $this->pdo()->query(
                'SELECT id, rede_id, nome FROM modalidades WHERE status = \'ATIVO\' ORDER BY nome ASC'
            );

            return $statement->fetchAll();
        }

        $statement = $this->pdo()->prepare(
            'SELECT id, rede_id, nome FROM modalidades'
            . ' WHERE status = \'ATIVO\' AND (rede_id IS NULL OR rede_id = :rede_id) ORDER BY nome ASC'
        );
        $statement->execute(['rede_id' => $redeId]);

        return $statement->fetchAll();
    }
}

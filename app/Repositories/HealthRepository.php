<?php

declare(strict_types=1);

namespace App\Repositories;

final class HealthRepository extends Repository
{
    public function record(?string $note = null): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO health_checks (checked_at, note) VALUES (:checked_at, :note)'
        );

        $statement->execute([
            'checked_at' => date('Y-m-d H:i:s'),
            'note' => $note,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function count(): int
    {
        $statement = $this->pdo()->query('SELECT COUNT(*) FROM health_checks');

        return (int) $statement->fetchColumn();
    }

    public function latest(): ?array
    {
        $statement = $this->pdo()->query(
            'SELECT id, checked_at, note FROM health_checks ORDER BY id DESC LIMIT 1'
        );

        $row = $statement->fetch();

        return $row === false ? null : $row;
    }
}

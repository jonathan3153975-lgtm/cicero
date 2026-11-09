<?php

declare(strict_types=1);

namespace App\Repositories;

final class EstornoRepository extends Repository
{
    public function create(
        int $pagamentoId,
        float $valor,
        ?string $motivo,
        string $data,
        ?int $usuarioId
    ): int {
        $statement = $this->pdo()->prepare(
            'INSERT INTO estornos (pagamento_id, valor, motivo, data, usuario_id)'
            . ' VALUES (:pagamento_id, :valor, :motivo, :data, :usuario_id)'
        );
        $statement->execute([
            'pagamento_id' => $pagamentoId,
            'valor' => $valor,
            'motivo' => $motivo,
            'data' => $data,
            'usuario_id' => $usuarioId,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }
}

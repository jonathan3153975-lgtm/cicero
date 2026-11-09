<?php

declare(strict_types=1);

namespace App\Repositories;

final class MovimentoCaixaRepository extends Repository
{
    public function create(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO movimentos_caixa (rede_id, unidade_id, conta_bancaria_id, tipo, origem,'
            . ' valor, data, descricao, referencia_id, referencia_tipo)'
            . ' VALUES (:rede_id, :unidade_id, :conta_bancaria_id, :tipo, :origem,'
            . ' :valor, :data, :descricao, :referencia_id, :referencia_tipo)'
        );
        $statement->execute([
            'rede_id' => (int) $data['rede_id'],
            'unidade_id' => (int) $data['unidade_id'],
            'conta_bancaria_id' => (int) $data['conta_bancaria_id'],
            'tipo' => $data['tipo'],
            'origem' => $data['origem'],
            'valor' => $data['valor'],
            'data' => $data['data'],
            'descricao' => $data['descricao'] ?? null,
            'referencia_id' => $data['referencia_id'] ?? null,
            'referencia_tipo' => $data['referencia_tipo'] ?? null,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function forContaPeriodo(int $contaId, string $inicio, string $fim): array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, tipo, origem, valor, data, descricao FROM movimentos_caixa'
            . ' WHERE conta_bancaria_id = :conta_id AND data BETWEEN :inicio AND :fim'
            . ' ORDER BY data ASC, id ASC'
        );
        $statement->execute(['conta_id' => $contaId, 'inicio' => $inicio, 'fim' => $fim]);

        return $statement->fetchAll();
    }
}

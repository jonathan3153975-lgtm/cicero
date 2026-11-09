<?php

declare(strict_types=1);

namespace App\Repositories;

final class EventoAcessoRepository extends Repository
{
    public function forScope(
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        int $limit = 200
    ): array {
        $sql = 'SELECT e.id, e.rede_id, e.unidade_id, e.dispositivo_id, e.pessoa_id, e.aluno_id,'
            . ' e.tipo, e.ocorrido_em, e.autorizado, e.motivo_negacao,'
            . ' p.nome AS pessoa_nome, d.nome AS dispositivo_nome, u.nome AS unidade_nome'
            . ' FROM eventos_acesso e'
            . ' INNER JOIN pessoas p ON p.id = e.pessoa_id'
            . ' INNER JOIN dispositivos_acesso d ON d.id = e.dispositivo_id'
            . ' INNER JOIN unidades u ON u.id = e.unidade_id';
        $where = [];
        $parameters = [];

        if ($unidadeId !== null) {
            $where[] = 'e.unidade_id = :unidade_id';
            $parameters['unidade_id'] = $unidadeId;
        } elseif ($regiaoId !== null) {
            $where[] = 'e.unidade_id IN (SELECT unidade_id FROM regiao_unidade WHERE regiao_id = :regiao_id)';
            $parameters['regiao_id'] = $regiaoId;
        } elseif ($redeId !== null) {
            $where[] = 'e.rede_id = :rede_id';
            $parameters['rede_id'] = $redeId;
        }

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $limit = max(1, min(500, $limit));
        $sql .= ' ORDER BY e.ocorrido_em DESC, e.id DESC LIMIT ' . $limit;
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function create(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO eventos_acesso (dispositivo_id, rede_id, unidade_id, pessoa_id, aluno_id,'
            . ' tipo, ocorrido_em, autorizado, motivo_negacao, payload_json)'
            . ' VALUES (:dispositivo_id, :rede_id, :unidade_id, :pessoa_id, :aluno_id,'
            . ' :tipo, :ocorrido_em, :autorizado, :motivo_negacao, :payload_json)'
        );
        $statement->execute([
            'dispositivo_id' => (int) $data['dispositivo_id'],
            'rede_id' => (int) $data['rede_id'],
            'unidade_id' => (int) $data['unidade_id'],
            'pessoa_id' => (int) $data['pessoa_id'],
            'aluno_id' => $data['aluno_id'] !== null ? (int) $data['aluno_id'] : null,
            'tipo' => $data['tipo'],
            'ocorrido_em' => $data['ocorrido_em'],
            'autorizado' => !empty($data['autorizado']) ? 1 : 0,
            'motivo_negacao' => $data['motivo_negacao'] ?: null,
            'payload_json' => isset($data['payload_json']) && $data['payload_json'] !== ''
                ? json_encode($data['payload_json'], JSON_UNESCAPED_UNICODE)
                : null,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories;

final class AuditoriaRepository extends Repository
{
    public function latest(int $limit = 100): array
    {
        $limit = max(1, min(500, $limit));

        $statement = $this->pdo()->query(
            'SELECT a.id, a.acao, a.entidade, a.entidade_id, a.ip, a.created_at, a.rede_id, a.unidade_id,'
            . ' u.nome AS usuario_nome, u.email AS usuario_email,'
            . ' r.nome_fantasia AS rede_nome, un.nome AS unidade_nome'
            . ' FROM auditoria a'
            . ' LEFT JOIN usuarios u ON u.id = a.usuario_id'
            . ' LEFT JOIN redes r ON r.id = a.rede_id'
            . ' LEFT JOIN unidades un ON un.id = a.unidade_id'
            . ' ORDER BY a.id DESC LIMIT ' . $limit
        );

        return $statement->fetchAll();
    }

    public function latestForScope(
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId = null,
        int $limit = 200
    ): array {
        $limit = max(1, min(500, $limit));

        $sql = 'SELECT a.id, a.acao, a.entidade, a.entidade_id, a.ip, a.created_at, a.rede_id, a.unidade_id,'
            . ' u.nome AS usuario_nome, u.email AS usuario_email,'
            . ' r.nome_fantasia AS rede_nome, un.nome AS unidade_nome'
            . ' FROM auditoria a'
            . ' LEFT JOIN usuarios u ON u.id = a.usuario_id'
            . ' LEFT JOIN redes r ON r.id = a.rede_id'
            . ' LEFT JOIN unidades un ON un.id = a.unidade_id';

        $parameters = [];

        if ($regiaoId !== null) {
            $sql .= ' WHERE a.unidade_id IN (SELECT unidade_id FROM regiao_unidade WHERE regiao_id = :regiao_id)';
            $parameters = ['regiao_id' => $regiaoId];
        } elseif ($unidadeId !== null) {
            $sql .= ' WHERE a.unidade_id = :unidade_id';
            $parameters = ['unidade_id' => $unidadeId];
        } elseif ($redeId !== null) {
            $sql .= ' WHERE a.rede_id = :rede_id';
            $parameters = ['rede_id' => $redeId];
        }

        $sql .= ' ORDER BY a.id DESC LIMIT ' . $limit;

        $statement = $this->pdo()->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function record(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO auditoria (usuario_id, rede_id, unidade_id, acao, entidade, entidade_id,'
            . ' dados_anteriores, dados_posteriores, ip, user_agent)'
            . ' VALUES (:usuario_id, :rede_id, :unidade_id, :acao, :entidade, :entidade_id,'
            . ' :dados_anteriores, :dados_posteriores, :ip, :user_agent)'
        );
        $statement->execute([
            'usuario_id' => $data['usuario_id'] ?? null,
            'rede_id' => $data['rede_id'] ?? null,
            'unidade_id' => $data['unidade_id'] ?? null,
            'acao' => $data['acao'],
            'entidade' => $data['entidade'],
            'entidade_id' => $data['entidade_id'] ?? null,
            'dados_anteriores' => $this->encode($data['dados_anteriores'] ?? null),
            'dados_posteriores' => $this->encode($data['dados_posteriores'] ?? null),
            'ip' => $data['ip'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    private function encode(?array $data): ?string
    {
        if ($data === null) {
            return null;
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

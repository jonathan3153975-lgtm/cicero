<?php

declare(strict_types=1);

namespace App\Repositories;

final class ConsentimentoRepository extends Repository
{
    public function record(
        int $pessoaId,
        string $tipo,
        ?string $finalidade = null,
        ?string $versaoTermo = null,
        ?string $ip = null,
        ?string $userAgent = null,
        string $status = 'ATIVO'
    ): int {
        $statement = $this->pdo()->prepare(
            'INSERT INTO consentimentos (pessoa_id, tipo, finalidade, versao_termo, concedido_em,'
            . ' ip, user_agent, status)'
            . ' VALUES (:pessoa_id, :tipo, :finalidade, :versao_termo, :concedido_em,'
            . ' :ip, :user_agent, :status)'
        );
        $statement->execute([
            'pessoa_id' => $pessoaId,
            'tipo' => $tipo,
            'finalidade' => $finalidade,
            'versao_termo' => $versaoTermo,
            'concedido_em' => $status === 'ATIVO' ? date('Y-m-d H:i:s') : null,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'status' => $status,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function forPessoa(int $pessoaId): array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, tipo, finalidade, versao_termo, concedido_em, revogado_em, status'
            . ' FROM consentimentos WHERE pessoa_id = :pessoa_id ORDER BY id DESC'
        );
        $statement->execute(['pessoa_id' => $pessoaId]);

        return $statement->fetchAll();
    }
}

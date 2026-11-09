<?php

declare(strict_types=1);

namespace App\Repositories;

final class PessoaContatoRepository extends Repository
{
    public function syncPrimary(int $pessoaId, ?string $email, ?string $telefone): void
    {
        $delete = $this->pdo()->prepare(
            'DELETE FROM pessoa_contatos WHERE pessoa_id = :pessoa_id AND principal = 1'
            . ' AND tipo IN (\'EMAIL\', \'TELEFONE\')'
        );
        $delete->execute(['pessoa_id' => $pessoaId]);

        $insert = $this->pdo()->prepare(
            'INSERT INTO pessoa_contatos (pessoa_id, tipo, valor, principal, status)'
            . ' VALUES (:pessoa_id, :tipo, :valor, 1, \'ATIVO\')'
        );

        if ($email !== null && $email !== '') {
            $insert->execute(['pessoa_id' => $pessoaId, 'tipo' => 'EMAIL', 'valor' => $email]);
        }

        if ($telefone !== null && $telefone !== '') {
            $insert->execute(['pessoa_id' => $pessoaId, 'tipo' => 'TELEFONE', 'valor' => $telefone]);
        }
    }

    public function forPessoa(int $pessoaId): array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, tipo, valor, principal, verificado_em, status'
            . ' FROM pessoa_contatos WHERE pessoa_id = :pessoa_id ORDER BY principal DESC, id ASC'
        );
        $statement->execute(['pessoa_id' => $pessoaId]);

        return $statement->fetchAll();
    }
}

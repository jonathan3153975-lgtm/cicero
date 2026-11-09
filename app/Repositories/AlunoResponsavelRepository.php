<?php

declare(strict_types=1);

namespace App\Repositories;

final class AlunoResponsavelRepository extends Repository
{
    public function forAluno(int $alunoId): array
    {
        $statement = $this->pdo()->prepare(
            'SELECT ar.id, ar.aluno_id, ar.responsavel_pessoa_id, ar.parentesco,'
            . ' ar.responsavel_financeiro, ar.autorizado_buscar, ar.status,'
            . ' p.nome, p.cpf, p.email_principal, p.telefone_principal'
            . ' FROM aluno_responsaveis ar'
            . ' INNER JOIN pessoas p ON p.id = ar.responsavel_pessoa_id'
            . ' WHERE ar.aluno_id = :aluno_id ORDER BY ar.id ASC'
        );
        $statement->execute(['aluno_id' => $alunoId]);

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, aluno_id, responsavel_pessoa_id, parentesco, responsavel_financeiro,'
            . ' autorizado_buscar, status FROM aluno_responsaveis WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function add(
        int $alunoId,
        int $responsavelPessoaId,
        ?string $parentesco,
        bool $responsavelFinanceiro,
        bool $autorizadoBuscar
    ): int {
        $statement = $this->pdo()->prepare(
            'INSERT INTO aluno_responsaveis (aluno_id, responsavel_pessoa_id, parentesco,'
            . ' responsavel_financeiro, autorizado_buscar, status)'
            . ' VALUES (:aluno_id, :responsavel_pessoa_id, :parentesco, :responsavel_financeiro,'
            . ' :autorizado_buscar, \'ATIVO\')'
            . ' ON DUPLICATE KEY UPDATE parentesco = VALUES(parentesco),'
            . ' responsavel_financeiro = VALUES(responsavel_financeiro),'
            . ' autorizado_buscar = VALUES(autorizado_buscar), status = \'ATIVO\','
            . ' id = LAST_INSERT_ID(id)'
        );
        $statement->execute([
            'aluno_id' => $alunoId,
            'responsavel_pessoa_id' => $responsavelPessoaId,
            'parentesco' => $parentesco,
            'responsavel_financeiro' => $responsavelFinanceiro ? 1 : 0,
            'autorizado_buscar' => $autorizadoBuscar ? 1 : 0,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function deactivate(int $id): void
    {
        $statement = $this->pdo()->prepare(
            'UPDATE aluno_responsaveis SET status = \'INATIVO\' WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
    }
}

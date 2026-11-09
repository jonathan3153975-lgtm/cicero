<?php

declare(strict_types=1);

namespace App\Repositories;

final class PessoaEnderecoRepository extends Repository
{
    public function syncPrimary(int $pessoaId, array $data): void
    {
        $fields = ['cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'estado'];
        $hasAddress = false;

        foreach ($fields as $field) {
            if (($data[$field] ?? '') !== '') {
                $hasAddress = true;
                break;
            }
        }

        $delete = $this->pdo()->prepare(
            'DELETE FROM pessoa_enderecos WHERE pessoa_id = :pessoa_id AND principal = 1'
        );
        $delete->execute(['pessoa_id' => $pessoaId]);

        if (!$hasAddress) {
            return;
        }

        $statement = $this->pdo()->prepare(
            'INSERT INTO pessoa_enderecos (pessoa_id, tipo, cep, logradouro, numero, complemento,'
            . ' bairro, cidade, estado, pais, principal, status)'
            . ' VALUES (:pessoa_id, :tipo, :cep, :logradouro, :numero, :complemento,'
            . ' :bairro, :cidade, :estado, \'Brasil\', 1, \'ATIVO\')'
        );
        $statement->execute([
            'pessoa_id' => $pessoaId,
            'tipo' => $data['tipo_endereco'] ?? 'RESIDENCIAL',
            'cep' => $data['cep'] ?: null,
            'logradouro' => $data['logradouro'] ?: null,
            'numero' => $data['numero'] ?: null,
            'complemento' => $data['complemento'] ?: null,
            'bairro' => $data['bairro'] ?: null,
            'cidade' => $data['cidade'] ?: null,
            'estado' => $data['estado'] ?: null,
        ]);
    }

    public function forPessoa(int $pessoaId): array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, tipo, cep, logradouro, numero, complemento, bairro, cidade, estado,'
            . ' pais, principal, status'
            . ' FROM pessoa_enderecos WHERE pessoa_id = :pessoa_id ORDER BY principal DESC, id ASC'
        );
        $statement->execute(['pessoa_id' => $pessoaId]);

        return $statement->fetchAll();
    }
}

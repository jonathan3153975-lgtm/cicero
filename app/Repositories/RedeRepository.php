<?php

declare(strict_types=1);

namespace App\Repositories;

final class RedeRepository extends Repository
{
    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, razao_social, nome_fantasia, cnpj, email, telefone, cep, logradouro, numero,'
            . ' complemento, bairro, cidade, estado, status, plano_contratado'
            . ' FROM redes WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function findByCnpj(string $cnpj): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, razao_social, nome_fantasia, cnpj, status FROM redes WHERE cnpj = :cnpj LIMIT 1'
        );
        $statement->execute(['cnpj' => $cnpj]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function allActive(): array
    {
        $statement = $this->pdo()->query(
            'SELECT id, nome_fantasia, status FROM redes WHERE status = \'ATIVA\' ORDER BY nome_fantasia ASC'
        );

        return $statement->fetchAll();
    }

    public function all(): array
    {
        $statement = $this->pdo()->query(
            'SELECT id, razao_social, nome_fantasia, cnpj, email, telefone, cidade, estado, status,'
            . ' plano_contratado FROM redes ORDER BY nome_fantasia ASC'
        );

        return $statement->fetchAll();
    }

    public function create(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO redes (razao_social, nome_fantasia, cnpj, email, telefone, cep, logradouro,'
            . ' numero, complemento, bairro, cidade, estado, status, plano_contratado)'
            . ' VALUES (:razao_social, :nome_fantasia, :cnpj, :email, :telefone, :cep, :logradouro,'
            . ' :numero, :complemento, :bairro, :cidade, :estado, :status, :plano_contratado)'
        );
        $statement->execute($this->parameters($data));

        return (int) $this->pdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $parameters = $this->parameters($data);
        $parameters['id'] = $id;

        $statement = $this->pdo()->prepare(
            'UPDATE redes SET razao_social = :razao_social, nome_fantasia = :nome_fantasia,'
            . ' cnpj = :cnpj, email = :email, telefone = :telefone, cep = :cep,'
            . ' logradouro = :logradouro, numero = :numero, complemento = :complemento,'
            . ' bairro = :bairro, cidade = :cidade, estado = :estado, status = :status,'
            . ' plano_contratado = :plano_contratado WHERE id = :id'
        );
        $statement->execute($parameters);
    }

    private function parameters(array $data): array
    {
        return [
            'razao_social' => $data['razao_social'],
            'nome_fantasia' => $data['nome_fantasia'],
            'cnpj' => $data['cnpj'],
            'email' => $data['email'] ?? null,
            'telefone' => $data['telefone'] ?? null,
            'cep' => $data['cep'] ?? null,
            'logradouro' => $data['logradouro'] ?? null,
            'numero' => $data['numero'] ?? null,
            'complemento' => $data['complemento'] ?? null,
            'bairro' => $data['bairro'] ?? null,
            'cidade' => $data['cidade'] ?? null,
            'estado' => $data['estado'] ?? null,
            'status' => $data['status'] ?? 'ATIVA',
            'plano_contratado' => $data['plano_contratado'] ?? null,
        ];
    }
}

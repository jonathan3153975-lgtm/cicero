<?php

declare(strict_types=1);

namespace App\Repositories;

final class PessoaRepository extends Repository
{
    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, rede_id, public_id, tipo, nome, nome_social, cpf, cnpj, data_nascimento,'
            . ' sexo, estado_civil, email_principal, telefone_principal, foto_arquivo_id, status,'
            . ' observacoes, created_by, updated_by, created_at, updated_at'
            . ' FROM pessoas WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function findByCpf(string $cpf): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, nome, cpf FROM pessoas WHERE cpf = :cpf LIMIT 1'
        );
        $statement->execute(['cpf' => $cpf]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function findByCnpj(string $cnpj): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, nome, cnpj FROM pessoas WHERE cnpj = :cnpj LIMIT 1'
        );
        $statement->execute(['cnpj' => $cnpj]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function create(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO pessoas (rede_id, public_id, tipo, nome, nome_social, cpf, cnpj,'
            . ' data_nascimento, sexo, estado_civil, email_principal, telefone_principal, status,'
            . ' observacoes, created_by, updated_by)'
            . ' VALUES (:rede_id, :public_id, :tipo, :nome, :nome_social, :cpf, :cnpj,'
            . ' :data_nascimento, :sexo, :estado_civil, :email_principal, :telefone_principal,'
            . ' :status, :observacoes, :created_by, :updated_by)'
        );
        $statement->execute($this->parameters($data));

        return (int) $this->pdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $parameters = $this->parameters($data);
        unset($parameters['public_id'], $parameters['created_by']);
        $parameters['id'] = $id;

        $statement = $this->pdo()->prepare(
            'UPDATE pessoas SET rede_id = :rede_id, tipo = :tipo, nome = :nome,'
            . ' nome_social = :nome_social, cpf = :cpf, cnpj = :cnpj,'
            . ' data_nascimento = :data_nascimento, sexo = :sexo, estado_civil = :estado_civil,'
            . ' email_principal = :email_principal, telefone_principal = :telefone_principal,'
            . ' status = :status, observacoes = :observacoes, updated_by = :updated_by'
            . ' WHERE id = :id'
        );
        $statement->execute($parameters);
    }

    public function forScope(
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        ?int $actorId = null,
        string $search = ''
    ): array {
        $sql = 'SELECT p.id, p.rede_id, p.public_id, p.tipo, p.nome, p.nome_social, p.cpf, p.cnpj,'
            . ' p.data_nascimento, p.email_principal, p.telefone_principal, p.status'
            . ' FROM pessoas p';
        $parameters = [];
        $where = [];

        if ($unidadeId !== null) {
            $where[] = '(EXISTS (SELECT 1 FROM alunos a WHERE a.pessoa_id = p.id AND a.unidade_id = :unidade_aluno)'
                . ' OR EXISTS (SELECT 1 FROM aluno_responsaveis ar'
                . ' INNER JOIN alunos a ON a.id = ar.aluno_id'
                . ' WHERE ar.responsavel_pessoa_id = p.id AND a.unidade_id = :unidade_responsavel)'
                . ' OR p.created_by = :actor_unidade)';
            $parameters['unidade_aluno'] = $unidadeId;
            $parameters['unidade_responsavel'] = $unidadeId;
            $parameters['actor_unidade'] = $actorId;
        } elseif ($regiaoId !== null) {
            $where[] = '(EXISTS (SELECT 1 FROM alunos a'
                . ' INNER JOIN regiao_unidade ru ON ru.unidade_id = a.unidade_id'
                . ' WHERE a.pessoa_id = p.id AND ru.regiao_id = :regiao_aluno)'
                . ' OR EXISTS (SELECT 1 FROM aluno_responsaveis ar'
                . ' INNER JOIN alunos a ON a.id = ar.aluno_id'
                . ' INNER JOIN regiao_unidade ru ON ru.unidade_id = a.unidade_id'
                . ' WHERE ar.responsavel_pessoa_id = p.id AND ru.regiao_id = :regiao_responsavel)'
                . ' OR p.created_by = :actor_regiao)';
            $parameters['regiao_aluno'] = $regiaoId;
            $parameters['regiao_responsavel'] = $regiaoId;
            $parameters['actor_regiao'] = $actorId;
        } elseif ($redeId !== null) {
            $where[] = '(p.rede_id = :rede_id'
                . ' OR EXISTS (SELECT 1 FROM alunos a WHERE a.pessoa_id = p.id AND a.rede_id = :rede_aluno)'
                . ' OR EXISTS (SELECT 1 FROM aluno_responsaveis ar'
                . ' INNER JOIN alunos a ON a.id = ar.aluno_id'
                . ' WHERE ar.responsavel_pessoa_id = p.id AND a.rede_id = :rede_responsavel))';
            $parameters['rede_id'] = $redeId;
            $parameters['rede_aluno'] = $redeId;
            $parameters['rede_responsavel'] = $redeId;
        }

        if ($search !== '') {
            $where[] = '(p.nome LIKE :search_nome OR p.cpf LIKE :search_cpf'
                . ' OR p.cnpj LIKE :search_cnpj OR p.email_principal LIKE :search_email)';
            $term = '%' . $search . '%';
            $parameters['search_nome'] = $term;
            $parameters['search_cpf'] = $term;
            $parameters['search_cnpj'] = $term;
            $parameters['search_email'] = $term;
        }

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY p.nome ASC LIMIT 200';

        $statement = $this->pdo()->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function isLinkedToAlunoInScope(
        int $pessoaId,
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId
    ): bool {
        if ($unidadeId === null && $regiaoId === null && $redeId === null) {
            return true;
        }

        if ($unidadeId !== null) {
            $statement = $this->pdo()->prepare(
                'SELECT 1 FROM alunos a WHERE a.pessoa_id = :pessoa_aluno AND a.unidade_id = :unidade_aluno'
                . ' UNION SELECT 1 FROM aluno_responsaveis ar'
                . ' INNER JOIN alunos a ON a.id = ar.aluno_id'
                . ' WHERE ar.responsavel_pessoa_id = :pessoa_responsavel AND a.unidade_id = :unidade_responsavel'
                . ' LIMIT 1'
            );
            $statement->execute([
                'pessoa_aluno' => $pessoaId,
                'unidade_aluno' => $unidadeId,
                'pessoa_responsavel' => $pessoaId,
                'unidade_responsavel' => $unidadeId,
            ]);

            return $statement->fetchColumn() !== false;
        }

        if ($regiaoId !== null) {
            $statement = $this->pdo()->prepare(
                'SELECT 1 FROM alunos a'
                . ' INNER JOIN regiao_unidade ru ON ru.unidade_id = a.unidade_id'
                . ' WHERE a.pessoa_id = :pessoa_aluno AND ru.regiao_id = :regiao_aluno'
                . ' UNION SELECT 1 FROM aluno_responsaveis ar'
                . ' INNER JOIN alunos a ON a.id = ar.aluno_id'
                . ' INNER JOIN regiao_unidade ru ON ru.unidade_id = a.unidade_id'
                . ' WHERE ar.responsavel_pessoa_id = :pessoa_responsavel AND ru.regiao_id = :regiao_responsavel'
                . ' LIMIT 1'
            );
            $statement->execute([
                'pessoa_aluno' => $pessoaId,
                'regiao_aluno' => $regiaoId,
                'pessoa_responsavel' => $pessoaId,
                'regiao_responsavel' => $regiaoId,
            ]);

            return $statement->fetchColumn() !== false;
        }

        $statement = $this->pdo()->prepare(
            'SELECT 1 FROM alunos a WHERE a.pessoa_id = :pessoa_aluno AND a.rede_id = :rede_aluno'
            . ' UNION SELECT 1 FROM aluno_responsaveis ar'
            . ' INNER JOIN alunos a ON a.id = ar.aluno_id'
            . ' WHERE ar.responsavel_pessoa_id = :pessoa_responsavel AND a.rede_id = :rede_responsavel'
            . ' LIMIT 1'
        );
        $statement->execute([
            'pessoa_aluno' => $pessoaId,
            'rede_aluno' => $redeId,
            'pessoa_responsavel' => $pessoaId,
            'rede_responsavel' => $redeId,
        ]);

        return $statement->fetchColumn() !== false;
    }

    private function parameters(array $data): array
    {
        return [
            'rede_id' => $data['rede_id'] ?? null,
            'public_id' => $data['public_id'] ?? '',
            'tipo' => $data['tipo'],
            'nome' => $data['nome'],
            'nome_social' => $data['nome_social'] ?: null,
            'cpf' => $data['cpf'] ?: null,
            'cnpj' => $data['cnpj'] ?: null,
            'data_nascimento' => $data['data_nascimento'] ?: null,
            'sexo' => $data['sexo'] ?: null,
            'estado_civil' => $data['estado_civil'] ?: null,
            'email_principal' => $data['email_principal'] ?: null,
            'telefone_principal' => $data['telefone_principal'] ?: null,
            'status' => $data['status'] ?? 'ATIVO',
            'observacoes' => $data['observacoes'] ?: null,
            'created_by' => $data['created_by'] ?? null,
            'updated_by' => $data['updated_by'] ?? null,
        ];
    }
}

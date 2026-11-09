<?php

declare(strict_types=1);

namespace App\Repositories;

final class AlunoRepository extends Repository
{
    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT a.id, a.public_id, a.pessoa_id, a.rede_id, a.unidade_id, a.codigo,'
            . ' a.data_entrada, a.como_conheceu, a.indicado_por_pessoa_id, a.status,'
            . ' a.observacoes, a.created_at, a.updated_at,'
            . ' p.nome, p.nome_social, p.cpf, p.cnpj, p.data_nascimento, p.sexo,'
            . ' p.estado_civil, p.email_principal, p.telefone_principal,'
            . ' u.nome AS unidade_nome, r.nome_fantasia AS rede_nome'
            . ' FROM alunos a'
            . ' INNER JOIN pessoas p ON p.id = a.pessoa_id'
            . ' INNER JOIN unidades u ON u.id = a.unidade_id'
            . ' INNER JOIN redes r ON r.id = a.rede_id'
            . ' WHERE a.id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function findByPessoaId(int $pessoaId): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, pessoa_id, rede_id, unidade_id, status FROM alunos WHERE pessoa_id = :pessoa_id LIMIT 1'
        );
        $statement->execute(['pessoa_id' => $pessoaId]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function create(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO alunos (public_id, pessoa_id, rede_id, unidade_id, codigo, data_entrada,'
            . ' como_conheceu, indicado_por_pessoa_id, status, observacoes, created_by, updated_by)'
            . ' VALUES (:public_id, :pessoa_id, :rede_id, :unidade_id, :codigo, :data_entrada,'
            . ' :como_conheceu, :indicado_por_pessoa_id, :status, :observacoes, :created_by, :updated_by)'
        );
        $statement->execute($this->parameters($data));

        return (int) $this->pdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $parameters = $this->parameters($data);
        unset($parameters['public_id'], $parameters['pessoa_id'], $parameters['created_by']);
        $parameters['id'] = $id;

        $statement = $this->pdo()->prepare(
            'UPDATE alunos SET rede_id = :rede_id, unidade_id = :unidade_id, codigo = :codigo,'
            . ' data_entrada = :data_entrada, como_conheceu = :como_conheceu,'
            . ' indicado_por_pessoa_id = :indicado_por_pessoa_id, status = :status,'
            . ' observacoes = :observacoes, updated_by = :updated_by WHERE id = :id'
        );
        $statement->execute($parameters);
    }

    public function forScope(
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        string $search = ''
    ): array {
        $sql = 'SELECT a.id, a.pessoa_id, a.rede_id, a.unidade_id, a.codigo, a.data_entrada,'
            . ' a.status, p.nome, p.cpf, p.email_principal, u.nome AS unidade_nome,'
            . ' r.nome_fantasia AS rede_nome'
            . ' FROM alunos a'
            . ' INNER JOIN pessoas p ON p.id = a.pessoa_id'
            . ' INNER JOIN unidades u ON u.id = a.unidade_id'
            . ' INNER JOIN redes r ON r.id = a.rede_id';
        $parameters = [];
        $where = [];

        if ($unidadeId !== null) {
            $where[] = 'a.unidade_id = :unidade_id';
            $parameters['unidade_id'] = $unidadeId;
        } elseif ($regiaoId !== null) {
            $where[] = 'a.unidade_id IN (SELECT unidade_id FROM regiao_unidade WHERE regiao_id = :regiao_id)';
            $parameters['regiao_id'] = $regiaoId;
        } elseif ($redeId !== null) {
            $where[] = 'a.rede_id = :rede_id';
            $parameters['rede_id'] = $redeId;
        }

        if ($search !== '') {
            $where[] = '(p.nome LIKE :search_nome OR p.cpf LIKE :search_cpf'
                . ' OR p.email_principal LIKE :search_email OR a.codigo LIKE :search_codigo)';
            $term = '%' . $search . '%';
            $parameters['search_nome'] = $term;
            $parameters['search_cpf'] = $term;
            $parameters['search_email'] = $term;
            $parameters['search_codigo'] = $term;
        }

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY p.nome ASC LIMIT 200';

        $statement = $this->pdo()->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    private function parameters(array $data): array
    {
        return [
            'public_id' => $data['public_id'],
            'pessoa_id' => (int) $data['pessoa_id'],
            'rede_id' => (int) $data['rede_id'],
            'unidade_id' => (int) $data['unidade_id'],
            'codigo' => $data['codigo'] ?: null,
            'data_entrada' => $data['data_entrada'] ?: null,
            'como_conheceu' => $data['como_conheceu'] ?: null,
            'indicado_por_pessoa_id' => $data['indicado_por_pessoa_id'] ?? null,
            'status' => $data['status'] ?? 'ATIVO',
            'observacoes' => $data['observacoes'] ?: null,
            'created_by' => $data['created_by'] ?? null,
            'updated_by' => $data['updated_by'] ?? null,
        ];
    }
}

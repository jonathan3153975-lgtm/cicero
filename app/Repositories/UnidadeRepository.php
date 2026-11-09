<?php

declare(strict_types=1);

namespace App\Repositories;

final class UnidadeRepository extends Repository
{
    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, rede_id, nome, codigo_interno, situacao, cep, logradouro, numero, complemento,'
            . ' bairro, cidade, estado, telefone, whatsapp, email, data_abertura,'
            . ' horario_funcionamento, capacidade_maxima, metragem'
            . ' FROM unidades WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function forRede(int $redeId): array
    {
        $statement = $this->pdo()->prepare(
            'SELECT u.id, u.rede_id, u.nome, u.codigo_interno, u.situacao, u.cidade, u.estado,'
            . ' r.nome_fantasia AS rede_nome'
            . ' FROM unidades u INNER JOIN redes r ON r.id = u.rede_id'
            . ' WHERE u.rede_id = :rede_id ORDER BY u.nome ASC'
        );
        $statement->execute(['rede_id' => $redeId]);

        return $statement->fetchAll();
    }

    public function forScope(?int $redeId, ?int $unidadeId, ?int $regiaoId = null): array
    {
        if ($unidadeId !== null) {
            $unidade = $this->findById($unidadeId);

            return $unidade === null ? [] : [$unidade];
        }

        if ($regiaoId !== null) {
            return $this->forRegiao($regiaoId);
        }

        if ($redeId !== null) {
            return $this->forRede($redeId);
        }

        return $this->all();
    }

    public function all(): array
    {
        $statement = $this->pdo()->query(
            'SELECT u.id, u.rede_id, u.nome, u.codigo_interno, u.situacao, u.cidade, u.estado,'
            . ' r.nome_fantasia AS rede_nome'
            . ' FROM unidades u INNER JOIN redes r ON r.id = u.rede_id'
            . ' ORDER BY r.nome_fantasia ASC, u.nome ASC'
        );

        return $statement->fetchAll();
    }

    public function allActive(): array
    {
        $statement = $this->pdo()->query(
            'SELECT id, rede_id, nome FROM unidades WHERE situacao = \'ATIVA\' ORDER BY nome ASC'
        );

        return $statement->fetchAll();
    }

    public function forRegiao(int $regiaoId): array
    {
        $statement = $this->pdo()->prepare(
            'SELECT u.id, u.rede_id, u.nome, u.codigo_interno, u.situacao, u.cidade, u.estado,'
            . ' r.nome_fantasia AS rede_nome'
            . ' FROM unidades u'
            . ' INNER JOIN regiao_unidade ru ON ru.unidade_id = u.id'
            . ' INNER JOIN redes r ON r.id = u.rede_id'
            . ' WHERE ru.regiao_id = :regiao_id ORDER BY u.nome ASC'
        );
        $statement->execute(['regiao_id' => $regiaoId]);

        return $statement->fetchAll();
    }

    public function isInRegiao(int $unidadeId, int $regiaoId): bool
    {
        $statement = $this->pdo()->prepare(
            'SELECT 1 FROM regiao_unidade WHERE unidade_id = :unidade_id AND regiao_id = :regiao_id LIMIT 1'
        );
        $statement->execute(['unidade_id' => $unidadeId, 'regiao_id' => $regiaoId]);

        return $statement->fetchColumn() !== false;
    }

    public function findByRedeAndCode(int $redeId, string $codigo): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, rede_id, nome, codigo_interno FROM unidades'
            . ' WHERE rede_id = :rede_id AND codigo_interno = :codigo LIMIT 1'
        );
        $statement->execute(['rede_id' => $redeId, 'codigo' => $codigo]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function create(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO unidades (rede_id, nome, codigo_interno, situacao, cep, logradouro, numero,'
            . ' complemento, bairro, cidade, estado, telefone, whatsapp, email, data_abertura,'
            . ' horario_funcionamento, capacidade_maxima, metragem)'
            . ' VALUES (:rede_id, :nome, :codigo_interno, :situacao, :cep, :logradouro, :numero,'
            . ' :complemento, :bairro, :cidade, :estado, :telefone, :whatsapp, :email, :data_abertura,'
            . ' :horario_funcionamento, :capacidade_maxima, :metragem)'
        );
        $statement->execute($this->parameters($data));

        return (int) $this->pdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $parameters = $this->parameters($data);
        $parameters['id'] = $id;

        $statement = $this->pdo()->prepare(
            'UPDATE unidades SET rede_id = :rede_id, nome = :nome, codigo_interno = :codigo_interno,'
            . ' situacao = :situacao, cep = :cep, logradouro = :logradouro, numero = :numero,'
            . ' complemento = :complemento, bairro = :bairro, cidade = :cidade, estado = :estado,'
            . ' telefone = :telefone, whatsapp = :whatsapp, email = :email, data_abertura = :data_abertura,'
            . ' horario_funcionamento = :horario_funcionamento, capacidade_maxima = :capacidade_maxima,'
            . ' metragem = :metragem WHERE id = :id'
        );
        $statement->execute($parameters);
    }

    private function parameters(array $data): array
    {
        return [
            'rede_id' => (int) $data['rede_id'],
            'nome' => $data['nome'],
            'codigo_interno' => $data['codigo_interno'],
            'situacao' => $data['situacao'] ?? 'ATIVA',
            'cep' => $data['cep'] ?? null,
            'logradouro' => $data['logradouro'] ?? null,
            'numero' => $data['numero'] ?? null,
            'complemento' => $data['complemento'] ?? null,
            'bairro' => $data['bairro'] ?? null,
            'cidade' => $data['cidade'] ?? null,
            'estado' => $data['estado'] ?? null,
            'telefone' => $data['telefone'] ?? null,
            'whatsapp' => $data['whatsapp'] ?? null,
            'email' => $data['email'] ?? null,
            'data_abertura' => $data['data_abertura'] ?: null,
            'horario_funcionamento' => $data['horario_funcionamento'] ?? null,
            'capacidade_maxima' => $data['capacidade_maxima'] !== '' ? (int) $data['capacidade_maxima'] : null,
            'metragem' => $data['metragem'] !== '' ? (float) $data['metragem'] : null,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories;

final class CobrancaRepository extends Repository
{
    public function findById(int $id): ?array
    {
        $statement = $this->pdo()->prepare(
            'SELECT c.id, c.public_id, c.rede_id, c.unidade_id, c.aluno_id, c.matricula_id,'
            . ' c.contrato_id, c.competencia, c.numero, c.tipo, c.data_emissao, c.data_vencimento,'
            . ' c.valor_original, c.valor_desconto, c.valor_juros, c.valor_multa, c.valor_total,'
            . ' c.status, p.nome AS aluno_nome, u.nome AS unidade_nome'
            . ' FROM cobrancas c'
            . ' INNER JOIN alunos a ON a.id = c.aluno_id'
            . ' INNER JOIN pessoas p ON p.id = a.pessoa_id'
            . ' INNER JOIN unidades u ON u.id = c.unidade_id'
            . ' WHERE c.id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    public function forScope(
        ?int $redeId,
        ?int $unidadeId,
        ?int $regiaoId,
        string $search = '',
        bool $onlyOpen = false
    ): array {
        $sql = 'SELECT c.id, c.aluno_id, c.rede_id, c.unidade_id, c.competencia, c.numero,'
            . ' c.tipo, c.data_vencimento, c.valor_total, c.status, p.nome AS aluno_nome,'
            . ' u.nome AS unidade_nome'
            . ' FROM cobrancas c'
            . ' INNER JOIN alunos a ON a.id = c.aluno_id'
            . ' INNER JOIN pessoas p ON p.id = a.pessoa_id'
            . ' INNER JOIN unidades u ON u.id = c.unidade_id';
        $where = [];
        $parameters = [];

        if ($onlyOpen) {
            $where[] = 'c.status IN (\'ABERTA\', \'VENCIDA\', \'PARCIAL\')';
        }

        if ($unidadeId !== null) {
            $where[] = 'c.unidade_id = :unidade_id';
            $parameters['unidade_id'] = $unidadeId;
        } elseif ($regiaoId !== null) {
            $where[] = 'c.unidade_id IN (SELECT unidade_id FROM regiao_unidade WHERE regiao_id = :regiao_id)';
            $parameters['regiao_id'] = $regiaoId;
        } elseif ($redeId !== null) {
            $where[] = 'c.rede_id = :rede_id';
            $parameters['rede_id'] = $redeId;
        }

        if ($search !== '') {
            $where[] = '(p.nome LIKE :search_nome OR c.numero LIKE :search_numero)';
            $term = '%' . $search . '%';
            $parameters['search_nome'] = $term;
            $parameters['search_numero'] = $term;
        }

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY c.data_vencimento ASC, c.id DESC LIMIT 200';
        $statement = $this->pdo()->prepare($sql);
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function items(int $cobrancaId): array
    {
        $statement = $this->pdo()->prepare(
            'SELECT id, descricao, quantidade, valor_unitario, valor_total'
            . ' FROM cobranca_itens WHERE cobranca_id = :cobranca_id ORDER BY id ASC'
        );
        $statement->execute(['cobranca_id' => $cobrancaId]);

        return $statement->fetchAll();
    }


    public function countForScope(?int $redeId, ?int $unidadeId, ?int $regiaoId, bool $onlyOpen = false): int
    {
        $sql = 'SELECT COUNT(*) FROM cobrancas c';
        $where = [];
        $parameters = [];

        if ($onlyOpen) {
            $where[] = "c.status IN ('ABERTA', 'VENCIDA', 'PARCIAL')";
        }

        if ($unidadeId !== null) {
            $where[] = 'c.unidade_id = :unidade_id';
            $parameters['unidade_id'] = $unidadeId;
        } elseif ($regiaoId !== null) {
            $where[] = 'c.unidade_id IN (SELECT unidade_id FROM regiao_unidade WHERE regiao_id = :regiao_id)';
            $parameters['regiao_id'] = $regiaoId;
        } elseif ($redeId !== null) {
            $where[] = 'c.rede_id = :rede_id';
            $parameters['rede_id'] = $redeId;
        }

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $statement = $this->pdo()->prepare($sql);
        $statement->execute($parameters);

        return (int) $statement->fetchColumn();
    }

    public function create(array $data): int
    {
        $statement = $this->pdo()->prepare(
            'INSERT INTO cobrancas (public_id, rede_id, unidade_id, aluno_id, matricula_id,'
            . ' contrato_id, competencia, numero, tipo, data_emissao, data_vencimento,'
            . ' valor_original, valor_desconto, valor_juros, valor_multa, valor_total,'
            . ' status, pix_ref, boleto_ref, created_by, updated_by)'
            . ' VALUES (:public_id, :rede_id, :unidade_id, :aluno_id, :matricula_id,'
            . ' :contrato_id, :competencia, :numero, :tipo, :data_emissao, :data_vencimento,'
            . ' :valor_original, :valor_desconto, :valor_juros, :valor_multa, :valor_total,'
            . ' :status, :pix_ref, :boleto_ref, :created_by, :updated_by)'
        );
        $statement->execute($this->parameters($data));

        return (int) $this->pdo()->lastInsertId();
    }

    public function addItem(
        int $cobrancaId,
        string $descricao,
        float $quantidade,
        float $valorUnitario,
        float $valorTotal,
        ?int $planoId = null,
        ?int $servicoId = null
    ): int {
        $statement = $this->pdo()->prepare(
            'INSERT INTO cobranca_itens (cobranca_id, descricao, quantidade, valor_unitario,'
            . ' valor_total, plano_id, servico_id)'
            . ' VALUES (:cobranca_id, :descricao, :quantidade, :valor_unitario, :valor_total,'
            . ' :plano_id, :servico_id)'
        );
        $statement->execute([
            'cobranca_id' => $cobrancaId,
            'descricao' => $descricao,
            'quantidade' => $quantidade,
            'valor_unitario' => $valorUnitario,
            'valor_total' => $valorTotal,
            'plano_id' => $planoId,
            'servico_id' => $servicoId,
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function updateStatus(int $id, string $status): void
    {
        $statement = $this->pdo()->prepare('UPDATE cobrancas SET status = :status WHERE id = :id');
        $statement->execute(['status' => $status, 'id' => $id]);
    }

    public function refreshOverdueStatus(): void
    {
        $this->pdo()->exec(
            'UPDATE cobrancas SET status = \'VENCIDA\''
            . ' WHERE status = \'ABERTA\' AND data_vencimento < CURDATE()'
        );
    }

    public function existsForMatriculaCompetencia(
        int $matriculaId,
        string $competencia,
        string $tipo = 'MENSALIDADE'
    ): bool {
        $statement = $this->pdo()->prepare(
            'SELECT 1 FROM cobrancas WHERE matricula_id = :matricula_id'
            . ' AND competencia = :competencia AND tipo = :tipo LIMIT 1'
        );
        $statement->execute([
            'matricula_id' => $matriculaId,
            'competencia' => $competencia,
            'tipo' => $tipo,
        ]);

        return $statement->fetchColumn() !== false;
    }

    private function parameters(array $data): array
    {
        return [
            'public_id' => $data['public_id'],
            'rede_id' => (int) $data['rede_id'],
            'unidade_id' => (int) $data['unidade_id'],
            'aluno_id' => (int) $data['aluno_id'],
            'matricula_id' => $data['matricula_id'] !== null ? (int) $data['matricula_id'] : null,
            'contrato_id' => $data['contrato_id'] !== null ? (int) $data['contrato_id'] : null,
            'competencia' => $data['competencia'],
            'numero' => $data['numero'],
            'tipo' => $data['tipo'] ?? 'MENSALIDADE',
            'data_emissao' => $data['data_emissao'],
            'data_vencimento' => $data['data_vencimento'],
            'valor_original' => $data['valor_original'],
            'valor_desconto' => $data['valor_desconto'] ?? 0,
            'valor_juros' => $data['valor_juros'] ?? 0,
            'valor_multa' => $data['valor_multa'] ?? 0,
            'valor_total' => $data['valor_total'],
            'status' => $data['status'] ?? 'ABERTA',
            'pix_ref' => ($data['pix_ref'] ?? '') ?: null,
            'boleto_ref' => ($data['boleto_ref'] ?? '') ?: null,
            'created_by' => $data['created_by'] ?? null,
            'updated_by' => $data['updated_by'] ?? null,
        ];
    }
}

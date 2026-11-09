<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Exceptions\ValidationException;
use App\Repositories\AlunoRepository;
use App\Repositories\AlunoResponsavelRepository;
use App\Repositories\CentroCustoRepository;
use App\Repositories\CobrancaRepository;
use App\Repositories\ConciliacaoRepository;
use App\Repositories\ContaBancariaRepository;
use App\Repositories\ContaPagarRepository;
use App\Repositories\ContratoRepository;
use App\Repositories\EstornoRepository;
use App\Repositories\FormaPagamentoRepository;
use App\Repositories\MatriculaRepository;
use App\Repositories\ModalidadeRepository;
use App\Repositories\MovimentoCaixaRepository;
use App\Repositories\PagamentoRepository;
use App\Repositories\PessoaContatoRepository;
use App\Repositories\PessoaEnderecoRepository;
use App\Repositories\PessoaRepository;
use App\Repositories\PlanoPrecoRepository;
use App\Repositories\PlanoRepository;
use App\Repositories\UnidadeRepository;
use App\Services\AlunoService;
use App\Services\CobrancaService;
use App\Services\ConciliacaoService;
use App\Services\ContaPagarService;
use App\Services\MatriculaService;
use App\Services\PagamentoService;
use App\Services\PessoaService;
use App\Services\PlanoPrecoService;
use App\Services\PlanoService;

final class FinanceiroTest extends IntegrationTestCase
{
    private const EMAILS = ['wave4.student@local.test', 'wave4.shopping@local.test'];
    private const PLAN_PREFIX = 'WAVE4 ';
    private const COMPETENCIA = '2099-11';

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanup();
    }

    protected function tearDown(): void
    {
        $this->cleanup();
    }

    public function testCobrancaDeMatriculaPagamentoEEstorno(): void
    {
        [$alunoId, , $precoId] = $this->studentAndPlan('wave4.student@local.test', 'CENTRO');
        $actorId = $this->userId('local@local.test');
        $redeId = $this->redeId();
        $centroId = $this->unidadeId('CENTRO');

        $matriculaId = $this->matriculaService()->create($actorId, $redeId, $centroId, null, [
            'aluno_id' => (string) $alunoId,
            'plano_unidade_preco_id' => (string) $precoId,
            'data_matricula' => '2099-01-10',
            'data_inicio' => '2099-01-10',
            'status' => 'ATIVA',
        ]);

        $cobrancaId = $this->cobrancaService()->generateForMatricula(
            $matriculaId,
            self::COMPETENCIA,
            '2099-11-10'
        );

        $cobrancas = new CobrancaRepository($this->database);
        $cobranca = $cobrancas->findById($cobrancaId);
        self::assertSame('150.00', (string) $cobranca['valor_total']);
        self::assertSame('ABERTA', $cobranca['status']);

        $pagamentoId = $this->pagamentoService()->register($actorId, $redeId, $centroId, null, [
            'cobranca_id' => (string) $cobrancaId,
            'valor' => '150.00',
            'conta_bancaria_id' => (string) $this->defaultAccount($centroId),
            'forma_pagamento_id' => (string) $this->pixForma(),
            'data_pagamento' => '2099-11-05',
        ]);

        self::assertSame('PAGA', $cobrancas->findById($cobrancaId)['status']);
        self::assertSame(150.0, (new PagamentoRepository($this->database))->sumAllocatedForCobranca($cobrancaId));

        $this->pagamentoService()->reverse($actorId, $redeId, $centroId, null, $pagamentoId, 'Teste de estorno');

        self::assertSame('ESTORNADO', (new PagamentoRepository($this->database))->findById($pagamentoId)['status']);
        self::assertSame('ABERTA', $cobrancas->findById($cobrancaId)['status']);
        self::assertSame(0.0, (new PagamentoRepository($this->database))->sumAllocatedForCobranca($cobrancaId));
    }

    public function testPagamentoAcimaDoSaldoERejeitado(): void
    {
        [$alunoId] = $this->studentAndPlan('wave4.student@local.test', 'CENTRO');
        $actorId = $this->userId('local@local.test');
        $redeId = $this->redeId();
        $centroId = $this->unidadeId('CENTRO');

        $cobrancaId = $this->cobrancaService()->createManual($actorId, $redeId, $centroId, null, [
            'aluno_id' => (string) $alunoId,
            'tipo' => 'SERVICO',
            'competencia' => self::COMPETENCIA,
            'data_vencimento' => '2099-11-10',
            'valor' => '100.00',
        ]);

        try {
            $this->pagamentoService()->register($actorId, $redeId, $centroId, null, [
                'cobranca_id' => (string) $cobrancaId,
                'valor' => '100.01',
                'conta_bancaria_id' => (string) $this->defaultAccount($centroId),
                'forma_pagamento_id' => (string) $this->pixForma(),
                'data_pagamento' => '2099-11-05',
            ]);
            self::fail('Pagamento acima do saldo deveria falhar.');
        } catch (ValidationException $exception) {
            self::assertArrayHasKey('valor', $exception->getErrors());
        }
    }

    public function testContaAPagarGeraMovimentoDeSaida(): void
    {
        $actorId = $this->userId('local@local.test');
        $redeId = $this->redeId();
        $centroId = $this->unidadeId('CENTRO');

        $contaId = $this->contaPagarService()->create($actorId, $redeId, $centroId, null, [
            'unidade_id' => (string) $centroId,
            'descricao' => 'WAVE4 Energia',
            'competencia' => self::COMPETENCIA,
            'data_vencimento' => '2099-11-10',
            'valor' => '200.00',
        ]);

        $this->contaPagarService()->pay(
            $actorId,
            $redeId,
            $centroId,
            null,
            $contaId,
            '2099-11-05',
            $this->defaultAccount($centroId)
        );

        $conta = (new ContaPagarRepository($this->database))->findById($contaId);
        self::assertSame('PAGA', $conta['status']);

        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM movimentos_caixa WHERE referencia_tipo = \'conta_pagar\''
            . ' AND referencia_id = :id AND tipo = \'SAIDA\''
        );
        $statement->execute(['id' => $contaId]);
        self::assertSame(1, (int) $statement->fetchColumn());
    }

    public function testConciliacaoImportaEConciliaMovimento(): void
    {
        $redeId = $this->redeId();
        $centroId = $this->unidadeId('CENTRO');
        $accountId = $this->defaultAccount($centroId);

        (new MovimentoCaixaRepository($this->database))->create([
            'rede_id' => $redeId,
            'unidade_id' => $centroId,
            'conta_bancaria_id' => $accountId,
            'tipo' => 'ENTRADA',
            'origem' => 'TESTE',
            'valor' => 123.45,
            'data' => '2099-11-05',
            'descricao' => 'WAVE4 movimento teste',
        ]);

        $conciliacaoId = $this->conciliacaoService()->create($redeId, $centroId, null, [
            'conta_bancaria_id' => (string) $accountId,
            'competencia' => self::COMPETENCIA,
        ]);

        $importados = $this->conciliacaoService()->importCsv(
            $conciliacaoId,
            "2099-11-05;WAVE4 linha teste;123,45;ENTRADA"
        );

        self::assertSame(1, $importados);
        $itens = $this->conciliacaoService()->items($conciliacaoId);
        self::assertSame('CONCILIADO', $itens[0]['status']);

        $this->conciliacaoService()->close($conciliacaoId, $this->userId('local@local.test'));
        self::assertSame('FECHADA', (new ConciliacaoRepository($this->database))->findById($conciliacaoId)['status']);
    }

    private function studentAndPlan(string $email, string $unitCode): array
    {
        $actorId = $unitCode === 'SHOPPING'
            ? $this->userId('admin@local.test')
            : $this->userId('local@local.test');
        $redeId = $this->redeId();
        $unidadeId = $this->unidadeId($unitCode);

        $alunoId = $this->alunoService()->create($actorId, $redeId, $unidadeId, null, [
            'nome' => 'Aluno ' . $email,
            'email_principal' => $email,
            'cpf' => $unitCode === 'CENTRO' ? '11144477735' : '52998224725',
            'codigo' => 'WAVE4-' . $unidadeId,
            'data_entrada' => '2099-01-01',
            'status' => 'ATIVO',
        ]);

        $planoId = $this->planoService()->create($actorId, $redeId, [
            'nome' => self::PLAN_PREFIX . $unitCode . ' ' . $email,
            'tipo' => 'MENSAL',
            'duracao_meses' => '1',
            'status' => 'ATIVO',
        ], $this->modalityIds(1));

        $precoId = $this->planoPrecoService()->create($actorId, $redeId, $unidadeId, null, $planoId, [
            'vigencia_inicio' => '2099-01-01',
            'valor_matricula' => '100.00',
            'valor_mensal' => '150.00',
            'status' => 'ATIVO',
        ]);

        return [$alunoId, $planoId, $precoId];
    }

    private function modalityIds(int $limit): array
    {
        $statement = $this->pdo->query(
            'SELECT id FROM modalidades WHERE rede_id IS NULL ORDER BY id ASC LIMIT ' . max(1, $limit)
        );

        return array_map('intval', $statement->fetchAll(\PDO::FETCH_COLUMN));
    }

    private function defaultAccount(int $unidadeId): int
    {
        $statement = $this->pdo->prepare(
            'SELECT id FROM contas_bancarias WHERE unidade_id = :unidade_id AND status = \'ATIVO\''
            . ' ORDER BY id ASC LIMIT 1'
        );
        $statement->execute(['unidade_id' => $unidadeId]);
        $id = $statement->fetchColumn();
        self::assertNotFalse($id);

        return (int) $id;
    }

    private function pixForma(): int
    {
        $id = $this->pdo->query(
            'SELECT id FROM formas_pagamento WHERE tipo = \'PIX\' AND status = \'ATIVO\' LIMIT 1'
        )->fetchColumn();
        self::assertNotFalse($id);

        return (int) $id;
    }

    private function pessoaService(): PessoaService
    {
        return new PessoaService(
            new PessoaRepository($this->database),
            new PessoaContatoRepository($this->database),
            new PessoaEnderecoRepository($this->database),
            $this->database
        );
    }

    private function alunoService(): AlunoService
    {
        return new AlunoService(
            new AlunoRepository($this->database),
            new AlunoResponsavelRepository($this->database),
            $this->pessoaService(),
            new PessoaRepository($this->database),
            new UnidadeRepository($this->database),
            $this->database
        );
    }

    private function planoService(): PlanoService
    {
        return new PlanoService(
            new PlanoRepository($this->database),
            new ModalidadeRepository($this->database),
            $this->database
        );
    }

    private function planoPrecoService(): PlanoPrecoService
    {
        return new PlanoPrecoService(
            new PlanoPrecoRepository($this->database),
            new PlanoRepository($this->database),
            new UnidadeRepository($this->database)
        );
    }

    private function matriculaService(): MatriculaService
    {
        return new MatriculaService(
            $this->alunoService(),
            new AlunoRepository($this->database),
            new PlanoPrecoRepository($this->database),
            new PlanoRepository($this->database),
            new ContratoRepository($this->database),
            new MatriculaRepository($this->database),
            new UnidadeRepository($this->database),
            $this->database
        );
    }

    private function cobrancaService(): CobrancaService
    {
        return new CobrancaService(
            new CobrancaRepository($this->database),
            new MatriculaRepository($this->database),
            $this->alunoService(),
            new UnidadeRepository($this->database),
            $this->database
        );
    }

    private function pagamentoService(): PagamentoService
    {
        return new PagamentoService(
            new PagamentoRepository($this->database),
            new CobrancaRepository($this->database),
            $this->cobrancaService(),
            new ContaBancariaRepository($this->database),
            new FormaPagamentoRepository($this->database),
            new MovimentoCaixaRepository($this->database),
            new EstornoRepository($this->database),
            new UnidadeRepository($this->database),
            $this->database
        );
    }

    private function contaPagarService(): ContaPagarService
    {
        return new ContaPagarService(
            new ContaPagarRepository($this->database),
            new ContaBancariaRepository($this->database),
            new CentroCustoRepository($this->database),
            new MovimentoCaixaRepository($this->database),
            new UnidadeRepository($this->database),
            $this->database
        );
    }

    private function conciliacaoService(): ConciliacaoService
    {
        return new ConciliacaoService(
            new ConciliacaoRepository($this->database),
            new ContaBancariaRepository($this->database),
            new MovimentoCaixaRepository($this->database),
            new UnidadeRepository($this->database),
            $this->database
        );
    }

    private function cleanup(): void
    {
        if (!isset($this->pdo)) {
            return;
        }

        $emailPlaceholders = implode(', ', array_fill(0, count(self::EMAILS), '?'));
        $pessoaStmt = $this->pdo->prepare(
            'SELECT id FROM pessoas WHERE email_principal IN (' . $emailPlaceholders . ')'
        );
        $pessoaStmt->execute(self::EMAILS);
        $pessoaIds = array_map('intval', $pessoaStmt->fetchAll(\PDO::FETCH_COLUMN));

        $planStmt = $this->pdo->prepare('SELECT id FROM planos WHERE nome LIKE :prefix');
        $planStmt->execute(['prefix' => self::PLAN_PREFIX . '%']);
        $planIds = array_map('intval', $planStmt->fetchAll(\PDO::FETCH_COLUMN));

        $alunoIds = [];

        if ($pessoaIds !== []) {
            $placeholders = implode(', ', array_fill(0, count($pessoaIds), '?'));
            $alunoStmt = $this->pdo->prepare('SELECT id FROM alunos WHERE pessoa_id IN (' . $placeholders . ')');
            $alunoStmt->execute($pessoaIds);
            $alunoIds = array_map('intval', $alunoStmt->fetchAll(\PDO::FETCH_COLUMN));
        }

        $precoIds = [];

        if ($planIds !== []) {
            $placeholders = implode(', ', array_fill(0, count($planIds), '?'));
            $precoStmt = $this->pdo->prepare(
                'SELECT id FROM plano_unidade_precos WHERE plano_id IN (' . $placeholders . ')'
            );
            $precoStmt->execute($planIds);
            $precoIds = array_map('intval', $precoStmt->fetchAll(\PDO::FETCH_COLUMN));
        }

        $cobrancaIds = [];
        $pagamentoIds = [];

        if ($alunoIds !== []) {
            $placeholders = implode(', ', array_fill(0, count($alunoIds), '?'));
            $cobrancaStmt = $this->pdo->prepare('SELECT id FROM cobrancas WHERE aluno_id IN (' . $placeholders . ')');
            $cobrancaStmt->execute($alunoIds);
            $cobrancaIds = array_map('intval', $cobrancaStmt->fetchAll(\PDO::FETCH_COLUMN));

            $pagamentoStmt = $this->pdo->prepare('SELECT id FROM pagamentos WHERE aluno_id IN (' . $placeholders . ')');
            $pagamentoStmt->execute($alunoIds);
            $pagamentoIds = array_map('intval', $pagamentoStmt->fetchAll(\PDO::FETCH_COLUMN));
        }

        if ($pagamentoIds !== []) {
            $placeholders = implode(', ', array_fill(0, count($pagamentoIds), '?'));
            $this->pdo->prepare('DELETE FROM estornos WHERE pagamento_id IN (' . $placeholders . ')')->execute($pagamentoIds);
            $this->pdo->prepare(
                'DELETE FROM movimentos_caixa WHERE referencia_tipo = \'pagamento\' AND referencia_id IN (' . $placeholders . ')'
            )->execute($pagamentoIds);
            $this->pdo->prepare('DELETE FROM pagamento_alocacoes WHERE pagamento_id IN (' . $placeholders . ')')->execute($pagamentoIds);
            $this->pdo->prepare('DELETE FROM pagamentos WHERE id IN (' . $placeholders . ')')->execute($pagamentoIds);
        }

        if ($cobrancaIds !== []) {
            $placeholders = implode(', ', array_fill(0, count($cobrancaIds), '?'));
            $this->pdo->prepare('DELETE FROM cobrancas WHERE id IN (' . $placeholders . ')')->execute($cobrancaIds);
        }

        $contaPagarStmt = $this->pdo->prepare('SELECT id FROM contas_pagar WHERE descricao LIKE \'WAVE4 %\'');
        $contaPagarStmt->execute();
        $contaPagarIds = array_map('intval', $contaPagarStmt->fetchAll(\PDO::FETCH_COLUMN));

        if ($contaPagarIds !== []) {
            $placeholders = implode(', ', array_fill(0, count($contaPagarIds), '?'));
            $this->pdo->prepare(
                'DELETE FROM movimentos_caixa WHERE referencia_tipo = \'conta_pagar\' AND referencia_id IN (' . $placeholders . ')'
            )->execute($contaPagarIds);
            $this->pdo->prepare('DELETE FROM contas_pagar WHERE id IN (' . $placeholders . ')')->execute($contaPagarIds);
        }

        $this->pdo->prepare('DELETE FROM conciliacoes_bancarias WHERE competencia = :competencia')
            ->execute(['competencia' => self::COMPETENCIA]);
        $this->pdo->prepare('DELETE FROM movimentos_caixa WHERE descricao LIKE \'WAVE4 %\'')->execute();

        if ($alunoIds !== []) {
            $placeholders = implode(', ', array_fill(0, count($alunoIds), '?'));
            $this->pdo->prepare('DELETE FROM matriculas WHERE aluno_id IN (' . $placeholders . ')')->execute($alunoIds);
            $this->pdo->prepare('DELETE FROM contratos WHERE aluno_id IN (' . $placeholders . ')')->execute($alunoIds);
        }

        if ($precoIds !== []) {
            $placeholders = implode(', ', array_fill(0, count($precoIds), '?'));
            $this->pdo->prepare('DELETE FROM plano_unidade_precos WHERE id IN (' . $placeholders . ')')->execute($precoIds);
        }

        if ($planIds !== []) {
            $placeholders = implode(', ', array_fill(0, count($planIds), '?'));
            $this->pdo->prepare('DELETE FROM plano_modalidades WHERE plano_id IN (' . $placeholders . ')')->execute($planIds);
            $this->pdo->prepare('DELETE FROM planos WHERE id IN (' . $placeholders . ')')->execute($planIds);
        }

        if ($alunoIds !== []) {
            $placeholders = implode(', ', array_fill(0, count($alunoIds), '?'));
            $this->pdo->prepare('DELETE FROM aluno_responsaveis WHERE aluno_id IN (' . $placeholders . ')')->execute($alunoIds);
            $this->pdo->prepare('DELETE FROM alunos WHERE id IN (' . $placeholders . ')')->execute($alunoIds);
        }

        if ($pessoaIds !== []) {
            $placeholders = implode(', ', array_fill(0, count($pessoaIds), '?'));
            $this->pdo->prepare('DELETE FROM pessoas WHERE id IN (' . $placeholders . ')')->execute($pessoaIds);
        }
    }
}

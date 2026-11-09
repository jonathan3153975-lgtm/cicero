<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;
use App\Repositories\AlunoRepository;
use App\Repositories\AlunoResponsavelRepository;
use App\Repositories\ContratoRepository;
use App\Repositories\MatriculaRepository;
use App\Repositories\ModalidadeRepository;
use App\Repositories\PessoaContatoRepository;
use App\Repositories\PessoaEnderecoRepository;
use App\Repositories\PessoaRepository;
use App\Repositories\PlanoPrecoRepository;
use App\Repositories\PlanoRepository;
use App\Repositories\UnidadeRepository;
use App\Services\AlunoService;
use App\Services\MatriculaService;
use App\Services\PessoaService;
use App\Services\PlanoPrecoService;
use App\Services\PlanoService;

final class PlanoMatriculaTest extends IntegrationTestCase
{
    private const EMAILS = ['wave2.student@local.test', 'wave2.shopping@local.test'];
    private const PLAN_PREFIX = 'WAVE2 ';

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanup();
    }

    protected function tearDown(): void
    {
        $this->cleanup();
    }

    public function testPlanoComModalidadesEPreco(): void
    {
        $service = $this->planoService();
        $actorId = $this->userId('local@local.test');
        $redeId = $this->redeId();
        $centroId = $this->unidadeId('CENTRO');
        $modalityIds = $this->modalityIds(2);

        $planoId = $service->create($actorId, $redeId, [
            'nome' => self::PLAN_PREFIX . 'Mensal',
            'tipo' => 'MENSAL',
            'duracao_meses' => '1',
            'aulas_semana' => '3',
            'limite_modalidades' => '2',
            'status' => 'ATIVO',
        ], $modalityIds);

        self::assertGreaterThan(0, $planoId);
        self::assertSame($modalityIds, (new PlanoRepository($this->database))->modalidadeIdsForPlano($planoId));

        $precoService = $this->planoPrecoService();
        $precoId = $precoService->create($actorId, $redeId, $centroId, null, $planoId, [
            'vigencia_inicio' => '2026-01-01',
            'valor_matricula' => '100.00',
            'valor_mensal' => '150.00',
            'status' => 'ATIVO',
        ]);

        $preco = (new PlanoPrecoRepository($this->database))->findById($precoId);
        self::assertSame($centroId, (int) $preco['unidade_id']);
        self::assertSame('150.00', (string) $preco['valor_mensal']);
    }

    public function testMatriculaCriaContratoEImpedeDuplicidade(): void
    {
        [$alunoId, $planoId, $precoCentro] = $this->studentAndPlan('wave2.student@local.test', 'CENTRO');
        $actorId = $this->userId('local@local.test');
        $redeId = $this->redeId();
        $centroId = $this->unidadeId('CENTRO');
        $service = $this->matriculaService();

        $matriculaId = $service->create($actorId, $redeId, $centroId, null, [
            'aluno_id' => (string) $alunoId,
            'plano_unidade_preco_id' => (string) $precoCentro,
            'data_matricula' => '2026-01-10',
            'data_inicio' => '2026-01-10',
            'status' => 'ATIVA',
        ]);

        $matricula = (new MatriculaRepository($this->database))->findById($matriculaId);
        self::assertNotNull($matricula);
        self::assertNotNull($matricula['contrato_id']);

        $contrato = (new ContratoRepository($this->database))->findById((int) $matricula['contrato_id']);
        self::assertNotNull($contrato);
        self::assertSame('150.00', (string) $contrato['valor_mensal']);

        try {
            $service->create($actorId, $redeId, $centroId, null, [
                'aluno_id' => (string) $alunoId,
                'plano_unidade_preco_id' => (string) $precoCentro,
                'data_matricula' => '2026-01-11',
                'data_inicio' => '2026-01-11',
                'status' => 'ATIVA',
            ]);
            self::fail('Matrícula ativa duplicada deveria falhar.');
        } catch (ValidationException $exception) {
            self::assertArrayHasKey('aluno_id', $exception->getErrors());
        }
    }

    public function testMatriculaRejeitaPrecoDeOutraUnidade(): void
    {
        [$alunoId, $planoId] = $this->studentAndPlan('wave2.student@local.test', 'CENTRO');
        $actorId = $this->userId('local@local.test');
        $redeId = $this->redeId();
        $centroId = $this->unidadeId('CENTRO');
        $shoppingId = $this->unidadeId('SHOPPING');

        $adminId = $this->userId('admin@local.test');
        $precoShopping = $this->planoPrecoService()->create($adminId, $redeId, $shoppingId, null, $planoId, [
            'vigencia_inicio' => '2026-01-01',
            'valor_matricula' => '100.00',
            'valor_mensal' => '150.00',
            'status' => 'ATIVO',
        ]);

        try {
            $this->matriculaService()->create($actorId, $redeId, $centroId, null, [
                'aluno_id' => (string) $alunoId,
                'plano_unidade_preco_id' => (string) $precoShopping,
                'data_matricula' => '2026-01-10',
                'data_inicio' => '2026-01-10',
                'status' => 'ATIVA',
            ]);
            self::fail('Preço de outra unidade deveria falhar.');
        } catch (ValidationException $exception) {
            self::assertArrayHasKey('plano_unidade_preco_id', $exception->getErrors());
        }
    }

    public function testAdminLocalNaoAcessaMatriculaDeOutraUnidade(): void
    {
        [$alunoId, $planoId, $precoShopping] = $this->studentAndPlan('wave2.shopping@local.test', 'SHOPPING');
        $adminId = $this->userId('admin@local.test');
        $localId = $this->userId('local@local.test');
        $redeId = $this->redeId();

        $matriculaId = $this->matriculaService()->create($adminId, $redeId, null, null, [
            'aluno_id' => (string) $alunoId,
            'plano_unidade_preco_id' => (string) $precoShopping,
            'data_matricula' => '2026-01-10',
            'data_inicio' => '2026-01-10',
            'status' => 'ATIVA',
        ]);

        try {
            $this->matriculaService()->assertCanAccess(
                $matriculaId,
                $redeId,
                $this->unidadeId('CENTRO'),
                null
            );
            self::fail('Admin Local não deveria acessar matrícula de outra unidade.');
        } catch (ForbiddenException) {
            self::assertTrue(true);
        }
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
            'cpf' => $email === 'wave2.student@local.test' ? '11144477735' : '52998224725',
            'codigo' => 'WAVE2-' . $unidadeId,
            'data_entrada' => '2026-01-01',
            'status' => 'ATIVO',
        ]);

        $planoId = $this->planoService()->create($actorId, $redeId, [
            'nome' => self::PLAN_PREFIX . $unitCode . ' ' . $email,
            'tipo' => 'MENSAL',
            'duracao_meses' => '1',
            'status' => 'ATIVO',
        ], $this->modalityIds(1));

        $precoId = $this->planoPrecoService()->create($actorId, $redeId, $unidadeId, null, $planoId, [
            'vigencia_inicio' => '2026-01-01',
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

        $contractIds = [];

        if ($alunoIds !== []) {
            $placeholders = implode(', ', array_fill(0, count($alunoIds), '?'));
            $contractStmt = $this->pdo->prepare(
                'SELECT id FROM contratos WHERE aluno_id IN (' . $placeholders . ')'
            );
            $contractStmt->execute($alunoIds);
            $contractIds = array_map('intval', $contractStmt->fetchAll(\PDO::FETCH_COLUMN));
        }

        if ($alunoIds !== []) {
            $placeholders = implode(', ', array_fill(0, count($alunoIds), '?'));
            $this->pdo->prepare('DELETE FROM matriculas WHERE aluno_id IN (' . $placeholders . ')')->execute($alunoIds);
        }

        if ($contractIds !== []) {
            $placeholders = implode(', ', array_fill(0, count($contractIds), '?'));
            $this->pdo->prepare('DELETE FROM reajustes WHERE contrato_id IN (' . $placeholders . ')')->execute($contractIds);
            $this->pdo->prepare('DELETE FROM descontos WHERE contrato_id IN (' . $placeholders . ')')->execute($contractIds);
            $this->pdo->prepare('DELETE FROM contratos WHERE id IN (' . $placeholders . ')')->execute($contractIds);
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

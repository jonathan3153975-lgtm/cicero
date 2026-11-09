<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;
use App\Repositories\AlunoRepository;
use App\Repositories\AlunoResponsavelRepository;
use App\Repositories\PessoaContatoRepository;
use App\Repositories\PessoaEnderecoRepository;
use App\Repositories\PessoaRepository;
use App\Repositories\UnidadeRepository;
use App\Services\AlunoService;
use App\Services\PessoaService;

final class PessoaAlunoTest extends IntegrationTestCase
{
    private const EMAILS = [
        'wave1.student@local.test',
        'wave1.responsible@local.test',
        'wave1.other@local.test',
        'wave1.admin@local.test',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanup();
    }

    protected function tearDown(): void
    {
        $this->cleanup();
    }

    public function testUsuariosForamVinculadosARegistrosDePessoa(): void
    {
        self::assertSame(
            0,
            (int) $this->pdo->query('SELECT COUNT(*) FROM usuarios WHERE pessoa_id IS NULL')->fetchColumn()
        );
        self::assertGreaterThanOrEqual(
            8,
            (int) $this->pdo->query('SELECT COUNT(*) FROM pessoas')->fetchColumn()
        );
    }

    public function testPessoaServiceCriaValidaEAtualiza(): void
    {
        $service = $this->pessoaService();
        $redeId = $this->redeId();
        $actorId = $this->userId('local@local.test');

        $id = $service->create([
            'tipo' => 'PF',
            'nome' => 'Aluno Wave 1',
            'cpf' => '52998224725',
            'email_principal' => 'wave1.student@local.test',
            'telefone_principal' => '(51) 99999-0001',
            'cep' => '90000-000',
            'logradouro' => 'Rua Wave',
            'numero' => '10',
            'bairro' => 'Centro',
            'cidade' => 'Porto Alegre',
            'estado' => 'RS',
        ], $redeId, $actorId);

        self::assertGreaterThan(0, $id);
        self::assertNotNull((new PessoaRepository($this->database))->findByCpf('52998224725'));
        self::assertNotEmpty((new PessoaContatoRepository($this->database))->forPessoa($id));
        self::assertNotEmpty((new PessoaEnderecoRepository($this->database))->forPessoa($id));

        $service->update($id, [
            'tipo' => 'PF',
            'nome' => 'Aluno Wave 1 Atualizado',
            'cpf' => '52998224725',
            'email_principal' => 'wave1.student@local.test',
        ], $redeId, $this->unidadeId('CENTRO'), null, $actorId);

        self::assertSame(
            'Aluno Wave 1 Atualizado',
            (new PessoaRepository($this->database))->findById($id)['nome']
        );

        try {
            $service->create([
                'tipo' => 'PF',
                'nome' => 'Duplicado',
                'cpf' => '52998224725',
                'email_principal' => 'wave1.other@local.test',
            ], $redeId, $actorId);
            self::fail('CPF duplicado deveria falhar.');
        } catch (ValidationException $exception) {
            self::assertArrayHasKey('cpf', $exception->getErrors());
        }

        try {
            $service->create([
                'tipo' => 'PF',
                'nome' => 'CPF inválido',
                'cpf' => '12345678900',
                'email_principal' => 'wave1.other@local.test',
            ], $redeId, $actorId);
            self::fail('CPF inválido deveria falhar.');
        } catch (ValidationException $exception) {
            self::assertArrayHasKey('cpf', $exception->getErrors());
        }
    }

    public function testEscopoDePessoasPorUnidade(): void
    {
        $service = $this->pessoaService();
        $adminId = $this->userId('admin@local.test');
        $localId = $this->userId('local@local.test');
        $redeId = $this->redeId();
        $centroId = $this->unidadeId('CENTRO');
        $shoppingId = $this->unidadeId('SHOPPING');

        $adminPersonId = $service->create([
            'tipo' => 'PF',
            'nome' => 'Pessoa do Admin',
            'email_principal' => 'wave1.admin@local.test',
        ], $redeId, $adminId);

        $localPersonId = $service->create([
            'tipo' => 'PF',
            'nome' => 'Pessoa do Local',
            'email_principal' => 'wave1.other@local.test',
        ], $redeId, $localId);

        try {
            $service->assertCanAccess($localId, $adminPersonId, $redeId, $centroId, null);
            self::fail('Admin Local não deveria acessar pessoa criada pelo Superadmin sem vínculo.');
        } catch (ForbiddenException) {
            self::assertTrue(true);
        }

        $centroEmails = array_map(
            static fn (array $pessoa): string => $pessoa['email_principal'] ?? '',
            $service->forScope($redeId, $centroId, null, $localId)
        );
        $receptionId = $this->userId('recepcao@local.test');
        $shoppingEmails = array_map(
            static fn (array $pessoa): string => $pessoa['email_principal'] ?? '',
            $service->forScope($redeId, $shoppingId, null, $receptionId)
        );

        self::assertContains('wave1.other@local.test', $centroEmails);
        self::assertNotContains('wave1.admin@local.test', $centroEmails);
        self::assertNotContains('wave1.other@local.test', $shoppingEmails);

        $service->assertCanAccess($localId, $localPersonId, $redeId, $centroId, null);
        self::assertTrue(true);
    }

    public function testAlunoServiceCriaAlunoComPessoaEResponsavel(): void
    {
        $alunoService = $this->alunoService();
        $actorId = $this->userId('local@local.test');
        $redeId = $this->redeId();
        $centroId = $this->unidadeId('CENTRO');

        $alunoId = $alunoService->create($actorId, $redeId, $centroId, null, [
            'nome' => 'Aluno Wave 1',
            'cpf' => '52998224725',
            'email_principal' => 'wave1.student@local.test',
            'telefone_principal' => '(51) 99999-0001',
            'codigo' => 'WAVE1',
            'data_entrada' => '2026-01-10',
            'status' => 'ATIVO',
        ]);

        $aluno = (new AlunoRepository($this->database))->findById($alunoId);
        self::assertSame('Aluno Wave 1', $aluno['nome']);
        self::assertSame($centroId, (int) $aluno['unidade_id']);

        $responsavelId = $alunoService->addResponsavel(
            $alunoId,
            $actorId,
            $redeId,
            $centroId,
            null,
            [
                'nome' => 'Responsável Wave 1',
                'email_principal' => 'wave1.responsible@local.test',
                'parentesco' => 'Mãe',
                'responsavel_financeiro' => '1',
                'autorizado_buscar' => '1',
            ]
        );

        self::assertGreaterThan(0, $responsavelId);
        $responsaveis = (new AlunoResponsavelRepository($this->database))->forAluno($alunoId);
        self::assertCount(1, $responsaveis);
        self::assertSame('Mãe', $responsaveis[0]['parentesco']);

        $alunoService->removeResponsavel($alunoId, $responsavelId, $redeId, $centroId, null);
        self::assertSame(
            'INATIVO',
            (new AlunoResponsavelRepository($this->database))->findById($responsavelId)['status']
        );

        $centroAlunos = $alunoService->forScope($redeId, $centroId, null);
        $shoppingAlunos = $alunoService->forScope($redeId, $this->unidadeId('SHOPPING'), null);

        self::assertNotEmpty($centroAlunos);
        self::assertSame([], $shoppingAlunos);
    }

    public function testAlunoComPessoaExistenteNaoDuplicaCadastro(): void
    {
        $actorId = $this->userId('local@local.test');
        $redeId = $this->redeId();
        $centroId = $this->unidadeId('CENTRO');

        $pessoaId = $this->pessoaService()->create([
            'tipo' => 'PF',
            'nome' => 'Pessoa Existente Wave 1',
            'cpf' => '52998224725',
            'email_principal' => 'wave1.student@local.test',
        ], $redeId, $actorId);

        $alunoService = $this->alunoService();
        $alunoService->create($actorId, $redeId, $centroId, null, [
            'pessoa_id' => (string) $pessoaId,
            'codigo' => 'WAVE1B',
        ]);

        try {
            $alunoService->create($actorId, $redeId, $centroId, null, [
                'pessoa_id' => (string) $pessoaId,
                'codigo' => 'WAVE1C',
            ]);
            self::fail('A mesma pessoa não deveria ter dois cadastros de aluno.');
        } catch (ValidationException $exception) {
            self::assertArrayHasKey('pessoa_id', $exception->getErrors());
        }
    }

    public function testAdminLocalNaoAcessaAlunoDeOutraUnidade(): void
    {
        $actorId = $this->userId('admin@local.test');
        $localId = $this->userId('local@local.test');
        $redeId = $this->redeId();
        $shoppingId = $this->unidadeId('SHOPPING');

        $alunoId = $this->alunoService()->create($actorId, $redeId, null, null, [
            'nome' => 'Aluno Shopping',
            'email_principal' => 'wave1.other@local.test',
            'unidade_id' => (string) $shoppingId,
            'codigo' => 'WAVE1SHOP',
        ]);

        try {
            $this->alunoService()->assertCanAccess($alunoId, $redeId, $this->unidadeId('CENTRO'), null);
            self::fail('Admin Local não deveria acessar aluno de outra unidade.');
        } catch (ForbiddenException) {
            self::assertTrue(true);
        }

        $localAlunos = $this->alunoService()->forScope($redeId, $this->unidadeId('CENTRO'), null);

        foreach ($localAlunos as $aluno) {
            self::assertNotSame($alunoId, (int) $aluno['id']);
        }
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

    private function cleanup(): void
    {
        if (!isset($this->pdo)) {
            return;
        }

        $placeholders = implode(', ', array_fill(0, count(self::EMAILS), '?'));
        $statement = $this->pdo->prepare('SELECT id FROM pessoas WHERE email_principal IN (' . $placeholders . ')');
        $statement->execute(self::EMAILS);
        $ids = $statement->fetchAll(\PDO::FETCH_COLUMN);

        if ($ids === []) {
            return;
        }

        $idPlaceholders = implode(', ', array_fill(0, count($ids), '?'));

        $this->pdo->prepare(
            'DELETE FROM aluno_responsaveis WHERE responsavel_pessoa_id IN (' . $idPlaceholders . ')'
        )->execute($ids);

        $this->pdo->prepare(
            'DELETE FROM alunos WHERE pessoa_id IN (' . $idPlaceholders . ')'
        )->execute($ids);

        $this->pdo->prepare(
            'DELETE FROM pessoas WHERE id IN (' . $idPlaceholders . ')'
        )->execute($ids);
    }
}

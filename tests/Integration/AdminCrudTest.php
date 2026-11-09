<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;
use App\Repositories\AuditoriaRepository;
use App\Repositories\PerfilRepository;
use App\Repositories\PermissaoRepository;
use App\Repositories\PlanoRedeRepository;
use App\Repositories\RedeRepository;
use App\Repositories\UnidadeRepository;
use App\Repositories\UsuarioRepository;
use App\Repositories\VinculoRepository;
use App\Services\AuditService;
use App\Services\AuthorizationService;
use App\Services\PerfilService;
use App\Services\RedeService;
use App\Services\UnidadeService;
use App\Services\UsuarioService;
use App\Services\VinculoService;
use App\Support\Seeder;

final class AdminCrudTest extends IntegrationTestCase
{
    private const TEST_CNPJ = '11222333000181';
    private const TEST_EMAIL = 'crud@local.test';
    private const TEST_EMAIL_SHOPPING = 'crud.shopping@local.test';
    private const TEST_PROFILE = 'CRUD_TESTE';
    private const TEST_UNIT_CODE = 'CRUD';

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanup();
    }

    protected function tearDown(): void
    {
        $this->cleanup();
    }

    public function testRedeServiceCreatesValidatesAndUpdates(): void
    {
        $service = new RedeService(new RedeRepository($this->database), new PlanoRedeRepository($this->database));

        $id = $service->create($this->redeData());

        self::assertGreaterThan(0, $id);
        self::assertSame('Rede CRUD', (new RedeRepository($this->database))->findByCnpj(self::TEST_CNPJ)['nome_fantasia']);

        $service->update($id, array_merge($this->redeData(), ['nome_fantasia' => 'Rede CRUD Editada']));

        self::assertSame('Rede CRUD Editada', (new RedeRepository($this->database))->findById($id)['nome_fantasia']);

        try {
            $service->create($this->redeData());
            self::fail('CNPJ duplicado deveria falhar.');
        } catch (ValidationException $exception) {
            self::assertArrayHasKey('cnpj', $exception->getErrors());
        }

        try {
            $service->create(array_merge($this->redeData(), ['cnpj' => '11111111111111']));
            self::fail('CNPJ inválido deveria falhar.');
        } catch (ValidationException $exception) {
            self::assertArrayHasKey('cnpj', $exception->getErrors());
        }
    }

    public function testUnidadeServiceCreatesAndUpdates(): void
    {
        $service = new UnidadeService(
            new UnidadeRepository($this->database),
            new RedeRepository($this->database)
        );

        $id = $service->create($this->unidadeData());

        self::assertGreaterThan(0, $id);
        self::assertNotNull((new UnidadeRepository($this->database))->findByRedeAndCode($this->redeId(), self::TEST_UNIT_CODE));

        $service->update($id, array_merge($this->unidadeData(), ['nome' => 'Unidade CRUD Editada']));

        self::assertSame('Unidade CRUD Editada', (new UnidadeRepository($this->database))->findById($id)['nome']);

        try {
            $service->create($this->unidadeData());
            self::fail('Código duplicado deveria falhar.');
        } catch (ValidationException $exception) {
            self::assertArrayHasKey('codigo_interno', $exception->getErrors());
        }
    }

    public function testUsuarioServiceCreatesUserWithMembership(): void
    {
        $service = $this->usuarioService();

        $id = $service->createWithMembership([
            'nome' => 'Usuário CRUD',
            'email' => self::TEST_EMAIL,
            'password' => 'Crud@1234',
            'status' => 'ATIVO',
        ], $this->redeId(), $this->unidadeId('CENTRO'));

        $user = (new UsuarioRepository($this->database))->findByEmail(self::TEST_EMAIL);
        self::assertNotNull($user);
        self::assertSame($id, (int) $user['id']);

        $vinculos = new VinculoRepository($this->database);
        self::assertTrue($vinculos->hasRedeMembership($id, $this->redeId()));
        self::assertTrue($vinculos->hasUnidadeMembership($id, $this->unidadeId('CENTRO')));
    }

    public function testPerfilServiceEnforcesAntiEscalation(): void
    {
        $service = $this->perfilService();
        $actorId = $this->userId('local@local.test');
        $redeId = $this->redeId();
        $unidadeId = $this->unidadeId('CENTRO');

        $id = $service->create($actorId, $redeId, $unidadeId, [
            'slug' => self::TEST_PROFILE,
            'nome' => 'Perfil CRUD',
            'nivel' => 30,
            'escopo' => 'UNIDADE',
            'status' => 'ATIVO',
        ], ['cliente.visualizar', 'dashboard.visualizar', 'contexto.trocar']);

        self::assertGreaterThan(0, $id);
        self::assertNotNull((new PerfilRepository($this->database))->findBySlug(self::TEST_PROFILE));

        $permissions = (new PermissaoRepository($this->database))->forProfile($id);
        self::assertContains('cliente.visualizar', $permissions);

        try {
            $service->create($actorId, $redeId, $unidadeId, [
                'slug' => 'CRUD_ALTO',
                'nome' => 'Perfil Alto',
                'nivel' => 100,
                'escopo' => 'PLATAFORMA',
                'status' => 'ATIVO',
            ], ['rede.criar']);
            self::fail('Nível superior deveria ser bloqueado.');
        } catch (ForbiddenException) {
            self::assertTrue(true);
        }

        try {
            $service->create($actorId, $redeId, $unidadeId, [
                'slug' => 'CRUD_PERMISSAO',
                'nome' => 'Perfil Permissão',
                'nivel' => 30,
                'escopo' => 'UNIDADE',
                'status' => 'ATIVO',
            ], ['rede.criar']);
            self::fail('Permissão não possuída deveria ser bloqueada.');
        } catch (ForbiddenException) {
            self::assertTrue(true);
        }
    }

    public function testVinculoServiceAssignsAndRevokesWithinScope(): void
    {
        $targetId = $this->usuarioService()->createWithMembership([
            'nome' => 'Usuário CRUD',
            'email' => self::TEST_EMAIL,
            'password' => 'Crud@1234',
            'status' => 'ATIVO',
        ], $this->redeId(), $this->unidadeId('CENTRO'));

        $service = $this->vinculoService();
        $actorId = $this->userId('local@local.test');
        $gerenteId = (int) (new PerfilRepository($this->database))->findBySlug('GERENTE')['id'];

        $assignmentId = $service->assignProfileAs(
            $actorId,
            $targetId,
            $gerenteId,
            $this->redeId(),
            $this->unidadeId('CENTRO'),
            null
        );

        $vinculos = new VinculoRepository($this->database);
        self::assertNotNull($vinculos->findAssignmentById($assignmentId));

        try {
            $superadminId = (int) (new PerfilRepository($this->database))->findBySlug('SUPERADMIN')['id'];
            $service->assignProfileAs(
                $actorId,
                $targetId,
                $superadminId,
                $this->redeId(),
                $this->unidadeId('CENTRO'),
                null
            );
            self::fail('Atribuição acima do nível deveria ser bloqueada.');
        } catch (ForbiddenException) {
            self::assertTrue(true);
        }

        $service->revokeAs($actorId, $assignmentId, $this->redeId(), $this->unidadeId('CENTRO'));
        self::assertSame('INATIVO', $vinculos->findAssignmentById($assignmentId)['status']);
    }

    public function testPermissionChangesAreReflectedImmediately(): void
    {
        $recepaoId = $this->userId('recepcao@local.test');
        $perfilId = (int) (new PerfilRepository($this->database))->findBySlug('RECEPCAO')['id'];
        $authorization = $this->authorizationService();

        $before = $authorization->permissionsForContext($recepaoId, $this->redeId(), $this->unidadeId('CENTRO'));
        self::assertContains('cliente.criar', $before);

        $perfilRepository = new PerfilRepository($this->database);
        $permissionRepository = new PermissaoRepository($this->database);
        $current = $permissionRepository->forProfile($perfilId);
        $without = array_values(array_filter($current, static fn (string $slug): bool => $slug !== 'cliente.criar'));
        $perfilRepository->syncPermissions($perfilId, array_values($permissionRepository->idsBySlugs($without)));

        $after = $authorization->permissionsForContext($recepaoId, $this->redeId(), $this->unidadeId('CENTRO'));
        self::assertNotContains('cliente.criar', $after);

        (new Seeder($this->pdo, base_path('database/seeds/data')))->run();
    }

    public function testScopeIsolationBetweenUnits(): void
    {
        $shoppingUserId = $this->usuarioService()->createWithMembership([
            'nome' => 'Usuário Shopping',
            'email' => self::TEST_EMAIL_SHOPPING,
            'password' => 'Crud@1234',
            'status' => 'ATIVO',
        ], $this->redeId(), $this->unidadeId('SHOPPING'));

        $vinculos = new VinculoRepository($this->database);
        $funcionarioId = (int) (new PerfilRepository($this->database))->findBySlug('FUNCIONARIO')['id'];

        $this->vinculoService()->assignProfileAs(
            $this->userId('admin@local.test'),
            $shoppingUserId,
            $funcionarioId,
            $this->redeId(),
            $this->unidadeId('SHOPPING'),
            null
        );

        self::assertFalse($vinculos->userInScope($shoppingUserId, $this->redeId(), $this->unidadeId('CENTRO')));
        self::assertTrue($vinculos->userInScope($shoppingUserId, $this->redeId(), $this->unidadeId('SHOPPING')));

        $centroAssignments = $vinculos->allAssignmentsForScope($this->redeId(), $this->unidadeId('CENTRO'));
        $shoppingAssignments = $vinculos->allAssignmentsForScope($this->redeId(), $this->unidadeId('SHOPPING'));

        foreach ($centroAssignments as $assignment) {
            self::assertNotSame($shoppingUserId, (int) $assignment['usuario_id']);
        }

        $foundInShopping = false;

        foreach ($shoppingAssignments as $assignment) {
            if ((int) $assignment['usuario_id'] === $shoppingUserId) {
                $foundInShopping = true;
                break;
            }
        }

        self::assertTrue($foundInShopping);
    }

    public function testLocalAdminCannotManageHigherLevelOrRegionalUsers(): void
    {
        $service = $this->usuarioService();
        $actorId = $this->userId('local@local.test');
        $redeId = $this->redeId();
        $unidadeId = $this->unidadeId('CENTRO');

        $service->assertCanManage($actorId, $this->userId('gerente@local.test'), $redeId, $unidadeId);
        self::assertTrue(true);

        try {
            $service->assertCanManage($actorId, $this->userId('regional@local.test'), $redeId, $unidadeId);
            self::fail('Admin Local não deveria gerenciar o Admin Regional.');
        } catch (ForbiddenException) {
            self::assertTrue(true);
        }

        try {
            $service->assertCanManage($actorId, $this->userId('admin@local.test'), $redeId, $unidadeId);
            self::fail('Admin Local não deveria gerenciar o Superadmin.');
        } catch (ForbiddenException) {
            self::assertTrue(true);
        }

        $service->assertCanManage($this->userId('admin@local.test'), $this->userId('regional@local.test'), null, null);
        self::assertTrue(true);
    }

    public function testUnitAuditExcludesRedeLevelActions(): void
    {
        $repository = new AuditoriaRepository($this->database);
        $redeId = $this->redeId();
        $unidadeId = $this->unidadeId('CENTRO');
        $actorId = $this->userId('local@local.test');

        $repository->record([
            'usuario_id' => $actorId,
            'rede_id' => $redeId,
            'unidade_id' => $unidadeId,
            'acao' => 'teste.unidade',
            'entidade' => 'teste_escopo',
            'dados_posteriores' => ['ok' => true],
        ]);

        $repository->record([
            'usuario_id' => $actorId,
            'rede_id' => $redeId,
            'unidade_id' => null,
            'acao' => 'teste.rede',
            'entidade' => 'teste_escopo',
            'dados_posteriores' => ['ok' => true],
        ]);

        $unitActions = array_map(
            static fn (array $row): string => $row['acao'],
            $repository->latestForScope($redeId, $unidadeId, null, 50)
        );
        $redeActions = array_map(
            static fn (array $row): string => $row['acao'],
            $repository->latestForScope($redeId, null, null, 50)
        );

        self::assertContains('teste.unidade', $unitActions);
        self::assertNotContains('teste.rede', $unitActions);
        self::assertContains('teste.rede', $redeActions);
    }

    public function testLocalAdminProfileListIsFilteredByLevel(): void
    {
        $service = $this->perfilService();
        $actorId = $this->userId('local@local.test');
        $redeId = $this->redeId();
        $unidadeId = $this->unidadeId('CENTRO');

        $visible = $service->visibleProfiles($actorId, $redeId, $unidadeId);
        $slugs = array_map(static fn (array $profile): string => $profile['slug'], $visible);

        self::assertContains('GERENTE', $slugs);
        self::assertNotContains('SUPERADMIN', $slugs);
        self::assertNotContains('ADMIN_REGIONAL', $slugs);

        $gerente = null;

        foreach ($visible as $profile) {
            if ($profile['slug'] === 'GERENTE') {
                $gerente = $profile;
                break;
            }
        }

        self::assertNotNull($gerente);
        self::assertFalse((bool) $gerente['can_edit']);

        $customId = $service->create($actorId, $redeId, $unidadeId, [
            'slug' => 'CRUD_SCOPE',
            'nome' => 'Perfil Escopo CRUD',
            'nivel' => 30,
            'escopo' => 'UNIDADE',
            'status' => 'ATIVO',
        ], ['cliente.visualizar', 'dashboard.visualizar', 'contexto.trocar']);

        $custom = (new PerfilRepository($this->database))->findById($customId);
        self::assertSame($redeId, (int) $custom['rede_id']);

        $customVisible = null;

        foreach ($service->visibleProfiles($actorId, $redeId, $unidadeId) as $profile) {
            if ($profile['slug'] === 'CRUD_SCOPE') {
                $customVisible = $profile;
                break;
            }
        }

        self::assertNotNull($customVisible);
        self::assertTrue((bool) $customVisible['can_edit']);
        $service->assertCanManage($actorId, $customId, $redeId, $unidadeId);
        self::assertTrue(true);

        try {
            $service->assertCanManage(
                $actorId,
                (int) (new PerfilRepository($this->database))->findBySlug('SUPERADMIN')['id'],
                $redeId,
                $unidadeId
            );
            self::fail('Admin Local não deveria gerenciar o perfil Superadmin.');
        } catch (ForbiddenException) {
            self::assertTrue(true);
        }

        try {
            $service->assertCanManage($actorId, (int) $gerente['id'], $redeId, $unidadeId);
            self::fail('Admin Local não deveria editar perfil de sistema.');
        } catch (ForbiddenException) {
            self::assertTrue(true);
        }
    }

    public function testGrantableProfilesRespectGrantMatrix(): void
    {
        $service = $this->perfilService();
        $redeId = $this->redeId();
        $unidadeId = $this->unidadeId('CENTRO');
        $regiaoId = $this->regiaoId();

        $localSlugs = array_map(
            static fn (array $profile): string => $profile['slug'],
            $service->grantableProfiles($this->userId('local@local.test'), $redeId, $unidadeId)
        );

        self::assertContains('GERENTE', $localSlugs);
        self::assertNotContains('ADMIN_LOCAL', $localSlugs);
        self::assertNotContains('ADMIN_REGIONAL', $localSlugs);
        self::assertNotContains('SUPERADMIN', $localSlugs);

        $regionalSlugs = array_map(
            static fn (array $profile): string => $profile['slug'],
            $service->grantableProfiles($this->userId('regional@local.test'), $redeId, null, $regiaoId)
        );

        self::assertContains('ADMIN_LOCAL', $regionalSlugs);
        self::assertNotContains('ADMIN_REGIONAL', $regionalSlugs);
        self::assertNotContains('SUPERADMIN', $regionalSlugs);

        $superadminSlugs = array_map(
            static fn (array $profile): string => $profile['slug'],
            $service->grantableProfiles($this->userId('admin@local.test'), $redeId, $unidadeId)
        );

        self::assertContains('ADMIN_LOCAL', $superadminSlugs);
        self::assertContains('ADMIN_REGIONAL', $superadminSlugs);
        self::assertContains('SUPERADMIN', $superadminSlugs);
    }

    public function testRegionalScopeFiltersUnitsUsersAndAssignments(): void
    {
        $redeId = $this->redeId();
        $regiaoId = $this->regiaoId();

        $unitCodes = array_map(
            static fn (array $unit): string => $unit['codigo_interno'],
            (new UnidadeRepository($this->database))->forScope($redeId, null, $regiaoId)
        );

        self::assertContains('CENTRO', $unitCodes);
        self::assertContains('SHOPPING', $unitCodes);

        $userEmails = array_map(
            static fn (array $user): string => $user['email'],
            (new UsuarioRepository($this->database))->forScope($redeId, null, $regiaoId)
        );

        self::assertContains('local@local.test', $userEmails);
        self::assertContains('gerente@local.test', $userEmails);
        self::assertNotContains('admin@local.test', $userEmails);

        $assignmentEmails = array_map(
            static fn (array $assignment): string => $assignment['usuario_email'],
            (new VinculoRepository($this->database))->allAssignmentsForScope($redeId, null, $regiaoId)
        );

        self::assertContains('regional@local.test', $assignmentEmails);
        self::assertContains('local@local.test', $assignmentEmails);
    }

    public function testRegionAuditScopeIncludesOnlyRegionUnits(): void
    {
        $repository = new AuditoriaRepository($this->database);
        $redeId = $this->redeId();
        $regiaoId = $this->regiaoId();
        $actorId = $this->userId('regional@local.test');

        foreach (['CENTRO' => 'teste.regiao.centro', 'SHOPPING' => 'teste.regiao.shopping'] as $code => $action) {
            $repository->record([
                'usuario_id' => $actorId,
                'rede_id' => $redeId,
                'unidade_id' => $this->unidadeId($code),
                'acao' => $action,
                'entidade' => 'teste_regiao',
                'dados_posteriores' => ['ok' => true],
            ]);
        }

        $repository->record([
            'usuario_id' => $actorId,
            'rede_id' => $redeId,
            'unidade_id' => null,
            'acao' => 'teste.regiao.rede',
            'entidade' => 'teste_regiao',
            'dados_posteriores' => ['ok' => true],
        ]);

        $actions = array_map(
            static fn (array $row): string => $row['acao'],
            $repository->latestForScope($redeId, null, $regiaoId, 50)
        );

        self::assertContains('teste.regiao.centro', $actions);
        self::assertContains('teste.regiao.shopping', $actions);
        self::assertNotContains('teste.regiao.rede', $actions);
    }

    public function testAuditServiceRecordsAndScopesEntries(): void
    {
        $service = new AuditService(new AuditoriaRepository($this->database));

        $service->record(
            $this->userId('local@local.test'),
            'teste.auditoria',
            'teste_auditoria',
            1,
            null,
            ['ok' => true],
            $this->redeId(),
            $this->unidadeId('CENTRO'),
            '127.0.0.1',
            'phpunit'
        );

        $rows = (new AuditoriaRepository($this->database))->latestForScope(
            $this->redeId(),
            $this->unidadeId('CENTRO'),
            null,
            10
        );

        $found = false;

        foreach ($rows as $row) {
            if ($row['acao'] === 'teste.auditoria') {
                $found = true;
                break;
            }
        }

        self::assertTrue($found);
    }

    private function usuarioService(): UsuarioService
    {
        return new UsuarioService(
            new UsuarioRepository($this->database),
            new VinculoRepository($this->database),
            new UnidadeRepository($this->database),
            $this->authorizationService()
        );
    }

    private function authorizationService(): AuthorizationService
    {
        return new AuthorizationService(
            new VinculoRepository($this->database),
            new PermissaoRepository($this->database),
            new PerfilRepository($this->database),
            new UnidadeRepository($this->database)
        );
    }

    private function perfilService(): PerfilService
    {
        return new PerfilService(
            new PerfilRepository($this->database),
            new PermissaoRepository($this->database),
            $this->authorizationService()
        );
    }

    private function vinculoService(): VinculoService
    {
        return new VinculoService(
            new VinculoRepository($this->database),
            new UnidadeRepository($this->database),
            new \App\Repositories\RegiaoRepository($this->database),
            new RedeRepository($this->database),
            new PerfilRepository($this->database),
            $this->authorizationService()
        );
    }

    private function redeData(): array
    {
        return [
            'razao_social' => 'Rede CRUD LTDA',
            'nome_fantasia' => 'Rede CRUD',
            'cnpj' => self::TEST_CNPJ,
            'email' => 'crud@demo.local',
            'telefone' => '(51) 0000-0000',
            'cep' => '90000-000',
            'logradouro' => 'Rua CRUD',
            'numero' => '1',
            'complemento' => '',
            'bairro' => 'Centro',
            'cidade' => 'Porto Alegre',
            'estado' => 'RS',
            'status' => 'ATIVA',
            'plano_contratado' => 'TESTE',
        ];
    }

    private function unidadeData(): array
    {
        return [
            'rede_id' => $this->redeId(),
            'nome' => 'Unidade CRUD',
            'codigo_interno' => self::TEST_UNIT_CODE,
            'situacao' => 'ATIVA',
            'cep' => '90000-000',
            'logradouro' => 'Rua CRUD',
            'numero' => '2',
            'complemento' => '',
            'bairro' => 'Centro',
            'cidade' => 'Porto Alegre',
            'estado' => 'RS',
            'telefone' => '(51) 0000-0000',
            'whatsapp' => '(51) 90000-0000',
            'email' => 'unidade@crud.local',
            'data_abertura' => '2026-01-01',
            'horario_funcionamento' => '06:00-22:00',
            'capacidade_maxima' => '100',
            'metragem' => '500.00',
        ];
    }

    private function cleanup(): void
    {
        foreach ([self::TEST_EMAIL, self::TEST_EMAIL_SHOPPING] as $email) {
            $this->pdo->prepare(
                'DELETE up FROM usuario_perfil up INNER JOIN usuarios u ON u.id = up.usuario_id WHERE u.email = :email'
            )->execute(['email' => $email]);
            $this->pdo->prepare(
                'DELETE ur FROM usuario_rede ur INNER JOIN usuarios u ON u.id = ur.usuario_id WHERE u.email = :email'
            )->execute(['email' => $email]);
            $this->pdo->prepare(
                'DELETE uu FROM usuario_unidade uu INNER JOIN usuarios u ON u.id = uu.usuario_id WHERE u.email = :email'
            )->execute(['email' => $email]);
            $this->pdo->prepare('DELETE FROM usuarios WHERE email = :email')->execute(['email' => $email]);
        }

        foreach ([self::TEST_PROFILE, 'CRUD_SCOPE'] as $slug) {
            $this->pdo->prepare(
                'DELETE up FROM usuario_perfil up INNER JOIN perfis p ON p.id = up.perfil_id WHERE p.slug = :slug'
            )->execute(['slug' => $slug]);
            $this->pdo->prepare('DELETE FROM perfis WHERE slug = :slug')->execute(['slug' => $slug]);
        }

        $this->pdo->prepare(
            'DELETE FROM usuario_unidade WHERE unidade_id IN (SELECT id FROM unidades WHERE codigo_interno = :code)'
        )->execute(['code' => self::TEST_UNIT_CODE]);
        $this->pdo->prepare(
            'DELETE FROM regiao_unidade WHERE unidade_id IN (SELECT id FROM unidades WHERE codigo_interno = :code)'
        )->execute(['code' => self::TEST_UNIT_CODE]);
        $this->pdo->prepare('DELETE FROM unidades WHERE codigo_interno = :code')->execute(['code' => self::TEST_UNIT_CODE]);

        $this->pdo->prepare(
            'DELETE FROM regiao_unidade WHERE regiao_id IN (SELECT id FROM regioes WHERE rede_id IN'
            . ' (SELECT id FROM redes WHERE cnpj = :cnpj))'
        )->execute(['cnpj' => self::TEST_CNPJ]);
        $this->pdo->prepare(
            'DELETE FROM regioes WHERE rede_id IN (SELECT id FROM redes WHERE cnpj = :cnpj)'
        )->execute(['cnpj' => self::TEST_CNPJ]);
        $this->pdo->prepare(
            'DELETE FROM unidades WHERE rede_id IN (SELECT id FROM redes WHERE cnpj = :cnpj)'
        )->execute(['cnpj' => self::TEST_CNPJ]);
        $this->pdo->prepare('DELETE FROM redes WHERE cnpj = :cnpj')->execute(['cnpj' => self::TEST_CNPJ]);

        $this->pdo->exec("DELETE FROM auditoria WHERE entidade IN ('teste_auditoria', 'teste_escopo', 'teste_regiao')");
    }
}

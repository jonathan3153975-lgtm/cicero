<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Repositories\PerfilRepository;
use App\Repositories\PermissaoRepository;
use App\Repositories\UnidadeRepository;
use App\Repositories\VinculoRepository;
use App\Services\AuthorizationService;

final class AuthorizationServiceTest extends IntegrationTestCase
{
    private AuthorizationService $authorization;

    private PerfilRepository $perfis;

    protected function setUp(): void
    {
        parent::setUp();

        $this->perfis = new PerfilRepository($this->database);

        $this->authorization = new AuthorizationService(
            new VinculoRepository($this->database),
            new PermissaoRepository($this->database),
            $this->perfis,
            new UnidadeRepository($this->database)
        );
    }

    public function testSuperadminHasGlobalPermissions(): void
    {
        $permissions = $this->authorization->permissionsForContext(
            $this->userId('admin@local.test'),
            null,
            null
        );

        self::assertContains('rede.criar', $permissions);
        self::assertContains('configuracao.visualizar', $permissions);
        self::assertContains('financeiro.editar', $permissions);
    }

    public function testReceptionHasOnlyItsProfilePermissions(): void
    {
        $permissions = $this->authorization->permissionsForContext(
            $this->userId('recepcao@local.test'),
            $this->redeId(),
            $this->unidadeId('CENTRO')
        );

        self::assertContains('cliente.criar', $permissions);
        self::assertContains('dashboard.visualizar', $permissions);
        self::assertNotContains('financeiro.visualizar', $permissions);
        self::assertNotContains('configuracao.visualizar', $permissions);
    }

    public function testPermissionsAreScopedToTheAssignedUnit(): void
    {
        $userId = $this->userId('local@local.test');

        $inCentro = $this->authorization->permissionsForContext(
            $userId,
            $this->redeId(),
            $this->unidadeId('CENTRO')
        );
        $inShopping = $this->authorization->permissionsForContext(
            $userId,
            $this->redeId(),
            $this->unidadeId('SHOPPING')
        );

        self::assertContains('financeiro.editar', $inCentro);
        self::assertNotContains('financeiro.editar', $inShopping);
        self::assertSame([], $inShopping);
    }

    public function testRegionalPermissionsApplyToUnitsOfTheRegion(): void
    {
        $userId = $this->userId('regional@local.test');

        foreach (['CENTRO', 'SHOPPING'] as $code) {
            $permissions = $this->authorization->permissionsForContext(
                $userId,
                $this->redeId(),
                $this->unidadeId($code)
            );

            self::assertContains('unidade.editar', $permissions);
        }
    }

    public function testRegionalPermissionsInRegionContext(): void
    {
        $permissions = $this->authorization->permissionsForContext(
            $this->userId('regional@local.test'),
            $this->redeId(),
            null,
            $this->regiaoId()
        );

        self::assertContains('unidade.editar', $permissions);
        self::assertContains('unidade.criar', $permissions);
        self::assertContains('usuario.criar', $permissions);
        self::assertNotContains('rede.criar', $permissions);
        self::assertNotContains('regiao.criar', $permissions);
    }

    public function testRegionalCanGrantLocalAdminProfile(): void
    {
        $adminLocalId = (int) $this->perfis->findBySlug('ADMIN_LOCAL')['id'];

        self::assertTrue($this->authorization->canGrantProfile(
            $this->userId('regional@local.test'),
            $adminLocalId,
            $this->redeId(),
            $this->unidadeId('CENTRO')
        ));
    }

    public function testPeoplePermissionsAreAssignedToOperationalProfiles(): void
    {
        $localPermissions = $this->authorization->permissionsForContext(
            $this->userId('local@local.test'),
            $this->redeId(),
            $this->unidadeId('CENTRO')
        );
        $receptionPermissions = $this->authorization->permissionsForContext(
            $this->userId('recepcao@local.test'),
            $this->redeId(),
            $this->unidadeId('CENTRO')
        );
        $employeePermissions = $this->authorization->permissionsForContext(
            $this->userId('funcionario@local.test'),
            $this->redeId(),
            $this->unidadeId('CENTRO')
        );

        self::assertContains('pessoa.criar', $localPermissions);
        self::assertContains('aluno.criar', $localPermissions);
        self::assertContains('aluno.criar', $receptionPermissions);
        self::assertNotContains('aluno.criar', $employeePermissions);
    }

    public function testItBlocksEqualLevelGrantsForNonSuperadmin(): void
    {
        $adminLocalId = (int) $this->perfis->findBySlug('ADMIN_LOCAL')['id'];
        $adminRegionalId = (int) $this->perfis->findBySlug('ADMIN_REGIONAL')['id'];

        self::assertFalse($this->authorization->canGrantProfile(
            $this->userId('local@local.test'),
            $adminLocalId,
            $this->redeId(),
            $this->unidadeId('CENTRO')
        ));

        self::assertFalse($this->authorization->canGrantProfile(
            $this->userId('regional@local.test'),
            $adminRegionalId,
            $this->redeId(),
            $this->unidadeId('CENTRO')
        ));

        self::assertTrue($this->authorization->canGrantProfile(
            $this->userId('admin@local.test'),
            $adminRegionalId,
            $this->redeId(),
            $this->unidadeId('CENTRO')
        ));
    }

    public function testItPreventsPrivilegeEscalation(): void
    {
        $gerenteId = (int) $this->perfis->findBySlug('GERENTE')['id'];
        $superadminId = (int) $this->perfis->findBySlug('SUPERADMIN')['id'];

        self::assertFalse($this->authorization->canGrantProfile(
            $this->userId('recepcao@local.test'),
            $gerenteId,
            $this->redeId(),
            $this->unidadeId('CENTRO')
        ));

        self::assertFalse($this->authorization->canGrantProfile(
            $this->userId('recepcao@local.test'),
            $superadminId,
            $this->redeId(),
            $this->unidadeId('CENTRO')
        ));

        self::assertTrue($this->authorization->canGrantProfile(
            $this->userId('local@local.test'),
            $gerenteId,
            $this->redeId(),
            $this->unidadeId('CENTRO')
        ));
    }
}

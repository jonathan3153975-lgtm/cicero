<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Exceptions\ForbiddenException;
use App\Repositories\RedeRepository;
use App\Repositories\RegiaoRepository;
use App\Repositories\UnidadeRepository;
use App\Repositories\VinculoRepository;
use App\Services\TenantContextService;

final class TenantContextServiceTest extends IntegrationTestCase
{
    private TenantContextService $contexts;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contexts = new TenantContextService(
            new VinculoRepository($this->database),
            new UnidadeRepository($this->database),
            new RedeRepository($this->database),
            new RegiaoRepository($this->database)
        );
    }

    public function testRegionalUserCanAccessRegionAndUnitsOfTheRegion(): void
    {
        $contexts = $this->contexts->contextsForUser($this->userId('regional@local.test'));

        self::assertCount(3, $contexts);

        $types = array_map(static fn (array $context): string => $context['tipo'], $contexts);
        self::assertContains('REGIAO', $types);
        self::assertStringContainsString('Centro', implode(' | ', array_column($contexts, 'label')));
        self::assertStringContainsString('Shopping', implode(' | ', array_column($contexts, 'label')));
    }

    public function testItResolvesTheRegionContext(): void
    {
        $context = $this->contexts->resolve(
            $this->userId('regional@local.test'),
            $this->redeId(),
            null,
            $this->regiaoId()
        );

        self::assertSame('REGIAO', $context['tipo']);
        self::assertSame($this->regiaoId(), $context['regiao_id']);
    }

    public function testLocalUserHasOnlyItsUnit(): void
    {
        $contexts = $this->contexts->contextsForUser($this->userId('local@local.test'));

        self::assertCount(1, $contexts);
        self::assertSame($this->unidadeId('CENTRO'), $contexts[0]['unidade_id']);
    }

    public function testSuperadminHasPlatformAndUnitContexts(): void
    {
        $contexts = $this->contexts->contextsForUser($this->userId('admin@local.test'));

        self::assertGreaterThanOrEqual(4, count($contexts));
        self::assertSame('PLATAFORMA', $contexts[0]['tipo']);
    }

    public function testItRejectsAContextOutsideTheUsersScope(): void
    {
        $this->expectException(ForbiddenException::class);

        $this->contexts->resolve(
            $this->userId('local@local.test'),
            $this->redeId(),
            $this->unidadeId('SHOPPING')
        );
    }

    public function testItResolvesAValidRegionalUnitContext(): void
    {
        $context = $this->contexts->resolve(
            $this->userId('regional@local.test'),
            $this->redeId(),
            $this->unidadeId('SHOPPING')
        );

        self::assertSame($this->unidadeId('SHOPPING'), $context['unidade_id']);
        self::assertSame($this->redeId(), $context['rede_id']);
    }
}

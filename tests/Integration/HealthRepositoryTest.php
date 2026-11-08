<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Repositories\HealthRepository;
use App\Support\Config;
use App\Support\Database;
use App\Support\MigrationRunner;
use PHPUnit\Framework\TestCase;
use Throwable;

final class HealthRepositoryTest extends TestCase
{
    private ?HealthRepository $repository = null;

    protected function setUp(): void
    {
        $config = new Config(base_path('config'));
        $config->load();

        $database = new Database($config);

        try {
            $pdo = $database->connection();
            (new MigrationRunner($pdo, base_path('database/migrations')))->migrate();
        } catch (Throwable $exception) {
            self::markTestSkipped('Banco de dados indisponível: ' . $exception->getMessage());
        }

        $this->repository = new HealthRepository($database);
    }

    public function testItPersistsAndRetrievesARecord(): void
    {
        $before = $this->repository?->count() ?? 0;

        $id = $this->repository?->record('teste de integração');

        self::assertGreaterThan(0, (int) $id);
        self::assertSame($before + 1, $this->repository?->count());

        $latest = $this->repository?->latest();

        self::assertIsArray($latest);
        self::assertSame('teste de integração', $latest['note']);
    }
}

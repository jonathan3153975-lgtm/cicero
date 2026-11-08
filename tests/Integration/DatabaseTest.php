<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Support\Config;
use App\Support\Database;
use PDO;
use PHPUnit\Framework\TestCase;
use Throwable;

final class DatabaseTest extends TestCase
{
    private ?Database $database = null;

    protected function setUp(): void
    {
        $config = new Config(base_path('config'));
        $config->load();

        $database = new Database($config);

        try {
            $database->connection();
        } catch (Throwable $exception) {
            self::markTestSkipped('Banco de dados indisponível: ' . $exception->getMessage());
        }

        $this->database = $database;
    }

    public function testItReturnsAPdoConnection(): void
    {
        self::assertInstanceOf(PDO::class, $this->database?->connection());
    }

    public function testItRunsASimpleQuery(): void
    {
        $value = $this->database?->connection()->query('SELECT 1')->fetchColumn();

        self::assertSame('1', (string) $value);
    }
}

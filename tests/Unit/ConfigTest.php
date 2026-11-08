<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Config;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testItReadsNestedValuesWithDotNotation(): void
    {
        $config = new Config();
        $config->set('app.name', 'Plataforma de Teste');
        $config->set('database.host', '127.0.0.1');

        self::assertSame('Plataforma de Teste', $config->get('app.name'));
        self::assertSame('127.0.0.1', $config->get('database.host'));
    }

    public function testItReturnsDefaultForMissingKeys(): void
    {
        $config = new Config();

        self::assertSame('padrão', $config->get('missing.key', 'padrão'));
    }

    public function testItLoadsPhpFilesFromDirectory(): void
    {
        $config = new Config(base_path('config'));
        $config->load();

        self::assertSame('mysql', $config->get('database.driver'));
        self::assertIsArray($config->get('app'));
    }
}

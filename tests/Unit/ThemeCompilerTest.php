<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\ThemeCompiler;
use PHPUnit\Framework\TestCase;

final class ThemeCompilerTest extends TestCase
{
    public function testItCompilesValidBrandColors(): void
    {
        $css = ThemeCompiler::cssVariables([
            'primary' => '#F55800',
            'accent' => '#E0FF51',
            'background' => 'invalid',
        ]);

        self::assertStringContainsString('--brand-primary:#F55800', $css);
        self::assertStringContainsString('--brand-accent:#E0FF51', $css);
        self::assertStringNotContainsString('--color-background', $css);
    }

    public function testItRejectsArbitraryCss(): void
    {
        self::assertFalse(ThemeCompiler::isValidColor('red;background:url(evil)'));
        self::assertTrue(ThemeCompiler::isValidColor('#abc'));
        self::assertTrue(ThemeCompiler::isValidColor('#A1B2C3'));
    }
}

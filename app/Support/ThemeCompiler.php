<?php

declare(strict_types=1);

namespace App\Support;

final class ThemeCompiler
{
    private const MAP = [
        'primary' => '--brand-primary',
        'primary_hover' => '--brand-primary-hover',
        'accent' => '--brand-accent',
        'background' => '--color-background',
        'surface' => '--color-surface',
        'surface_elevated' => '--color-surface-elevated',
        'text' => '--color-text',
        'text_muted' => '--color-text-muted',
        'border' => '--color-border',
    ];

    public static function cssVariables(array $brand): string
    {
        $declarations = [];

        foreach (self::MAP as $key => $variable) {
            $value = $brand[$key] ?? null;

            if (is_string($value) && self::isValidColor($value)) {
                $declarations[] = $variable . ':' . $value;
            }
        }

        return $declarations === [] ? '' : implode(';', $declarations) . ';';
    }

    public static function isValidColor(string $value): bool
    {
        return (bool) preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value);
    }
}

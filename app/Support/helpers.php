<?php

declare(strict_types=1);

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $base = dirname(__DIR__, 2);

        return $path === ''
            ? $base
            : $base . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return base_path('storage' . ($path === '' ? '' : DIRECTORY_SEPARATOR . ltrim($path, '/\\')));
    }
}

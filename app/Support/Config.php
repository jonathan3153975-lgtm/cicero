<?php

declare(strict_types=1);

namespace App\Support;

final class Config
{
    private array $items = [];

    public function __construct(private readonly string $path = '')
    {
    }

    public function load(): void
    {
        if ($this->path === '' || !is_dir($this->path)) {
            throw new \RuntimeException('Diretório de configuração inválido.');
        }

        foreach (glob($this->path . DIRECTORY_SEPARATOR . '*.php') ?: [] as $file) {
            $key = basename($file, '.php');
            $this->items[$key] = require $file;
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->items;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    public function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $target = &$this->items;

        foreach ($segments as $segment) {
            if (!isset($target[$segment]) || !is_array($target[$segment])) {
                $target[$segment] = [];
            }

            $target = &$target[$segment];
        }

        $target = $value;
    }

    public function all(): array
    {
        return $this->items;
    }
}

<?php

declare(strict_types=1);

namespace App\Support;

final class RouteResult
{
    public const NOT_FOUND = 0;
    public const METHOD_NOT_ALLOWED = 1;
    public const FOUND = 2;

    private function __construct(
        public readonly int $status,
        public readonly array $handler = [],
        public readonly array $vars = [],
        public readonly array $allowedMethods = [],
        public readonly array $middleware = []
    ) {
    }

    public static function found(array $data, array $vars): self
    {
        return new self(
            self::FOUND,
            $data['handler'] ?? [],
            $vars,
            [],
            $data['middleware'] ?? []
        );
    }

    public static function notFound(): self
    {
        return new self(self::NOT_FOUND);
    }

    public static function methodNotAllowed(array $allowedMethods): self
    {
        return new self(self::METHOD_NOT_ALLOWED, [], [], $allowedMethods);
    }
}

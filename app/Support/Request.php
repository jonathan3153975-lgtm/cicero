<?php

declare(strict_types=1);

namespace App\Support;

final class Request
{
    private array $routeParams = [];

    public function __construct(
        public readonly string $method,
        public readonly string $path,
        private readonly array $query = [],
        private readonly array $body = [],
        private readonly array $server = [],
        private readonly array $files = []
    ) {
    }

    public static function capture(): self
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);

        return new self(
            $method,
            is_string($path) && $path !== '' ? $path : '/',
            $_GET,
            $_POST,
            $_SERVER,
            $_FILES
        );
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function files(): array
    {
        return $this->files;
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

        if (isset($this->server[$key])) {
            return (string) $this->server[$key];
        }

        if (strcasecmp($name, 'Content-Type') === 0 && isset($this->server['CONTENT_TYPE'])) {
            return (string) $this->server['CONTENT_TYPE'];
        }

        return null;
    }

    public function isMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }

    public function isApi(): bool
    {
        return str_starts_with($this->path, '/api/');
    }

    public function wantsJson(): bool
    {
        if ($this->isApi()) {
            return true;
        }

        $accept = $this->header('Accept') ?? '';

        return str_contains($accept, 'application/json')
            || strtolower($this->header('X-Requested-With') ?? '') === 'xmlhttprequest';
    }

    public function ip(): string
    {
        return (string) ($this->server['REMOTE_ADDR'] ?? '127.0.0.1');
    }

    public function userAgent(): string
    {
        return (string) ($this->server['HTTP_USER_AGENT'] ?? '');
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function routeParams(): array
    {
        return $this->routeParams;
    }

    public function routeParam(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }
}

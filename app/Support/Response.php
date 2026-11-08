<?php

declare(strict_types=1);

namespace App\Support;

final class Response
{
    public function __construct(
        private int $status = 200,
        private array $headers = [],
        private string $body = ''
    ) {
    }

    public static function html(string $body, int $status = 200): self
    {
        return new self($status, ['Content-Type' => 'text/html; charset=UTF-8'], $body);
    }

    public static function json(array $payload, int $status = 200): self
    {
        $body = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );

        return new self($status, ['Content-Type' => 'application/json; charset=UTF-8'], $body);
    }

    public static function redirect(string $location, int $status = 302): self
    {
        return new self($status, ['Location' => $location]);
    }

    public static function noContent(): self
    {
        return new self(204);
    }

    public function withHeader(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->headers[$name] = $value;

        return $clone;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function header(string $name): ?string
    {
        foreach ($this->headers as $key => $value) {
            if (strcasecmp($key, $name) === 0) {
                return $value;
            }
        }

        return null;
    }

    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->status);

            foreach ($this->headers as $name => $value) {
                header(sprintf('%s: %s', $name, $value));
            }
        }

        echo $this->body;
    }
}

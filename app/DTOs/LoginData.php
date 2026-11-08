<?php

declare(strict_types=1);

namespace App\DTOs;

final class LoginData
{
    public function __construct(
        public readonly string $email,
        public readonly string $password
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            trim((string) ($data['email'] ?? '')),
            (string) ($data['password'] ?? '')
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Support;

final class Csrf
{
    public function __construct(
        private readonly Session $session,
        private readonly Config $config
    ) {
    }

    public function name(): string
    {
        return (string) $this->config->get('security.csrf_token_name', '_token');
    }

    public function token(): string
    {
        $token = $this->session->get('_csrf_token');

        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            $this->session->set('_csrf_token', $token);
        }

        return $token;
    }

    public function rotate(): void
    {
        $this->session->set('_csrf_token', bin2hex(random_bytes(32)));
    }

    public function validate(?string $token): bool
    {
        if (!is_string($token) || $token === '') {
            return false;
        }

        return hash_equals($this->token(), $token);
    }

    public function field(): string
    {
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            e($this->name()),
            e($this->token())
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UsuarioRepository;

final class AuthService
{
    public function __construct(private readonly UsuarioRepository $usuarios)
    {
    }

    public function attempt(string $email, string $password): ?array
    {
        $user = $this->usuarios->findByEmail(trim($email));

        if ($user === null) {
            return null;
        }

        if (strtoupper((string) $user['status']) !== 'ATIVO') {
            return null;
        }

        if (!password_verify($password, (string) $user['senha_hash'])) {
            return null;
        }

        $this->usuarios->updateLastLogin((int) $user['id'], date('Y-m-d H:i:s'));

        unset($user['senha_hash']);

        return $user;
    }
}

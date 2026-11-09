<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Repositories\UsuarioRepository;
use App\Services\AuthService;

final class AuthServiceTest extends IntegrationTestCase
{
    private function service(): AuthService
    {
        return new AuthService(new UsuarioRepository($this->database));
    }

    public function testItAuthenticatesASeededUser(): void
    {
        $user = $this->service()->attempt('admin@local.test', 'Admin@123');

        self::assertIsArray($user);
        self::assertSame('admin@local.test', $user['email']);
        self::assertArrayNotHasKey('senha_hash', $user);
    }

    public function testItRejectsInvalidPassword(): void
    {
        self::assertNull($this->service()->attempt('admin@local.test', 'senha-incorreta'));
    }

    public function testItRejectsUnknownEmail(): void
    {
        self::assertNull($this->service()->attempt('nao-existe@local.test', 'Admin@123'));
    }

    public function testItRejectsInactiveUser(): void
    {
        $userId = $this->userId('funcionario@local.test');
        $statement = $this->pdo->prepare('UPDATE usuarios SET status = \'INATIVO\' WHERE id = :id');
        $statement->execute(['id' => $userId]);

        self::assertNull($this->service()->attempt('funcionario@local.test', 'Admin@123'));
    }
}

<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Support\Config;
use App\Support\Database;
use App\Support\MigrationRunner;
use App\Support\Seeder;
use PDO;
use PHPUnit\Framework\TestCase;
use Throwable;

abstract class IntegrationTestCase extends TestCase
{
    protected Database $database;

    protected PDO $pdo;

    protected function setUp(): void
    {
        $config = new Config(base_path('config'));
        $config->load();

        $this->database = new Database($config);

        try {
            $this->pdo = $this->database->connection();
            (new MigrationRunner($this->pdo, base_path('database/migrations')))->migrate();
            (new Seeder($this->pdo, base_path('database/seeds/data')))->run();
        } catch (Throwable $exception) {
            self::markTestSkipped('Banco de dados indisponível: ' . $exception->getMessage());
        }
    }

    protected function userId(string $email): int
    {
        $statement = $this->pdo->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);
        $id = $statement->fetchColumn();

        self::assertNotFalse($id, 'Usuário de seed não encontrado: ' . $email);

        return (int) $id;
    }

    protected function redeId(): int
    {
        $id = $this->pdo->query('SELECT id FROM redes WHERE cnpj = \'00000000000191\' LIMIT 1')->fetchColumn();

        self::assertNotFalse($id);

        return (int) $id;
    }

    protected function regiaoId(): int
    {
        $statement = $this->pdo->prepare(
            'SELECT id FROM regioes WHERE rede_id = :rede_id AND nome = :nome LIMIT 1'
        );
        $statement->execute(['rede_id' => $this->redeId(), 'nome' => 'Região Metropolitana']);
        $id = $statement->fetchColumn();

        self::assertNotFalse($id, 'Região de seed não encontrada.');

        return (int) $id;
    }

    protected function unidadeId(string $codigo): int
    {
        $statement = $this->pdo->prepare(
            'SELECT id FROM unidades WHERE rede_id = :rede_id AND codigo_interno = :codigo LIMIT 1'
        );
        $statement->execute(['rede_id' => $this->redeId(), 'codigo' => $codigo]);
        $id = $statement->fetchColumn();

        self::assertNotFalse($id, 'Unidade de seed não encontrada: ' . $codigo);

        return (int) $id;
    }
}

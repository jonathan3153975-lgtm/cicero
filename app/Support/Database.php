<?php

declare(strict_types=1);

namespace App\Support;

use PDO;
use PDOException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

final class Database
{
    private ?PDO $connection = null;

    public function __construct(
        private readonly Config $config,
        private readonly ?LoggerInterface $logger = null
    ) {
    }

    public function connection(): PDO
    {
        if ($this->connection instanceof PDO) {
            return $this->connection;
        }

        $host = (string) $this->config->get('database.host', '127.0.0.1');
        $port = (string) $this->config->get('database.port', '3306');
        $database = (string) $this->config->get('database.database', '');
        $username = (string) $this->config->get('database.username', '');
        $password = (string) $this->config->get('database.password', '');
        $charset = (string) $this->config->get('database.charset', 'utf8mb4');

        if ($database === '' || $username === '') {
            throw new RuntimeException('Configuração do banco de dados incompleta.');
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $host,
            $port,
            $database,
            $charset
        );

        try {
            $this->connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            $this->logger?->error('Falha ao conectar ao banco de dados.', [
                'host' => $host,
                'port' => $port,
                'database' => $database,
                'exception' => $exception->getMessage(),
            ]);

            throw new RuntimeException(
                'Não foi possível conectar ao banco de dados.',
                0,
                $exception
            );
        }

        return $this->connection;
    }

    public function transaction(callable $callback): mixed
    {
        $pdo = $this->connection();

        if ($pdo->inTransaction()) {
            return $callback($pdo);
        }

        $pdo->beginTransaction();

        try {
            $result = $callback($pdo);
            $pdo->commit();

            return $result;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }
}

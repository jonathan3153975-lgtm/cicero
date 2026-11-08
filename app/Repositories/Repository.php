<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;
use PDO;

abstract class Repository
{
    public function __construct(protected readonly Database $database)
    {
    }

    protected function pdo(): PDO
    {
        return $this->database->connection();
    }

    protected function transaction(callable $callback): mixed
    {
        return $this->database->transaction($callback);
    }
}

<?php

declare(strict_types=1);

use App\Support\Application;
use App\Support\Database;
use App\Support\Seeder;

$root = dirname(__DIR__);

/** @var Application $app */
$app = require $root . '/bootstrap/app.php';

try {
    /** @var Database $database */
    $database = $app->container()->get(Database::class);
    $seeder = new Seeder($database->connection(), $root . '/database/seeds/data');
    $result = $seeder->run();

    foreach ($result as $key => $value) {
        echo ucfirst($key) . ': ' . $value . PHP_EOL;
    }
} catch (Throwable $exception) {
    fwrite(STDERR, 'Erro: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}

<?php

declare(strict_types=1);

use App\Support\Application;
use App\Support\Database;
use App\Support\MigrationRunner;

$root = dirname(__DIR__);

/** @var Application $app */
$app = require $root . '/bootstrap/app.php';

$command = $argv[1] ?? 'migrate';

try {
    /** @var Database $database */
    $database = $app->container()->get(Database::class);
    $runner = new MigrationRunner($database->connection(), $root . '/database/migrations');

    switch ($command) {
        case 'migrate':
            $executed = $runner->migrate();

            if ($executed === []) {
                echo "Nada a migrar.\n";
                break;
            }

            foreach ($executed as $migration) {
                echo "Migrada: {$migration}\n";
            }
            break;

        case 'rollback':
            $steps = (int) ($argv[2] ?? 1);
            $rolledBack = $runner->rollback($steps);

            if ($rolledBack === []) {
                echo "Nada a reverter.\n";
                break;
            }

            foreach ($rolledBack as $migration) {
                echo "Revertida: {$migration}\n";
            }
            break;

        case 'status':
            $applied = $runner->migrated();
            echo $applied === []
                ? "Nenhuma migration aplicada.\n"
                : implode("\n", $applied) . "\n";
            break;

        default:
            fwrite(STDERR, "Uso: php bin/migrate.php [migrate|rollback|status] [passos]\n");
            exit(1);
    }
} catch (Throwable $exception) {
    fwrite(STDERR, 'Erro: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}

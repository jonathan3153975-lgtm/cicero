<?php

declare(strict_types=1);

use Dotenv\Dotenv;

$root = dirname(__DIR__);

require $root . '/vendor/autoload.php';

if (is_file($root . '/.env')) {
    Dotenv::createImmutable($root)->safeLoad();
}

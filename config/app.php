<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'name' => Env::get('APP_NAME', 'Plataforma Fitness'),
    'env' => Env::get('APP_ENV', 'local'),
    'debug' => Env::bool('APP_DEBUG', false),
    'url' => Env::get('APP_URL', 'http://localhost:8000'),
    'timezone' => Env::get('APP_TIMEZONE', 'America/Sao_Paulo'),
    'key' => Env::get('APP_KEY', ''),
    'views' => base_path('resources/views'),
    'log' => [
        'channel' => Env::get('LOG_CHANNEL', 'daily'),
        'level' => Env::get('LOG_LEVEL', 'debug'),
        'path' => storage_path('logs/app.log'),
    ],
];

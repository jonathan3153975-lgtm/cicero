<?php

declare(strict_types=1);

use App\Controllers\Web\AuthController;
use App\Controllers\Web\DashboardController;
use App\Controllers\Web\HomeController;
use App\Controllers\Web\SettingsController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;

return [
    ['GET', '/', [HomeController::class, 'index']],

    ['GET', '/login', [AuthController::class, 'showLogin'], [GuestMiddleware::class]],
    ['POST', '/login', [AuthController::class, 'login'], [GuestMiddleware::class]],
    ['POST', '/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]],

    ['GET', '/dashboard', [DashboardController::class, 'index'], [
        AuthMiddleware::class,
        'middleware.role.admin',
    ]],

    ['GET', '/configuracoes', [SettingsController::class, 'index'], [
        AuthMiddleware::class,
        'middleware.role.admin',
    ]],
];

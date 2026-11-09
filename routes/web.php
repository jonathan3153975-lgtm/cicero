<?php

declare(strict_types=1);

use App\Controllers\Admin\AdminDashboardController;
use App\Controllers\Admin\AuditoriaController;
use App\Controllers\Admin\PerfilController;
use App\Controllers\Admin\PermissaoController;
use App\Controllers\Admin\RedeController;
use App\Controllers\Admin\RegiaoController;
use App\Controllers\Admin\UnidadeController;
use App\Controllers\Admin\UsuarioController;
use App\Controllers\Admin\VinculoController;
use App\Controllers\Web\AuthController;
use App\Controllers\Web\ContextController;
use App\Controllers\Web\DashboardController;
use App\Controllers\Web\HomeController;
use App\Controllers\Web\SettingsController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Middleware\ScopeMiddleware;

$admin = static fn (string $permission): array => [
    AuthMiddleware::class,
    ScopeMiddleware::class,
    [PermissionMiddleware::class, [$permission]],
];

return [
    ['GET', '/', [HomeController::class, 'index']],

    ['GET', '/login', [AuthController::class, 'showLogin'], [GuestMiddleware::class]],
    ['POST', '/login', [AuthController::class, 'login'], [GuestMiddleware::class]],
    ['POST', '/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]],

    ['GET', '/contexto', [ContextController::class, 'index'], [AuthMiddleware::class]],
    ['POST', '/contexto', [ContextController::class, 'switch'], [AuthMiddleware::class]],

    ['GET', '/dashboard', [DashboardController::class, 'index'], $admin('dashboard.visualizar')],
    ['GET', '/configuracoes', [SettingsController::class, 'index'], $admin('configuracao.visualizar')],

    ['GET', '/admin', [AdminDashboardController::class, 'index'], $admin('usuario.visualizar')],

    ['GET', '/admin/redes', [RedeController::class, 'index'], $admin('rede.visualizar')],
    ['GET', '/admin/redes/novo', [RedeController::class, 'create'], $admin('rede.criar')],
    ['POST', '/admin/redes', [RedeController::class, 'store'], $admin('rede.criar')],
    ['GET', '/admin/redes/{id}/editar', [RedeController::class, 'edit'], $admin('rede.editar')],
    ['POST', '/admin/redes/{id}', [RedeController::class, 'update'], $admin('rede.editar')],

    ['GET', '/admin/regioes', [RegiaoController::class, 'index'], $admin('regiao.visualizar')],
    ['GET', '/admin/regioes/novo', [RegiaoController::class, 'create'], $admin('regiao.criar')],
    ['POST', '/admin/regioes', [RegiaoController::class, 'store'], $admin('regiao.criar')],
    ['GET', '/admin/regioes/{id}/editar', [RegiaoController::class, 'edit'], $admin('regiao.editar')],
    ['POST', '/admin/regioes/{id}', [RegiaoController::class, 'update'], $admin('regiao.editar')],

    ['GET', '/admin/unidades', [UnidadeController::class, 'index'], $admin('unidade.visualizar')],
    ['GET', '/admin/unidades/novo', [UnidadeController::class, 'create'], $admin('unidade.criar')],
    ['POST', '/admin/unidades', [UnidadeController::class, 'store'], $admin('unidade.criar')],
    ['GET', '/admin/unidades/{id}/editar', [UnidadeController::class, 'edit'], $admin('unidade.editar')],
    ['POST', '/admin/unidades/{id}', [UnidadeController::class, 'update'], $admin('unidade.editar')],

    ['GET', '/admin/usuarios', [UsuarioController::class, 'index'], $admin('usuario.visualizar')],
    ['GET', '/admin/usuarios/novo', [UsuarioController::class, 'create'], $admin('usuario.criar')],
    ['POST', '/admin/usuarios', [UsuarioController::class, 'store'], $admin('usuario.criar')],
    ['GET', '/admin/usuarios/{id}/editar', [UsuarioController::class, 'edit'], $admin('usuario.editar')],
    ['POST', '/admin/usuarios/{id}', [UsuarioController::class, 'update'], $admin('usuario.editar')],

    ['GET', '/admin/perfis', [PerfilController::class, 'index'], $admin('perfil.visualizar')],
    ['GET', '/admin/perfis/novo', [PerfilController::class, 'create'], $admin('perfil.criar')],
    ['POST', '/admin/perfis', [PerfilController::class, 'store'], $admin('perfil.criar')],
    ['GET', '/admin/perfis/{id}/editar', [PerfilController::class, 'edit'], $admin('perfil.editar')],
    ['POST', '/admin/perfis/{id}', [PerfilController::class, 'update'], $admin('perfil.editar')],

    ['GET', '/admin/permissoes', [PermissaoController::class, 'index'], $admin('permissao.visualizar')],
    ['GET', '/admin/vinculos', [VinculoController::class, 'index'], $admin('vinculo.visualizar')],
    ['GET', '/admin/vinculos/novo', [VinculoController::class, 'create'], $admin('vinculo.criar')],
    ['POST', '/admin/vinculos', [VinculoController::class, 'store'], $admin('vinculo.criar')],
    ['POST', '/admin/vinculos/{id}/revogar', [VinculoController::class, 'revoke'], $admin('vinculo.editar')],

    ['GET', '/admin/auditoria', [AuditoriaController::class, 'index'], $admin('auditoria.visualizar')],
];

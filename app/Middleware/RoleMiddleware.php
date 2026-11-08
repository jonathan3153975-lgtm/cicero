<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exceptions\ForbiddenException;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;

final class RoleMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly Session $session,
        private readonly array $roles
    ) {
    }

    public function process(Request $request, callable $next): Response
    {
        $user = $this->session->get('auth.user');
        $role = is_array($user) ? ($user['role'] ?? null) : null;

        if (!is_string($role) || !in_array($role, $this->roles, true)) {
            if ($request->wantsJson()) {
                return Response::json([
                    'success' => false,
                    'message' => 'Sem permissão para executar esta operação.',
                    'errors' => [],
                ], 403);
            }

            throw new ForbiddenException('Sem permissão para acessar este recurso.');
        }

        return $next($request);
    }
}

<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Support\Request;
use App\Support\Response;
use App\Support\Session;

final class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly Session $session)
    {
    }

    public function process(Request $request, callable $next): Response
    {
        if (!$this->session->has('auth.user_id')) {
            if ($request->wantsJson()) {
                return Response::json([
                    'success' => false,
                    'message' => 'Não autenticado.',
                    'errors' => [],
                ], 401);
            }

            $this->session->flash('_intended', $request->path);

            return Response::redirect('/login');
        }

        return $next($request);
    }
}

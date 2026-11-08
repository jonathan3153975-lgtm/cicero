<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Support\Request;
use App\Support\Response;
use App\Support\Session;

final class GuestMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly Session $session)
    {
    }

    public function process(Request $request, callable $next): Response
    {
        if ($this->session->has('auth.user_id')) {
            return Response::redirect('/dashboard');
        }

        return $next($request);
    }
}

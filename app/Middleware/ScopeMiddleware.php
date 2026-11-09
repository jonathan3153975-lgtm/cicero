<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exceptions\ForbiddenException;
use App\Services\TenantContextService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;

final class ScopeMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly Session $session,
        private readonly TenantContextService $contexts
    ) {
    }

    public function process(Request $request, callable $next): Response
    {
        $userId = (int) ($this->session->get('auth.user_id') ?? 0);
        $context = $this->session->get('context');

        if ($userId <= 0 || !is_array($context)) {
            return $this->redirectToContext($request);
        }

        try {
            $resolved = $this->contexts->resolve(
                $userId,
                $context['rede_id'] ?? null,
                $context['unidade_id'] ?? null,
                $context['regiao_id'] ?? null
            );
        } catch (ForbiddenException) {
            $this->session->remove('context');

            return $this->redirectToContext($request);
        }

        $this->session->set('context', $resolved);

        return $next($request);
    }

    private function redirectToContext(Request $request): Response
    {
        if ($request->wantsJson()) {
            return Response::json([
                'success' => false,
                'message' => 'Contexto de rede/unidade não selecionado.',
                'errors' => [],
            ], 409);
        }

        $this->session->flash('error', 'Selecione um contexto de rede/unidade para continuar.');

        return Response::redirect('/contexto');
    }
}

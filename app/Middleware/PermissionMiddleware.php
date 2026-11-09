<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exceptions\ForbiddenException;
use App\Services\AuthorizationService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use RuntimeException;

final class PermissionMiddleware implements MiddlewareInterface, ConfigurableMiddlewareInterface
{
    private ?string $permission = null;

    public function __construct(
        private readonly Session $session,
        private readonly AuthorizationService $authorization
    ) {
    }

    public function configure(array $parameters): void
    {
        $this->permission = isset($parameters[0]) ? (string) $parameters[0] : null;
    }

    public function process(Request $request, callable $next): Response
    {
        if ($this->permission === null || $this->permission === '') {
            throw new RuntimeException('Permissão não configurada no middleware.');
        }

        $userId = (int) ($this->session->get('auth.user_id') ?? 0);
        $context = $this->session->get('context');
        $context = is_array($context) ? $context : null;

        $redeId = $context !== null && $context['rede_id'] !== null ? (int) $context['rede_id'] : null;
        $unidadeId = $context !== null && $context['unidade_id'] !== null ? (int) $context['unidade_id'] : null;
        $regiaoId = $context !== null && ($context['regiao_id'] ?? null) !== null ? (int) $context['regiao_id'] : null;

        if ($userId <= 0 || !$this->authorization->can($userId, $this->permission, $redeId, $unidadeId, $regiaoId)) {
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

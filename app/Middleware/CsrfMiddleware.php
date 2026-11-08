<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exceptions\HttpException;
use App\Support\Config;
use App\Support\Csrf;
use App\Support\Request;
use App\Support\Response;

final class CsrfMiddleware implements MiddlewareInterface
{
    private const STATE_CHANGING = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function __construct(
        private readonly Csrf $csrf,
        private readonly Config $config
    ) {
    }

    public function process(Request $request, callable $next): Response
    {
        if (in_array($request->method, self::STATE_CHANGING, true)) {
            $name = (string) $this->config->get('security.csrf_token_name', '_token');
            $token = $request->input($name) ?? $request->header('X-CSRF-Token');

            if (!is_string($token) || !$this->csrf->validate($token)) {
                throw new HttpException(
                    'Sessão expirada. Recarregue a página e tente novamente.',
                    403
                );
            }
        }

        return $next($request);
    }
}

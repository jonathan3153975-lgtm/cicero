<?php

declare(strict_types=1);

namespace App\Support;

use App\Exceptions\HttpException;
use App\Exceptions\NotFoundException;
use App\Middleware\ConfigurableMiddlewareInterface;
use App\Middleware\CsrfMiddleware;
use App\Middleware\MiddlewareInterface;
use App\Services\AuthorizationService;
use RuntimeException;
use Throwable;

final class Application
{
    public function __construct(
        private readonly Container $container,
        private readonly Router $router,
        private readonly Config $config,
        private readonly Session $session,
        private readonly View $view,
        private readonly Csrf $csrf,
        private readonly ExceptionHandler $exceptions,
        private readonly AuthorizationService $authorization
    ) {
    }

    public function registerErrorHandling(): void
    {
        $this->exceptions->register();
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function handle(Request $request): Response
    {
        try {
            $this->session->start();
            $this->shareViewData($request);

            $result = $this->router->dispatch($request->method, $request->path);

            if ($result->status === RouteResult::NOT_FOUND) {
                throw new NotFoundException();
            }

            if ($result->status === RouteResult::METHOD_NOT_ALLOWED) {
                throw new HttpException(
                    'Método não permitido.',
                    405,
                    ['Allow' => implode(', ', $result->allowedMethods)]
                );
            }

            $request->setRouteParams($result->vars);

            $middleware = $result->middleware;

            if (!$request->isApi()) {
                array_unshift($middleware, CsrfMiddleware::class);
            }

            $next = fn (Request $req): Response => $this->invoke($result->handler, $req);

            foreach (array_reverse($middleware) as $definition) {
                $next = function (Request $req) use ($definition, $next): Response {
                    return $this->resolveMiddleware($definition)->process($req, $next);
                };
            }

            return $next($request);
        } catch (Throwable $exception) {
            return $this->exceptions->render($exception, $request);
        }
    }

    private function invoke(array $handler, Request $request): Response
    {
        $class = $handler[0] ?? null;
        $method = $handler[1] ?? null;

        if (!is_string($class) || !is_string($method)) {
            throw new RuntimeException('Handler de rota inválido.');
        }

        $controller = $this->container->get($class);

        if (!method_exists($controller, $method)) {
            throw new RuntimeException(sprintf('Ação "%s::%s" não encontrada.', $class, $method));
        }

        $response = $controller->{$method}($request);

        if (!$response instanceof Response) {
            throw new RuntimeException(sprintf('A ação "%s::%s" deve retornar uma Response.', $class, $method));
        }

        return $response;
    }

    private function resolveMiddleware(string|array $definition): MiddlewareInterface
    {
        if (is_array($definition)) {
            $class = $definition[0] ?? null;
            $parameters = (array) ($definition[1] ?? []);

            if (!is_string($class)) {
                throw new RuntimeException('Definição de middleware inválida.');
            }

            $middleware = $this->container->get($class);

            if ($middleware instanceof ConfigurableMiddlewareInterface) {
                $middleware->configure($parameters);
            }
        } else {
            $middleware = $this->container->get($definition);
        }

        if (!$middleware instanceof MiddlewareInterface) {
            throw new RuntimeException('Middleware inválido.');
        }

        return $middleware;
    }

    private function shareViewData(Request $request): void
    {
        $brand = (array) $this->config->get('theme.brand', []);
        $context = $this->session->get('context');
        $context = is_array($context) ? $context : null;
        $userId = (int) ($this->session->get('auth.user_id') ?? 0);

        $permissions = [];

        if ($userId > 0) {
            try {
                $permissions = $this->authorization->permissionsForContext(
                    $userId,
                    $context !== null && $context['rede_id'] !== null ? (int) $context['rede_id'] : null,
                    $context !== null && $context['unidade_id'] !== null ? (int) $context['unidade_id'] : null,
                    $context !== null && ($context['regiao_id'] ?? null) !== null ? (int) $context['regiao_id'] : null
                );
            } catch (Throwable) {
                $permissions = [];
            }
        }

        $this->view->shareMany([
            'app' => (array) $this->config->get('app', []),
            'brand' => $brand,
            'brandCss' => ThemeCompiler::cssVariables($brand),
            'csrfToken' => $this->csrf->token(),
            'csrfName' => $this->csrf->name(),
            'user' => $this->session->get('auth.user'),
            'isAuthenticated' => $this->session->has('auth.user_id'),
            'flash' => $this->session->flashes(),
            'currentPath' => $request->path,
            'defaultTheme' => (string) $this->config->get('theme.default', 'dark'),
            'permissions' => $permissions,
            'can' => static fn (string $permission): bool => in_array($permission, $permissions, true),
            'context' => $context,
        ]);
    }
}

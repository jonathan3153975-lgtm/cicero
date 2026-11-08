<?php

declare(strict_types=1);

namespace App\Support;

use FastRoute\DataGenerator\GroupCountBased as GroupCountBasedGenerator;
use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased as GroupCountBasedDispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;

final class Router
{
    private readonly RouteCollector $collector;

    private ?Dispatcher $dispatcher = null;

    public function __construct()
    {
        $this->collector = new RouteCollector(new Std(), new GroupCountBasedGenerator());
    }

    public function add(string $method, string $path, array $handler, array $middleware = []): void
    {
        $this->collector->addRoute($method, $path, [
            'handler' => $handler,
            'middleware' => $middleware,
        ]);

        $this->dispatcher = null;
    }

    public function load(array $routes): void
    {
        foreach ($routes as $route) {
            $method = $route[0] ?? null;
            $path = $route[1] ?? null;
            $handler = $route[2] ?? null;
            $middleware = $route[3] ?? [];

            if (!is_string($method) || !is_string($path) || !is_array($handler)) {
                throw new \InvalidArgumentException('Definição de rota inválida.');
            }

            $this->add($method, $path, $handler, $middleware);
        }
    }

    public function dispatch(string $method, string $path): RouteResult
    {
        $this->dispatcher ??= new GroupCountBasedDispatcher($this->collector->getData());

        $result = $this->dispatcher->dispatch(strtoupper($method), $path);

        return match ($result[0]) {
            Dispatcher::FOUND => RouteResult::found($result[1], $result[2]),
            Dispatcher::METHOD_NOT_ALLOWED => RouteResult::methodNotAllowed($result[1]),
            default => RouteResult::notFound(),
        };
    }
}

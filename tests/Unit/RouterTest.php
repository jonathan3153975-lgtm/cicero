<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Router;
use App\Support\RouteResult;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    private function router(): Router
    {
        $router = new Router();
        $router->add('GET', '/', ['App\\Controllers\\Web\\HomeController', 'index']);
        $router->add('GET', '/users/{id}', ['App\\Controllers\\Web\\UserController', 'show']);

        return $router;
    }

    public function testItFindsRegisteredRoute(): void
    {
        $result = $this->router()->dispatch('GET', '/');

        self::assertSame(RouteResult::FOUND, $result->status);
        self::assertSame(['App\\Controllers\\Web\\HomeController', 'index'], $result->handler);
    }

    public function testItExtractsRouteParameters(): void
    {
        $result = $this->router()->dispatch('GET', '/users/42');

        self::assertSame(RouteResult::FOUND, $result->status);
        self::assertSame(['id' => '42'], $result->vars);
    }

    public function testItReturnsNotFoundForUnknownPath(): void
    {
        $result = $this->router()->dispatch('GET', '/missing');

        self::assertSame(RouteResult::NOT_FOUND, $result->status);
    }

    public function testItReturnsMethodNotAllowed(): void
    {
        $result = $this->router()->dispatch('POST', '/');

        self::assertSame(RouteResult::METHOD_NOT_ALLOWED, $result->status);
        self::assertContains('GET', $result->allowedMethods);
    }
}

<?php

declare(strict_types=1);

use App\Middleware\RoleMiddleware;
use App\Support\Application;
use App\Support\Config;
use App\Support\Container;
use App\Support\Csrf;
use App\Support\Database;
use App\Support\ExceptionHandler;
use App\Support\LoggerFactory;
use App\Support\Router;
use App\Support\Session;
use App\Support\View;
use Dotenv\Dotenv;
use Psr\Log\LoggerInterface;

$root = dirname(__DIR__);

require $root . '/vendor/autoload.php';

if (is_file($root . '/.env')) {
    Dotenv::createImmutable($root)->safeLoad();
}

$config = new Config($root . '/config');
$config->load();

date_default_timezone_set((string) $config->get('app.timezone', 'UTC'));

$logger = LoggerFactory::create($config);

$container = new Container();
$container->instance(Container::class, $container);
$container->instance(Config::class, $config);
$container->instance(LoggerInterface::class, $logger);

$container->singleton(Session::class, static fn (Container $c): Session => new Session($c->get(Config::class)));
$container->singleton(Csrf::class, static fn (Container $c): Csrf => new Csrf(
    $c->get(Session::class),
    $c->get(Config::class)
));
$container->singleton(View::class, static fn (Container $c): View => new View($c->get(Config::class)));
$container->singleton(Database::class, static fn (Container $c): Database => new Database(
    $c->get(Config::class),
    $c->get(LoggerInterface::class)
));
$container->singleton(ExceptionHandler::class, static fn (Container $c): ExceptionHandler => new ExceptionHandler(
    $c->get(View::class),
    $c->get(Config::class),
    $c->get(LoggerInterface::class)
));
$container->singleton(
    'middleware.role.admin',
    static fn (Container $c): RoleMiddleware => new RoleMiddleware(
        $c->get(Session::class),
        ['admin']
    )
);

$router = new Router();
$router->load(require $root . '/routes/web.php');
$router->load(require $root . '/routes/api.php');

$container->instance(Router::class, $router);

return new Application(
    $container,
    $router,
    $config,
    $container->get(Session::class),
    $container->get(View::class),
    $container->get(Csrf::class),
    $container->get(ExceptionHandler::class)
);

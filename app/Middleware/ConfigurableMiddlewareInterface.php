<?php

declare(strict_types=1);

namespace App\Middleware;

interface ConfigurableMiddlewareInterface
{
    public function configure(array $parameters): void;
}

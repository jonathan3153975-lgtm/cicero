<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Support\Request;
use App\Support\Response;

interface MiddlewareInterface
{
    public function process(Request $request, callable $next): Response;
}

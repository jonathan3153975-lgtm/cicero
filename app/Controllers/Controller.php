<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\Response;
use App\Support\View;

abstract class Controller
{
    public function __construct(protected readonly View $view)
    {
    }

    protected function render(string $template, array $data = [], ?string $layout = 'layouts.app'): Response
    {
        return Response::html($this->view->render($template, $data, $layout));
    }

    protected function json(array $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function redirect(string $location, int $status = 302): Response
    {
        return Response::redirect($location, $status);
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Controllers\Controller;
use App\Support\Request;
use App\Support\Response;

final class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->render('dashboard.index', [
            'title' => 'Painel',
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Controller;
use App\Support\Config;
use App\Support\Database;
use App\Support\Request;
use App\Support\Response;
use App\Support\View;
use Throwable;

final class HealthController extends Controller
{
    public function __construct(
        View $view,
        private readonly Database $database,
        private readonly Config $config
    ) {
        parent::__construct($view);
    }

    public function index(Request $request): Response
    {
        $databaseStatus = 'ok';
        $message = 'Aplicação operacional.';

        try {
            $this->database->connection()->query('SELECT 1');
        } catch (Throwable) {
            $databaseStatus = 'unavailable';
            $message = 'Aplicação operacional com banco de dados indisponível.';
        }

        return $this->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'application' => (string) $this->config->get('app.name', ''),
                'environment' => (string) $this->config->get('app.env', ''),
                'status' => 'ok',
                'database' => $databaseStatus,
                'timestamp' => date(DATE_ATOM),
            ],
        ]);
    }
}

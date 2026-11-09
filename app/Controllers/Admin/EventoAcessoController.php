<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Services\AcessoService;
use App\Services\AuditService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

final class EventoAcessoController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly AcessoService $service
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        return $this->render('admin.acessos.index', [
            'title' => 'Eventos de acesso',
            'eventos' => $this->service->eventsForScope(
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            ),
        ]);
    }
}

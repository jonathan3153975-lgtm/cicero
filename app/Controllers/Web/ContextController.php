<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Controllers\Controller;
use App\Exceptions\ForbiddenException;
use App\Services\AuditService;
use App\Services\AuthorizationService;
use App\Services\TenantContextService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

final class ContextController extends Controller
{
    public function __construct(
        View $view,
        private readonly Session $session,
        private readonly TenantContextService $contexts,
        private readonly AuthorizationService $authorization,
        private readonly AuditService $audit
    ) {
        parent::__construct($view);
    }

    public function index(Request $request): Response
    {
        $userId = (int) ($this->session->get('auth.user_id') ?? 0);

        return $this->render('context.index', [
            'title' => 'Selecionar contexto',
            'contexts' => $this->contexts->contextsForUser($userId),
            'current' => $this->session->get('context'),
        ]);
    }

    public function switch(Request $request): Response
    {
        $userId = (int) ($this->session->get('auth.user_id') ?? 0);
        $previous = $this->session->get('context');
        $previous = is_array($previous) ? $previous : null;

        if ($previous !== null) {
            $allowed = $this->authorization->can(
                $userId,
                'contexto.trocar',
                $previous['rede_id'] !== null ? (int) $previous['rede_id'] : null,
                $previous['unidade_id'] !== null ? (int) $previous['unidade_id'] : null,
                ($previous['regiao_id'] ?? null) !== null ? (int) $previous['regiao_id'] : null
            );

            if (!$allowed) {
                throw new ForbiddenException('Sem permissão para trocar de contexto.');
            }
        }

        $context = $this->contexts->resolve(
            $userId,
            $request->input('rede_id'),
            $request->input('unidade_id'),
            $request->input('regiao_id')
        );

        $this->session->set('context', $context);

        $this->audit->record(
            $userId,
            'contexto.trocar',
            'contexto',
            null,
            $previous,
            $context,
            $context['rede_id'],
            $context['unidade_id'],
            $request->ip(),
            $request->userAgent()
        );

        $this->session->flash('success', 'Contexto alterado para ' . $context['label'] . '.');

        return $this->redirect('/dashboard');
    }
}

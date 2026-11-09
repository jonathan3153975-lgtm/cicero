<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Services\AuditService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

abstract class AdminController extends Controller
{
    public function __construct(
        View $view,
        protected readonly Session $session,
        private readonly AuditService $audit
    ) {
        parent::__construct($view);
    }

    protected function actorId(): int
    {
        return (int) ($this->session->get('auth.user_id') ?? 0);
    }

    protected function contextRedeId(): ?int
    {
        $context = $this->session->get('context');

        return is_array($context) && $context['rede_id'] !== null ? (int) $context['rede_id'] : null;
    }

    protected function contextUnidadeId(): ?int
    {
        $context = $this->session->get('context');

        return is_array($context) && $context['unidade_id'] !== null ? (int) $context['unidade_id'] : null;
    }

    protected function contextRegiaoId(): ?int
    {
        $context = $this->session->get('context');

        return is_array($context) && ($context['regiao_id'] ?? null) !== null ? (int) $context['regiao_id'] : null;
    }

    protected function isPlatformContext(): bool
    {
        return $this->contextRedeId() === null && $this->contextUnidadeId() === null;
    }

    protected function contextLabel(): string
    {
        $context = $this->session->get('context');

        return is_array($context) ? (string) ($context['label'] ?? '—') : '—';
    }

    protected function formState(): array
    {
        return [
            'errors' => (array) $this->session->getFlash('errors', []),
            'old' => (array) $this->session->getFlash('old', []),
        ];
    }

    protected function backWithErrors(string $location, array $errors, array $old): Response
    {
        $this->session->flash('errors', $errors);
        $this->session->flash('old', $old);

        return $this->redirect($location);
    }

    protected function flashError(string $message): void
    {
        $this->session->flash('error', $message);
    }

    protected function flashSuccess(string $message): void
    {
        $this->session->flash('success', $message);
    }

    protected function audit(
        Request $request,
        string $action,
        string $entity,
        ?int $entityId,
        ?array $before,
        ?array $after,
        ?int $redeId = null,
        ?int $unidadeId = null
    ): void {
        $this->audit->record(
            $this->actorId(),
            $action,
            $entity,
            $entityId,
            $before,
            $after,
            $redeId ?? $this->contextRedeId(),
            $unidadeId ?? $this->contextUnidadeId(),
            $request->ip(),
            $request->userAgent()
        );
    }

    protected function auditExplicit(
        Request $request,
        string $action,
        string $entity,
        ?int $entityId,
        ?array $before,
        ?array $after,
        ?int $redeId,
        ?int $unidadeId
    ): void {
        $this->audit->record(
            $this->actorId(),
            $action,
            $entity,
            $entityId,
            $before,
            $after,
            $redeId,
            $unidadeId,
            $request->ip(),
            $request->userAgent()
        );
    }
}

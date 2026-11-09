<header class="app-header">
    <button class="icon-button app-header__menu" type="button" data-sidebar-toggle aria-label="Abrir menu" aria-controls="sidebar" aria-expanded="false">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18v2H3zM3 11h18v2H3zM3 16h18v2H3z"/></svg>
    </button>

    <div class="app-header__title">
        <h1><?= e($title ?? 'Painel') ?></h1>
        <p class="text-muted">Fundação técnica · MVC · PHP 8.2+</p>
    </div>

    <div class="app-header__actions">
        <button class="icon-button" type="button" data-theme-toggle aria-label="Alternar tema" title="Alternar tema">
            <svg class="theme-icon theme-icon--moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.7 13.3A8.5 8.5 0 0 1 10.7 3.3 8.5 8.5 0 1 0 20.7 13.3z"/></svg>
            <svg class="theme-icon theme-icon--sun" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 17a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-15h1v3h-2V2h1zm0 17h1v3h-2v-3h1zM2 11h3v2H2v-2zm17 0h3v2h-3v-2zM4.2 5.6l1.4-1.4 2.1 2.1-1.4 1.4zM16.3 17.7l1.4-1.4 2.1 2.1-1.4 1.4zM4.2 18.4l2.1-2.1 1.4 1.4-2.1 2.1zM16.3 6.3l2.1-2.1 1.4 1.4-2.1 2.1z"/></svg>
        </button>

        <?php if (!empty($isAuthenticated)): ?>
            <?php if (is_array($context ?? null)): ?>
                <a class="context-chip" href="/contexto" title="Trocar contexto">
                    <span class="context-chip__label">Contexto</span>
                    <strong><?= e($context['label'] ?? '—') ?></strong>
                </a>
            <?php endif; ?>
            <div class="user-chip">
                <span class="user-chip__avatar" aria-hidden="true">
                    <?= e(mb_substr((string) (($user['name'] ?? 'U')), 0, 1)) ?>
                </span>
                <span class="user-chip__name"><?= e($user['name'] ?? 'Usuário') ?></span>
            </div>
        <?php else: ?>
            <a class="btn btn-primary btn-sm" href="/login">Entrar</a>
        <?php endif; ?>
    </div>
</header>

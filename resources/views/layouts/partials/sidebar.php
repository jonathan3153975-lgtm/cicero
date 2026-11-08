<aside class="sidebar" id="sidebar" aria-label="Navegação principal">
    <a class="sidebar__brand" href="/">
        <span class="sidebar__logo" aria-hidden="true">F</span>
        <span class="sidebar__brand-text">
            <strong><?= e($app['name'] ?? 'Plataforma Fitness') ?></strong>
            <small>Gestão de academias</small>
        </span>
    </a>

    <nav class="sidebar__nav">
        <a class="nav-link <?= ($currentPath ?? '/') === '/' ? 'is-active' : '' ?>" href="/">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/></svg>
            <span>Início</span>
        </a>

        <?php if (!empty($isAuthenticated)): ?>
            <a class="nav-link <?= ($currentPath ?? '') === '/dashboard' ? 'is-active' : '' ?>" href="/dashboard">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z"/></svg>
                <span>Painel</span>
            </a>
            <a class="nav-link <?= ($currentPath ?? '') === '/configuracoes' ? 'is-active' : '' ?>" href="/configuracoes">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 2.4 2.4 3.3-.6.9 3.2 3 1.6-1.5 3 .9 3.3-3.1 1.3-.7 3.3L12 19l-3.2 1.5-.7-3.3-3.1-1.3.9-3.3L4.4 9.6l3-1.6.9-3.2 3.3.6zM12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8z"/></svg>
                <span>Configurações</span>
            </a>
        <?php else: ?>
            <a class="nav-link <?= ($currentPath ?? '') === '/login' ? 'is-active' : '' ?>" href="/login">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h5v-2H5V5h5zm5.6 4.4L14.2 8.8 16.4 11H8v2h8.4l-2.2 2.2 1.4 1.4L20.4 12z"/></svg>
                <span>Entrar</span>
            </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar__footer">
        <?php if (!empty($isAuthenticated)): ?>
            <form method="post" action="/logout">
                <input type="hidden" name="<?= e($csrfName ?? '_token') ?>" value="<?= e($csrfToken ?? '') ?>">
                <button class="btn btn-ghost btn-block" type="submit">Sair</button>
            </form>
        <?php else: ?>
            <span class="sidebar__hint">Fundação v1.0</span>
        <?php endif; ?>
    </div>
</aside>

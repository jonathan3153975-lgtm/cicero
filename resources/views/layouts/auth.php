<!DOCTYPE html>
<html lang="pt-BR" data-theme="<?= e($defaultTheme ?? 'dark') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark light">
    <title><?= e($title ?? 'Entrar') ?> · <?= e($app['name'] ?? 'Plataforma Fitness') ?></title>
    <link rel="icon" type="image/svg+xml" href="/assets/icons/favicon.svg">
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>:root{<?= $brandCss ?? '' ?>}</style>
    <script>
        try {
            var storedTheme = localStorage.getItem('academia-theme');
            if (storedTheme) {
                document.documentElement.dataset.theme = storedTheme;
            }
        } catch (error) {
            /* preferência indisponível */
        }
    </script>
</head>
<body>
<div class="auth-shell">
    <section class="auth-brand">
        <a class="auth-brand__logo" href="/">
            <span class="sidebar__logo" aria-hidden="true">F</span>
            <span><?= e($app['name'] ?? 'Plataforma Fitness') ?></span>
        </a>

        <div class="auth-brand__copy">
            <span class="badge badge--accent">Fundação pronta</span>
            <h1>Gestão de academias para redes e unidades.</h1>
            <p class="text-muted">
                Estrutura MVC, camada de dados isolada, temas configuráveis e segurança por padrão.
            </p>
            <ul class="auth-brand__list">
                <li>Composer com autoload PSR-4</li>
                <li>Roteamento web e API versionada</li>
                <li>PDO com prepared statements</li>
                <li>Tema escuro e claro por variáveis CSS</li>
            </ul>
        </div>

        <p class="auth-brand__footer text-muted">PHP 8.2+ · MariaDB · Design System próprio</p>
    </section>

    <main class="auth-card">
        <button class="icon-button auth-card__theme" type="button" data-theme-toggle aria-label="Alternar tema" title="Alternar tema">
            <svg class="theme-icon theme-icon--moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.7 13.3A8.5 8.5 0 0 1 10.7 3.3 8.5 8.5 0 1 0 20.7 13.3z"/></svg>
            <svg class="theme-icon theme-icon--sun" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 17a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-15h1v3h-2V2h1zm0 17h1v3h-2v-3h1zM2 11h3v2H2v-2zm17 0h3v2h-3v-2zM4.2 5.6l1.4-1.4 2.1 2.1-1.4 1.4zM16.3 17.7l1.4-1.4 2.1 2.1-1.4 1.4zM4.2 18.4l2.1-2.1 1.4 1.4-2.1 2.1zM16.3 6.3l2.1-2.1 1.4 1.4-2.1 2.1z"/></svg>
        </button>

        <?php require base_path('resources/views/layouts/partials/flash.php'); ?>

        <?= $content ?>
    </main>
</div>
<?php require base_path('resources/views/layouts/partials/loading.php'); ?>
<script type="module" src="/assets/js/app.js"></script>
</body>
</html>

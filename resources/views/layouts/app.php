<?php $pageTitle = $title ?? 'Painel'; ?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="<?= e($defaultTheme ?? 'dark') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark light">
    <title><?= e($pageTitle) ?> · <?= e($app['name'] ?? 'Plataforma Fitness') ?></title>
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
<div class="app-shell">
    <?php require base_path('resources/views/layouts/partials/sidebar.php'); ?>
    <button class="sidebar-overlay" type="button" data-sidebar-close aria-label="Fechar menu"></button>
    <div class="app-main">
        <?php require base_path('resources/views/layouts/partials/header.php'); ?>
        <main class="app-content">
            <?php require base_path('resources/views/layouts/partials/flash.php'); ?>
            <?= $content ?>
        </main>
    </div>
</div>
<?php require base_path('resources/views/layouts/partials/loading.php'); ?>
<script type="module" src="/assets/js/app.js"></script>
</body>
</html>

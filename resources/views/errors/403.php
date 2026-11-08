<?php $appName = $app['name'] ?? 'Plataforma Fitness'; ?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="<?= e($defaultTheme ?? 'dark') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 · <?= e($appName) ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>:root{<?= $brandCss ?? '' ?>}</style>
</head>
<body>
<main class="error-page">
    <div class="error-card">
        <span class="badge badge--danger">Erro 403</span>
        <h1>Acesso não autorizado</h1>
        <p class="text-muted"><?= e($message ?? 'Você não tem permissão para acessar este recurso.') ?></p>
        <div class="error-card__actions">
            <a class="btn btn-primary" href="/">Ir para o início</a>
            <?php if (!empty($isAuthenticated)): ?>
                <a class="btn btn-ghost" href="/dashboard">Voltar ao painel</a>
            <?php else: ?>
                <a class="btn btn-ghost" href="/login">Entrar</a>
            <?php endif; ?>
        </div>
    </div>
</main>
</body>
</html>

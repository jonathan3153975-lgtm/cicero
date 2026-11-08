<?php $appName = $app['name'] ?? 'Plataforma Fitness'; ?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="<?= e($defaultTheme ?? 'dark') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 · <?= e($appName) ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>:root{<?= $brandCss ?? '' ?>}</style>
</head>
<body>
<main class="error-page">
    <div class="error-card">
        <span class="badge badge--danger">Erro <?= e($status ?? 500) ?></span>
        <h1>Algo deu errado</h1>
        <p class="text-muted"><?= e($message ?? 'Ocorreu um erro inesperado. Tente novamente mais tarde.') ?></p>

        <?php if (!empty($exception)): ?>
            <div class="debug-panel">
                <strong>Detalhes de depuração</strong>
                <p><code><?= e($exception::class) ?></code></p>
                <pre class="code-block"><?= e($exception->getMessage()) ?></pre>
            </div>
        <?php endif; ?>

        <div class="error-card__actions">
            <a class="btn btn-primary" href="/">Ir para o início</a>
        </div>
    </div>
</main>
</body>
</html>

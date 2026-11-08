<?php $appName = $app['name'] ?? 'Plataforma Fitness'; ?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="<?= e($defaultTheme ?? 'dark') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>422 · <?= e($appName) ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>:root{<?= $brandCss ?? '' ?>}</style>
</head>
<body>
<main class="error-page">
    <div class="error-card">
        <span class="badge badge--warning">Erro 422</span>
        <h1>Dados inválidos</h1>
        <p class="text-muted"><?= e($message ?? 'A requisição contém dados inválidos.') ?></p>

        <?php if (!empty($errors)): ?>
            <ul class="error-list">
                <?php foreach ($errors as $fieldErrors): ?>
                    <?php foreach ((array) $fieldErrors as $fieldError): ?>
                        <li><?= e($fieldError) ?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div class="error-card__actions">
            <a class="btn btn-primary" href="/">Ir para o início</a>
        </div>
    </div>
</main>
</body>
</html>

<?php
$emailError = $errors['email'][0] ?? null;
$passwordError = $errors['password'][0] ?? null;
$oldEmail = $old['email'] ?? '';
?>
<section class="auth-form">
    <header class="auth-form__header">
        <h2>Acessar o painel</h2>
        <p class="text-muted">Entre para visualizar a área protegida da fundação.</p>
    </header>

    <form method="post" action="/login" class="form" novalidate>
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">

        <div class="form__field">
            <label for="email">E-mail</label>
            <input
                class="input <?= $emailError !== null ? 'is-invalid' : '' ?>"
                type="email"
                id="email"
                name="email"
                value="<?= e($oldEmail) ?>"
                autocomplete="username"
                inputmode="email"
                required
                autofocus
            >
            <?php if ($emailError !== null): ?>
                <p class="form__error"><?= e($emailError) ?></p>
            <?php endif; ?>
        </div>

        <div class="form__field">
            <label for="password">Senha</label>
            <div class="input-group">
                <input
                    class="input <?= $passwordError !== null ? 'is-invalid' : '' ?>"
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="current-password"
                    required
                    minlength="6"
                >
                <button class="input-group__action" type="button" data-password-toggle="password" aria-label="Mostrar senha">
                    Mostrar
                </button>
            </div>
            <?php if ($passwordError !== null): ?>
                <p class="form__error"><?= e($passwordError) ?></p>
            <?php endif; ?>
        </div>

        <button class="btn btn-primary btn-block" type="submit">Entrar</button>
    </form>

    <p class="auth-hint text-muted">
        Credenciais de demonstração configuradas em <code>.env</code>.<br>
        Substitua este fluxo pelo módulo real de autenticação.
    </p>

    <p class="auth-back"><a href="/">Voltar para a página inicial</a></p>
</section>

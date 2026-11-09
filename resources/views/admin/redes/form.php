<?php
$old = $old ?? [];
$item = $item ?? [];
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $item[$key] ?? $default);
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
$action = $item !== [] ? '/admin/redes/' . $item['id'] : '/admin/redes';
?>
<section class="page-heading">
    <div>
        <h2><?= e($title) ?></h2>
        <p class="text-muted">Dados cadastrais e status da rede.</p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/redes">Voltar</a>
</section>

<section class="card">
    <form method="post" action="<?= e($action) ?>" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">

        <div class="form-grid">
            <div class="form__field">
                <label for="razao_social">Razão social</label>
                <input class="input <?= $err('razao_social') ? 'is-invalid' : '' ?>" id="razao_social" name="razao_social" value="<?= e($val('razao_social')) ?>" required>
                <?php if ($err('razao_social')): ?><p class="form__error"><?= e($err('razao_social')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="nome_fantasia">Nome fantasia</label>
                <input class="input <?= $err('nome_fantasia') ? 'is-invalid' : '' ?>" id="nome_fantasia" name="nome_fantasia" value="<?= e($val('nome_fantasia')) ?>" required>
                <?php if ($err('nome_fantasia')): ?><p class="form__error"><?= e($err('nome_fantasia')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="cnpj">CNPJ</label>
                <input class="input <?= $err('cnpj') ? 'is-invalid' : '' ?>" id="cnpj" name="cnpj" value="<?= e($val('cnpj')) ?>" required>
                <?php if ($err('cnpj')): ?><p class="form__error"><?= e($err('cnpj')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="email">E-mail</label>
                <input class="input <?= $err('email') ? 'is-invalid' : '' ?>" type="email" id="email" name="email" value="<?= e($val('email')) ?>">
                <?php if ($err('email')): ?><p class="form__error"><?= e($err('email')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="telefone">Telefone</label>
                <input class="input" id="telefone" name="telefone" value="<?= e($val('telefone')) ?>">
            </div>
            <div class="form__field">
                <label for="plano_contratado">Plano contratado</label>
                <input class="input" id="plano_contratado" name="plano_contratado" value="<?= e($val('plano_contratado')) ?>">
            </div>
            <div class="form__field">
                <label for="cep">CEP</label>
                <input class="input" id="cep" name="cep" value="<?= e($val('cep')) ?>">
            </div>
            <div class="form__field">
                <label for="logradouro">Logradouro</label>
                <input class="input" id="logradouro" name="logradouro" value="<?= e($val('logradouro')) ?>">
            </div>
            <div class="form__field">
                <label for="numero">Número</label>
                <input class="input" id="numero" name="numero" value="<?= e($val('numero')) ?>">
            </div>
            <div class="form__field">
                <label for="complemento">Complemento</label>
                <input class="input" id="complemento" name="complemento" value="<?= e($val('complemento')) ?>">
            </div>
            <div class="form__field">
                <label for="bairro">Bairro</label>
                <input class="input" id="bairro" name="bairro" value="<?= e($val('bairro')) ?>">
            </div>
            <div class="form__field">
                <label for="cidade">Cidade</label>
                <input class="input" id="cidade" name="cidade" value="<?= e($val('cidade')) ?>">
            </div>
            <div class="form__field">
                <label for="estado">Estado</label>
                <input class="input" id="estado" name="estado" maxlength="2" value="<?= e($val('estado')) ?>">
            </div>
            <div class="form__field">
                <label for="status">Status</label>
                <select class="input" id="status" name="status">
                    <?php foreach (['ATIVA', 'SUSPENSA', 'EM_IMPLANTACAO', 'CANCELADA'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= $val('status', 'ATIVA') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Salvar</button>
            <a class="btn btn-ghost" href="/admin/redes">Cancelar</a>
        </div>
    </form>
</section>

<?php
$old = $old ?? [];
$item = $item ?? [];
$planosRede = $planosRede ?? [];
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $item[$key] ?? $default);
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
$action = $item !== [] ? '/admin/redes/' . $item['id'] : '/admin/redes';
?>
<section class="page-heading">
    <div>
        <h2><?= e($title) ?></h2>
        <p class="text-muted">Dados cadastrais, endereço e plano contratado da rede.</p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/redes">Voltar</a>
</section>

<section class="card">
    <form method="post" action="<?= e($action) ?>" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">
        <input type="hidden" name="plano_contratado" value="<?= e($val('plano_contratado')) ?>">

        <div class="row">
            <div class="col-sm-12 col-md-6">
                <div class="form__field">
                    <label for="razao_social">Razão social</label>
                    <input class="input <?= $err('razao_social') ? 'is-invalid' : '' ?>" id="razao_social" name="razao_social" value="<?= e($val('razao_social')) ?>" required>
                    <?php if ($err('razao_social')): ?><p class="form__error"><?= e($err('razao_social')) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-6">
                <div class="form__field">
                    <label for="nome_fantasia">Nome fantasia</label>
                    <input class="input <?= $err('nome_fantasia') ? 'is-invalid' : '' ?>" id="nome_fantasia" name="nome_fantasia" value="<?= e($val('nome_fantasia')) ?>" required>
                    <?php if ($err('nome_fantasia')): ?><p class="form__error"><?= e($err('nome_fantasia')) ?></p><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="cnpj">CNPJ</label>
                    <input class="input <?= $err('cnpj') ? 'is-invalid' : '' ?>" id="cnpj" name="cnpj" value="<?= e($val('cnpj')) ?>" data-mask="cnpj" inputmode="numeric" maxlength="18" required>
                    <?php if ($err('cnpj')): ?><p class="form__error"><?= e($err('cnpj')) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="email">E-mail</label>
                    <input class="input <?= $err('email') ? 'is-invalid' : '' ?>" type="email" id="email" name="email" value="<?= e($val('email')) ?>">
                    <?php if ($err('email')): ?><p class="form__error"><?= e($err('email')) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="telefone">Telefone</label>
                    <input class="input" id="telefone" name="telefone" value="<?= e($val('telefone')) ?>" data-mask="phone" inputmode="tel" maxlength="15">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12 col-md-3">
                <div class="form__field">
                    <label for="cep">CEP</label>
                    <input class="input <?= $err('cep') ? 'is-invalid' : '' ?>" id="cep" name="cep" value="<?= e($val('cep')) ?>" data-mask="cep" data-cep inputmode="numeric" maxlength="9">
                    <p class="text-muted" data-cep-feedback></p>
                    <?php if ($err('cep')): ?><p class="form__error"><?= e($err('cep')) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-6">
                <div class="form__field">
                    <label for="logradouro">Logradouro</label>
                    <input class="input" id="logradouro" name="logradouro" value="<?= e($val('logradouro')) ?>">
                </div>
            </div>
            <div class="col-sm-12 col-md-3">
                <div class="form__field">
                    <label for="numero">Número</label>
                    <input class="input" id="numero" name="numero" value="<?= e($val('numero')) ?>">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12 col-md-2">
                <div class="form__field">
                    <label for="complemento">Complemento</label>
                    <input class="input" id="complemento" name="complemento" value="<?= e($val('complemento')) ?>">
                </div>
            </div>
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="bairro">Bairro</label>
                    <input class="input" id="bairro" name="bairro" value="<?= e($val('bairro')) ?>">
                </div>
            </div>
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="cidade">Cidade</label>
                    <input class="input" id="cidade" name="cidade" value="<?= e($val('cidade')) ?>">
                </div>
            </div>
            <div class="col-sm-12 col-md-2">
                <div class="form__field">
                    <label for="estado">Estado</label>
                    <input class="input <?= $err('estado') ? 'is-invalid' : '' ?>" id="estado" name="estado" value="<?= e($val('estado')) ?>" maxlength="2">
                    <?php if ($err('estado')): ?><p class="form__error"><?= e($err('estado')) ?></p><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12 col-md-6">
                <div class="form__field">
                    <label for="plano_rede_id">Plano contratado</label>
                    <select class="input <?= $err('plano_rede_id') ? 'is-invalid' : '' ?>" id="plano_rede_id" name="plano_rede_id">
                        <option value="">Selecione um plano</option>
                        <?php foreach ($planosRede as $plano): ?>
                            <option value="<?= e((string) $plano['id']) ?>" <?= $val('plano_rede_id') === (string) $plano['id'] ? 'selected' : '' ?>>
                                <?= e($plano['nome']) ?><?= $plano['status'] !== 'ATIVO' ? ' (inativo)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($err('plano_rede_id')): ?><p class="form__error"><?= e($err('plano_rede_id')) ?></p><?php endif; ?>
                    <?php if ($planosRede === []): ?><p class="text-muted">Nenhum plano cadastrado. Solicite ao superadministrador.</p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-6">
                <div class="form__field">
                    <label for="status">Status</label>
                    <select class="input" id="status" name="status">
                        <?php foreach (['ATIVA', 'SUSPENSA', 'EM_IMPLANTACAO', 'CANCELADA'] as $status): ?>
                            <option value="<?= e($status) ?>" <?= $val('status', 'ATIVA') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Salvar</button>
            <a class="btn btn-ghost" href="/admin/redes">Cancelar</a>
        </div>
    </form>
</section>

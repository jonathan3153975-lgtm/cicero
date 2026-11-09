<?php
$old = $old ?? [];
$item = $item ?? [];
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $item[$key] ?? $default);
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
$action = $item !== [] ? '/admin/pessoas/' . $item['id'] : '/admin/pessoas';
$isEdit = $item !== [];
?>
<section class="page-heading">
    <div>
        <h2><?= e($title) ?></h2>
        <p class="text-muted">Dados cadastrais, contato e endereço principal.</p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/pessoas">Voltar</a>
</section>

<section class="card">
    <form method="post" action="<?= e($action) ?>" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">

        <div class="form-grid">
            <?php if ($isPlatform): ?>
                <div class="form__field">
                    <label for="rede_id">Rede</label>
                    <select class="input" id="rede_id" name="rede_id">
                        <option value="">Global (sem rede)</option>
                        <?php foreach ($redes as $rede): ?>
                            <option value="<?= e((string) $rede['id']) ?>" <?= $val('rede_id') === (string) $rede['id'] ? 'selected' : '' ?>><?= e($rede['nome_fantasia']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <input type="hidden" name="rede_id" value="<?= e((string) $currentRedeId) ?>">
            <?php endif; ?>

            <div class="form__field">
                <label for="tipo">Tipo</label>
                <select class="input" id="tipo" name="tipo">
                    <?php foreach (['PF', 'PJ'] as $tipo): ?>
                        <option value="<?= e($tipo) ?>" <?= $val('tipo', 'PF') === $tipo ? 'selected' : '' ?>><?= e($tipo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form__field">
                <label for="nome">Nome / Razão social</label>
                <input class="input <?= $err('nome') ? 'is-invalid' : '' ?>" id="nome" name="nome" value="<?= e($val('nome')) ?>" required>
                <?php if ($err('nome')): ?><p class="form__error"><?= e($err('nome')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="nome_social">Nome social / Fantasia</label>
                <input class="input" id="nome_social" name="nome_social" value="<?= e($val('nome_social')) ?>">
            </div>
            <div class="form__field">
                <label for="cpf">CPF</label>
                <input class="input <?= $err('cpf') ? 'is-invalid' : '' ?>" id="cpf" name="cpf" value="<?= e($val('cpf')) ?>">
                <?php if ($err('cpf')): ?><p class="form__error"><?= e($err('cpf')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="cnpj">CNPJ</label>
                <input class="input <?= $err('cnpj') ? 'is-invalid' : '' ?>" id="cnpj" name="cnpj" value="<?= e($val('cnpj')) ?>">
                <?php if ($err('cnpj')): ?><p class="form__error"><?= e($err('cnpj')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="data_nascimento">Data de nascimento</label>
                <input class="input <?= $err('data_nascimento') ? 'is-invalid' : '' ?>" type="date" id="data_nascimento" name="data_nascimento" value="<?= e($val('data_nascimento')) ?>">
                <?php if ($err('data_nascimento')): ?><p class="form__error"><?= e($err('data_nascimento')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="sexo">Sexo</label>
                <input class="input" id="sexo" name="sexo" value="<?= e($val('sexo')) ?>">
            </div>
            <div class="form__field">
                <label for="estado_civil">Estado civil</label>
                <input class="input" id="estado_civil" name="estado_civil" value="<?= e($val('estado_civil')) ?>">
            </div>
            <div class="form__field">
                <label for="email_principal">E-mail</label>
                <input class="input <?= $err('email_principal') ? 'is-invalid' : '' ?>" type="email" id="email_principal" name="email_principal" value="<?= e($val('email_principal')) ?>">
                <?php if ($err('email_principal')): ?><p class="form__error"><?= e($err('email_principal')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="telefone_principal">Telefone</label>
                <input class="input" id="telefone_principal" name="telefone_principal" value="<?= e($val('telefone_principal')) ?>">
            </div>
            <div class="form__field">
                <label for="status">Status</label>
                <select class="input" id="status" name="status">
                    <?php foreach (['ATIVO', 'INATIVO', 'BLOQUEADO'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= $val('status', 'ATIVO') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                    <?php endforeach; ?>
                </select>
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
                <input class="input <?= $err('estado') ? 'is-invalid' : '' ?>" id="estado" name="estado" maxlength="2" value="<?= e($val('estado')) ?>">
                <?php if ($err('estado')): ?><p class="form__error"><?= e($err('estado')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="observacoes">Observações</label>
                <textarea class="input" id="observacoes" name="observacoes" rows="3"><?= e($val('observacoes')) ?></textarea>
            </div>
        </div>

        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Salvar</button>
            <a class="btn btn-ghost" href="/admin/pessoas">Cancelar</a>
        </div>
    </form>
</section>

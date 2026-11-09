<?php
$old = $old ?? [];
$item = $item ?? [];
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $item[$key] ?? $default);
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
$action = $item !== [] ? '/admin/usuarios/' . $item['id'] : '/admin/usuarios';
$isEdit = $item !== [];
?>
<section class="page-heading">
    <div>
        <h2><?= e($title) ?></h2>
        <p class="text-muted"><?= $isEdit ? 'Atualize os dados do usuário.' : 'Crie o usuário e vincule-o a uma rede ou unidade.' ?></p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/usuarios">Voltar</a>
</section>

<section class="card">
    <form method="post" action="<?= e($action) ?>" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">

        <div class="form-grid">
            <div class="form__field">
                <label for="nome">Nome</label>
                <input class="input <?= $err('nome') ? 'is-invalid' : '' ?>" id="nome" name="nome" value="<?= e($val('nome')) ?>" required>
                <?php if ($err('nome')): ?><p class="form__error"><?= e($err('nome')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="email">E-mail</label>
                <input class="input <?= $err('email') ? 'is-invalid' : '' ?>" type="email" id="email" name="email" value="<?= e($val('email')) ?>" required>
                <?php if ($err('email')): ?><p class="form__error"><?= e($err('email')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="status">Status</label>
                <select class="input" id="status" name="status">
                    <?php foreach (['ATIVO', 'INATIVO'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= $val('status', 'ATIVO') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form__field">
                <label for="password"><?= $isEdit ? 'Nova senha (opcional)' : 'Senha' ?></label>
                <input class="input <?= $err('password') ? 'is-invalid' : '' ?>" type="password" id="password" name="password" <?= $isEdit ? '' : 'required' ?> minlength="8" autocomplete="new-password">
                <?php if ($err('password')): ?><p class="form__error"><?= e($err('password')) ?></p><?php endif; ?>
            </div>
        </div>

        <?php if (!$isEdit): ?>
            <div class="form-grid">
                <?php if ($isPlatform): ?>
                    <div class="form__field">
                        <label for="rede_id">Rede</label>
                        <select class="input" id="rede_id" name="rede_id">
                            <option value="">Sem vínculo de rede</option>
                            <?php foreach ($redes as $rede): ?>
                                <option value="<?= e((string) $rede['id']) ?>" <?= $val('rede_id') === (string) $rede['id'] ? 'selected' : '' ?>><?= e($rede['nome_fantasia']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="rede_id" value="<?= e((string) $currentRedeId) ?>">
                <?php endif; ?>

                <?php if ($currentUnidadeId === null): ?>
                    <div class="form__field">
                        <label for="unidade_id">Unidade</label>
                        <select class="input" id="unidade_id" name="unidade_id">
                            <option value="">Sem vínculo de unidade</option>
                            <?php foreach ($unidades as $unidade): ?>
                                <option value="<?= e((string) $unidade['id']) ?>" <?= $val('unidade_id') === (string) $unidade['id'] ? 'selected' : '' ?>>
                                    <?= e($unidade['nome']) ?><?= isset($unidade['rede_nome']) ? ' · ' . e($unidade['rede_nome']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="unidade_id" value="<?= e((string) $currentUnidadeId) ?>">
                    <div class="form__field">
                        <label>Unidade</label>
                        <input class="input" value="Unidade do contexto atual" disabled>
                    </div>
                <?php endif; ?>
            </div>
            <p class="text-muted">O perfil de acesso é atribuído em <a href="/admin/vinculos">Vínculos</a>.</p>
        <?php endif; ?>

        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Salvar</button>
            <a class="btn btn-ghost" href="/admin/usuarios">Cancelar</a>
        </div>
    </form>
</section>

<?php
$old = $old ?? [];
$item = $item ?? [];
$isEdit = $item !== [];
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $item[$key] ?? $default);
$action = $isEdit ? '/admin/modalidades/' . $item['id'] : '/admin/modalidades';
?>
<section class="page-heading">
    <div>
        <h2><?= e($title) ?></h2>
        <p class="text-muted">
            <?= $isPlatform ? 'No contexto de plataforma, a modalidade é criada como global.' : 'A modalidade será criada para a rede do contexto atual.' ?>
        </p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/modalidades">Voltar</a>
</section>

<section class="card">
    <form method="post" action="<?= e($action) ?>" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">

        <!-- Linha 1: Nome (col-md-10) + Status -->
        <div class="row">
            <div class="col-sm-12 col-md-9">
                <div class="form__field">
                    <label for="nome">Nome <span class="text-danger">*</span></label>
                    <input class="input <?= $err('nome') ? 'is-invalid' : '' ?>" id="nome" name="nome" value="<?= e($val('nome')) ?>" required>
                    <?php if ($err('nome')): ?><p class="form__error"><?= e($err('nome')) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-3">
                <div class="form__field">
                    <label for="status">Status</label>
                    <select class="input" id="status" name="status">
                        <?php foreach (['ATIVO', 'INATIVO'] as $status): ?>
                            <option value="<?= e($status) ?>" <?= $val('status', 'ATIVO') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Linha 2: Descrição (col-sm-12) -->
        <div class="row">
            <div class="col-sm-12">
                <div class="form__field">
                    <label for="descricao">Descrição</label>
                    <textarea class="input" id="descricao" name="descricao" rows="4"><?= e($val('descricao')) ?></textarea>
                </div>
            </div>
        </div>

        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Salvar</button>
            <a class="btn btn-ghost" href="/admin/modalidades">Cancelar</a>
        </div>
    </form>
</section>
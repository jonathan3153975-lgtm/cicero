<?php
$old = $old ?? [];
$item = $item ?? [];
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $item[$key] ?? $default);
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
$action = $item !== [] ? '/admin/regioes/' . $item['id'] : '/admin/regioes';
?>
<section class="page-heading">
    <div>
        <h2><?= e($title) ?></h2>
        <p class="text-muted">Regiões pertencem a uma rede e agrupam unidades próximas.</p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/regioes">Voltar</a>
</section>

<section class="card">
    <form method="post" action="<?= e($action) ?>" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">

        <div class="form-grid">
            <?php if ($isPlatform): ?>
                <div class="form__field">
                    <label for="rede_id">Rede</label>
                    <select class="input <?= $err('rede_id') ? 'is-invalid' : '' ?>" id="rede_id" name="rede_id" <?= $item !== [] ? 'disabled' : '' ?>>
                        <option value="">Selecione</option>
                        <?php foreach ($redes as $rede): ?>
                            <option value="<?= e((string) $rede['id']) ?>" <?= $val('rede_id') === (string) $rede['id'] ? 'selected' : '' ?>>
                                <?= e($rede['nome_fantasia']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($err('rede_id')): ?><p class="form__error"><?= e($err('rede_id')) ?></p><?php endif; ?>
                </div>
            <?php else: ?>
                <input type="hidden" name="rede_id" value="<?= e((string) $currentRedeId) ?>">
                <div class="form__field">
                    <label>Rede</label>
                    <input class="input" value="<?= e($item['rede_nome'] ?? 'Rede do contexto atual') ?>" disabled>
                </div>
            <?php endif; ?>

            <div class="form__field">
                <label for="nome">Nome da região</label>
                <input class="input <?= $err('nome') ? 'is-invalid' : '' ?>" id="nome" name="nome" value="<?= e($val('nome')) ?>" required>
                <?php if ($err('nome')): ?><p class="form__error"><?= e($err('nome')) ?></p><?php endif; ?>
            </div>

            <div class="form__field">
                <label for="status">Status</label>
                <select class="input" id="status" name="status">
                    <?php foreach (['ATIVO', 'INATIVO'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= $val('status', 'ATIVO') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Salvar</button>
            <a class="btn btn-ghost" href="/admin/regioes">Cancelar</a>
        </div>
    </form>
</section>

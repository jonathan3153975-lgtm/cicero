<?php
$old = $old ?? [];
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $default);
?>
<section class="page-heading">
    <div>
        <h2><?= e($title) ?></h2>
        <p class="text-muted">Escolha a conta e a competência para importar o extrato.</p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/conciliacoes">Voltar</a>
</section>

<section class="card">
    <form method="post" action="/admin/conciliacoes" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">
        <div class="form-grid">
            <div class="form__field">
                <label for="conta_bancaria_id">Conta bancária</label>
                <select class="input <?= $err('conta_bancaria_id') ? 'is-invalid' : '' ?>" id="conta_bancaria_id" name="conta_bancaria_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($contas as $conta): ?>
                        <option value="<?= e((string) $conta['id']) ?>" <?= $val('conta_bancaria_id') === (string) $conta['id'] ? 'selected' : '' ?>>
                            <?= e($conta['banco']) ?> · <?= e($conta['unidade_nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($err('conta_bancaria_id')): ?><p class="form__error"><?= e($err('conta_bancaria_id')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="competencia">Competência</label>
                <input class="input <?= $err('competencia') ? 'is-invalid' : '' ?>" id="competencia" type="month" name="competencia" value="<?= e($val('competencia', date('Y-m'))) ?>" required>
                <?php if ($err('competencia')): ?><p class="form__error"><?= e($err('competencia')) ?></p><?php endif; ?>
            </div>
        </div>
        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Criar conciliação</button>
            <a class="btn btn-ghost" href="/admin/conciliacoes">Cancelar</a>
        </div>
    </form>
</section>

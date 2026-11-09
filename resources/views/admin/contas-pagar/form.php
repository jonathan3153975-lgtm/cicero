<?php
$old = $old ?? [];
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $default);
?>
<section class="page-heading">
    <div>
        <h2><?= e($title) ?></h2>
        <p class="text-muted">Registre a despesa e quite-a depois com uma conta bancária.</p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/contas-pagar">Voltar</a>
</section>

<section class="card">
    <form method="post" action="/admin/contas-pagar" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">
        <div class="form-grid">
            <div class="form__field">
                <label for="unidade_id">Unidade</label>
                <select class="input <?= $err('unidade_id') ? 'is-invalid' : '' ?>" id="unidade_id" name="unidade_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($unidades as $unidade): ?>
                        <option value="<?= e((string) $unidade['id']) ?>" <?= $val('unidade_id') === (string) $unidade['id'] ? 'selected' : '' ?>>
                            <?= e($unidade['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($err('unidade_id')): ?><p class="form__error"><?= e($err('unidade_id')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="centro_custo_id">Centro de custo</label>
                <select class="input" id="centro_custo_id" name="centro_custo_id">
                    <option value="">Sem centro de custo</option>
                    <?php foreach ($centros as $centro): ?>
                        <option value="<?= e((string) $centro['id']) ?>" <?= $val('centro_custo_id') === (string) $centro['id'] ? 'selected' : '' ?>>
                            <?= e($centro['nome']) ?> · <?= e($centro['unidade_nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form__field">
                <label for="descricao">Descrição</label>
                <input class="input <?= $err('descricao') ? 'is-invalid' : '' ?>" id="descricao" name="descricao" value="<?= e($val('descricao')) ?>" required>
                <?php if ($err('descricao')): ?><p class="form__error"><?= e($err('descricao')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="competencia">Competência</label>
                <input class="input <?= $err('competencia') ? 'is-invalid' : '' ?>" id="competencia" type="month" name="competencia" value="<?= e($val('competencia', date('Y-m'))) ?>" required>
                <?php if ($err('competencia')): ?><p class="form__error"><?= e($err('competencia')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="data_emissao">Emissão</label>
                <input class="input <?= $err('data_emissao') ? 'is-invalid' : '' ?>" id="data_emissao" type="date" name="data_emissao" value="<?= e($val('data_emissao', date('Y-m-d'))) ?>">
                <?php if ($err('data_emissao')): ?><p class="form__error"><?= e($err('data_emissao')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="data_vencimento">Vencimento</label>
                <input class="input <?= $err('data_vencimento') ? 'is-invalid' : '' ?>" id="data_vencimento" type="date" name="data_vencimento" value="<?= e($val('data_vencimento', date('Y-m-d'))) ?>" required>
                <?php if ($err('data_vencimento')): ?><p class="form__error"><?= e($err('data_vencimento')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="valor">Valor</label>
                <input class="input <?= $err('valor') ? 'is-invalid' : '' ?>" id="valor" type="number" step="0.01" min="0.01" name="valor" value="<?= e($val('valor')) ?>" required>
                <?php if ($err('valor')): ?><p class="form__error"><?= e($err('valor')) ?></p><?php endif; ?>
            </div>
        </div>
        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Salvar conta</button>
            <a class="btn btn-ghost" href="/admin/contas-pagar">Cancelar</a>
        </div>
    </form>
</section>

<?php
$old = $old ?? [];
$item = $item ?? [];
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $item[$key] ?? $default);
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
?>
<section class="page-heading">
    <div>
        <h2><?= e($title) ?></h2>
        <p class="text-muted">Condições contratuais e situação.</p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/contratos">Voltar</a>
</section>

<section class="card">
    <form method="post" action="/admin/contratos/<?= e((string) $item['id']) ?>" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">

        <div class="form-grid">
            <div class="form__field">
                <label>Contrato</label>
                <input class="input" value="<?= e($item['numero']) ?>" disabled>
            </div>
            <div class="form__field">
                <label>Aluno</label>
                <input class="input" value="<?= e($item['aluno_nome']) ?>" disabled>
            </div>
            <div class="form__field">
                <label>Plano</label>
                <input class="input" value="<?= e($item['plano_nome']) ?>" disabled>
            </div>
            <div class="form__field">
                <label>Unidade</label>
                <input class="input" value="<?= e($item['unidade_nome']) ?>" disabled>
            </div>
            <div class="form__field">
                <label for="data_inicio">Data de início</label>
                <input class="input <?= $err('data_inicio') ? 'is-invalid' : '' ?>" type="date" id="data_inicio" name="data_inicio" value="<?= e($val('data_inicio')) ?>" required>
                <?php if ($err('data_inicio')): ?><p class="form__error"><?= e($err('data_inicio')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="data_fim">Data final</label>
                <input class="input <?= $err('data_fim') ? 'is-invalid' : '' ?>" type="date" id="data_fim" name="data_fim" value="<?= e($val('data_fim')) ?>">
                <?php if ($err('data_fim')): ?><p class="form__error"><?= e($err('data_fim')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="dia_vencimento">Dia de vencimento</label>
                <input class="input <?= $err('dia_vencimento') ? 'is-invalid' : '' ?>" type="number" min="1" max="31" id="dia_vencimento" name="dia_vencimento" value="<?= e($val('dia_vencimento')) ?>">
                <?php if ($err('dia_vencimento')): ?><p class="form__error"><?= e($err('dia_vencimento')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="valor_mensal">Valor mensal</label>
                <input class="input <?= $err('valor_mensal') ? 'is-invalid' : '' ?>" type="number" min="0" step="0.01" id="valor_mensal" name="valor_mensal" value="<?= e($val('valor_mensal')) ?>" required>
                <?php if ($err('valor_mensal')): ?><p class="form__error"><?= e($err('valor_mensal')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="status">Status</label>
                <select class="input" id="status" name="status">
                    <?php foreach (['ATIVO', 'SUSPENSO', 'CANCELADO', 'ENCERRADO', 'TRANSFERIDO'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= $val('status', 'ATIVO') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form__field">
                <label class="checkbox"><input type="checkbox" name="renovacao_automatica" value="1" <?= !empty($item['renovacao_automatica']) ? 'checked' : '' ?>> Renovação automática</label>
            </div>
            <div class="form__field">
                <label for="observacoes">Observações</label>
                <textarea class="input" id="observacoes" name="observacoes" rows="3"><?= e($val('observacoes')) ?></textarea>
            </div>
        </div>

        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Salvar</button>
            <a class="btn btn-ghost" href="/admin/contratos">Cancelar</a>
        </div>
    </form>
</section>

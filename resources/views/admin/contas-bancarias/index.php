<?php
$old = $old ?? [];
$canEdit = isset($can) && $can('financeiro.editar');
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $default);
?>
<section class="page-heading">
    <div>
        <h2>Contas bancárias</h2>
        <p class="text-muted">Caixas e contas usadas nos recebimentos e pagamentos.</p>
    </div>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Banco / caixa</th>
                    <th scope="col">Unidade</th>
                    <th scope="col">Agência</th>
                    <th scope="col">Conta</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Saldo inicial</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contas as $conta): ?>
                    <tr>
                        <td><strong><?= e($conta['banco']) ?></strong></td>
                        <td><?= e($conta['unidade_nome']) ?></td>
                        <td><?= e($conta['agencia'] ?? '—') ?></td>
                        <td><?= e($conta['conta'] ?? '—') ?></td>
                        <td><?= e($conta['tipo']) ?></td>
                        <td>R$ <?= e(number_format((float) $conta['saldo_inicial'], 2, ',', '.')) ?></td>
                        <td><span class="badge <?= $conta['status'] === 'ATIVO' ? 'badge--success' : 'badge--warning' ?>"><?= e($conta['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($contas === []): ?>
                    <tr><td colspan="7" class="text-muted">Nenhuma conta cadastrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php if ($canEdit): ?>
<section class="card">
    <h3>Nova conta</h3>
    <form method="post" action="/admin/contas-bancarias" class="form">
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
                <label for="banco">Banco / caixa</label>
                <input class="input <?= $err('banco') ? 'is-invalid' : '' ?>" id="banco" name="banco" value="<?= e($val('banco')) ?>" required>
                <?php if ($err('banco')): ?><p class="form__error"><?= e($err('banco')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="agencia">Agência</label>
                <input class="input" id="agencia" name="agencia" value="<?= e($val('agencia')) ?>">
            </div>
            <div class="form__field">
                <label for="conta">Conta</label>
                <input class="input" id="conta" name="conta" value="<?= e($val('conta')) ?>">
            </div>
            <div class="form__field">
                <label for="tipo">Tipo</label>
                <select class="input" id="tipo" name="tipo">
                    <?php foreach (['CORRENTE', 'POUPANCA', 'CAIXA', 'INVESTIMENTO'] as $tipo): ?>
                        <option value="<?= e($tipo) ?>" <?= $val('tipo', 'CORRENTE') === $tipo ? 'selected' : '' ?>><?= e($tipo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form__field">
                <label for="saldo_inicial">Saldo inicial</label>
                <input class="input" id="saldo_inicial" type="number" step="0.01" name="saldo_inicial" value="<?= e($val('saldo_inicial', '0')) ?>">
            </div>
        </div>
        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Criar conta</button>
        </div>
    </form>
</section>
<?php endif; ?>

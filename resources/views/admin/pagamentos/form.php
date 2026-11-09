<?php
$old = $old ?? [];
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $default);
$selected = $val('cobranca_id', (string) ($cobrancaSelecionada ?? ''));
?>
<section class="page-heading">
    <div>
        <h2><?= e($title) ?></h2>
        <p class="text-muted">O pagamento é alocado na cobrança selecionada e gera movimento de caixa.</p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/pagamentos">Voltar</a>
</section>

<section class="card">
    <form method="post" action="/admin/pagamentos" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">
        <div class="form-grid">
            <div class="form__field">
                <label for="cobranca_id">Cobrança</label>
                <select class="input <?= $err('cobranca_id') ? 'is-invalid' : '' ?>" id="cobranca_id" name="cobranca_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($cobrancas as $cobranca): ?>
                        <option value="<?= e((string) $cobranca['id']) ?>" <?= $selected === (string) $cobranca['id'] ? 'selected' : '' ?>>
                            <?= e($cobranca['numero']) ?> · <?= e($cobranca['aluno_nome']) ?> · R$ <?= e(number_format((float) $cobranca['valor_total'], 2, ',', '.')) ?> · venc. <?= e($cobranca['data_vencimento']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($err('cobranca_id')): ?><p class="form__error"><?= e($err('cobranca_id')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="valor">Valor</label>
                <input class="input <?= $err('valor') ? 'is-invalid' : '' ?>" id="valor" type="number" step="0.01" min="0.01" name="valor" value="<?= e($val('valor')) ?>" required>
                <?php if ($err('valor')): ?><p class="form__error"><?= e($err('valor')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="conta_bancaria_id">Conta bancária</label>
                <select class="input <?= $err('conta_bancaria_id') ? 'is-invalid' : '' ?>" id="conta_bancaria_id" name="conta_bancaria_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($contas as $conta): ?>
                        <option value="<?= e((string) $conta['id']) ?>" <?= $val('conta_bancaria_id') === (string) $conta['id'] ? 'selected' : '' ?>>
                            <?= e($conta['banco']) ?> · <?= e($conta['unidade_nome']) ?><?= $conta['agencia'] ? ' · Ag. ' . e($conta['agencia']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($err('conta_bancaria_id')): ?><p class="form__error"><?= e($err('conta_bancaria_id')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="forma_pagamento_id">Forma de pagamento</label>
                <select class="input <?= $err('forma_pagamento_id') ? 'is-invalid' : '' ?>" id="forma_pagamento_id" name="forma_pagamento_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($formas as $forma): ?>
                        <option value="<?= e((string) $forma['id']) ?>" <?= $val('forma_pagamento_id') === (string) $forma['id'] ? 'selected' : '' ?>>
                            <?= e($forma['nome']) ?> (<?= e($forma['tipo']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($err('forma_pagamento_id')): ?><p class="form__error"><?= e($err('forma_pagamento_id')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="data_pagamento">Data do pagamento</label>
                <input class="input <?= $err('data_pagamento') ? 'is-invalid' : '' ?>" id="data_pagamento" type="date" name="data_pagamento" value="<?= e($val('data_pagamento', date('Y-m-d'))) ?>" required>
                <?php if ($err('data_pagamento')): ?><p class="form__error"><?= e($err('data_pagamento')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="data_compensacao">Compensação</label>
                <input class="input <?= $err('data_compensacao') ? 'is-invalid' : '' ?>" id="data_compensacao" type="date" name="data_compensacao" value="<?= e($val('data_compensacao')) ?>">
                <?php if ($err('data_compensacao')): ?><p class="form__error"><?= e($err('data_compensacao')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="transacao_id">Transação / autorização</label>
                <input class="input" id="transacao_id" name="transacao_id" value="<?= e($val('transacao_id')) ?>" placeholder="Opcional">
            </div>
        </div>
        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Registrar pagamento</button>
            <a class="btn btn-ghost" href="/admin/pagamentos">Cancelar</a>
        </div>
    </form>
</section>

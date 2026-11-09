<?php
$canEdit = isset($can) && $can('financeiro.editar');
$saldo = max(0, (float) $cobranca['valor_total'] - (float) $pago);
?>
<section class="page-heading">
    <div>
        <h2><?= e($cobranca['numero']) ?></h2>
        <p class="text-muted"><?= e($cobranca['aluno_nome']) ?> · <?= e($cobranca['unidade_nome']) ?></p>
    </div>
    <div class="page-heading__actions">
        <?php if ($canEdit && in_array($cobranca['status'], ['ABERTA', 'VENCIDA', 'PARCIAL'], true)): ?>
            <a class="btn btn-primary" href="/admin/pagamentos/novo?cobranca_id=<?= e((string) $cobranca['id']) ?>">Registrar pagamento</a>
        <?php endif; ?>
        <a class="btn btn-ghost btn-sm" href="/admin/cobrancas">Voltar</a>
    </div>
</section>

<section class="card">
    <div class="form-grid">
        <div class="form__field"><label>Status</label><input class="input" value="<?= e($cobranca['status']) ?>" disabled></div>
        <div class="form__field"><label>Tipo</label><input class="input" value="<?= e($cobranca['tipo']) ?>" disabled></div>
        <div class="form__field"><label>Competência</label><input class="input" value="<?= e($cobranca['competencia']) ?>" disabled></div>
        <div class="form__field"><label>Emissão</label><input class="input" value="<?= e($cobranca['data_emissao']) ?>" disabled></div>
        <div class="form__field"><label>Vencimento</label><input class="input" value="<?= e($cobranca['data_vencimento']) ?>" disabled></div>
        <div class="form__field"><label>Valor original</label><input class="input" value="R$ <?= e(number_format((float) $cobranca['valor_original'], 2, ',', '.')) ?>" disabled></div>
        <div class="form__field"><label>Desconto</label><input class="input" value="R$ <?= e(number_format((float) $cobranca['valor_desconto'], 2, ',', '.')) ?>" disabled></div>
        <div class="form__field"><label>Pago</label><input class="input" value="R$ <?= e(number_format((float) $pago, 2, ',', '.')) ?>" disabled></div>
        <div class="form__field"><label>Saldo</label><input class="input" value="R$ <?= e(number_format($saldo, 2, ',', '.')) ?>" disabled></div>
    </div>
</section>

<section class="card">
    <h3>Itens</h3>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Descrição</th>
                    <th scope="col">Qtd.</th>
                    <th scope="col">Valor unitário</th>
                    <th scope="col">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens as $item): ?>
                    <tr>
                        <td><?= e($item['descricao']) ?></td>
                        <td><?= e(number_format((float) $item['quantidade'], 2, ',', '.')) ?></td>
                        <td>R$ <?= e(number_format((float) $item['valor_unitario'], 2, ',', '.')) ?></td>
                        <td>R$ <?= e(number_format((float) $item['valor_total'], 2, ',', '.')) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($itens === []): ?>
                    <tr><td colspan="4" class="text-muted">Sem itens.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

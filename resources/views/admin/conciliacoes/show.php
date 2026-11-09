<?php $canEdit = isset($can) && $can('financeiro.editar'); ?>
<section class="page-heading">
    <div>
        <h2>Conciliação <?= e($conciliacao['competencia']) ?></h2>
        <p class="text-muted"><?= e($conciliacao['banco']) ?> · <?= e($conciliacao['unidade_nome']) ?> · <?= e($conciliacao['status']) ?></p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/conciliacoes">Voltar</a>
</section>

<?php if ($canEdit && $conciliacao['status'] !== 'FECHADA'): ?>
<section class="card">
    <h3>Importar extrato</h3>
    <p class="text-muted">Uma linha por movimento: <code>data;descrição;valor;tipo</code> (tipo ENTRADA ou SAIDA).</p>
    <form method="post" action="/admin/conciliacoes/<?= e((string) $conciliacao['id']) ?>/importar" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">
        <div class="form__field">
            <label for="csv">Linhas do extrato</label>
            <textarea class="input" id="csv" name="csv" rows="8" placeholder="2026-11-05;Mensalidade João;150,00;ENTRADA" required></textarea>
        </div>
        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Importar</button>
        </div>
    </form>
</section>
<?php endif; ?>

<section class="card">
    <h3>Itens</h3>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Data</th>
                    <th scope="col">Descrição</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Valor</th>
                    <th scope="col">Movimento vinculado</th>
                    <th scope="col">Status</th>
                    <th scope="col">Diferença</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens as $item): ?>
                    <tr>
                        <td><?= e($item['data']) ?></td>
                        <td><?= e($item['descricao'] ?? '—') ?></td>
                        <td><?= e($item['tipo']) ?></td>
                        <td>R$ <?= e(number_format((float) $item['valor'], 2, ',', '.')) ?></td>
                        <td><?= e($item['movimento_descricao'] ?? '—') ?></td>
                        <td><span class="badge <?= $item['status'] === 'CONCILIADO' ? 'badge--success' : 'badge--warning' ?>"><?= e($item['status']) ?></span></td>
                        <td>R$ <?= e(number_format((float) $item['diferenca'], 2, ',', '.')) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($itens === []): ?>
                    <tr><td colspan="7" class="text-muted">Nenhum item importado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php if ($canEdit && $conciliacao['status'] !== 'FECHADA'): ?>
<section class="card">
    <form method="post" action="/admin/conciliacoes/<?= e((string) $conciliacao['id']) ?>/fechar" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">
        <div class="form__actions">
            <button class="btn btn-primary" type="submit" data-confirm="Fechar a conciliação?">Fechar conciliação</button>
        </div>
    </form>
</section>
<?php endif; ?>

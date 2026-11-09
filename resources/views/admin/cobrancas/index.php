<?php $canEdit = isset($can) && $can('financeiro.editar'); ?>
<section class="page-heading">
    <div>
        <h2>Cobranças</h2>
        <p class="text-muted">Mensalidades e cobranças avulsas por aluno.</p>
    </div>
    <?php if ($canEdit): ?>
        <a class="btn btn-primary" href="/admin/cobrancas/nova">Nova cobrança</a>
    <?php endif; ?>
</section>

<section class="card">
    <form method="get" action="/admin/cobrancas" class="form-grid">
        <div class="form__field">
            <label for="busca">Buscar</label>
            <input class="input" id="busca" name="busca" value="<?= e($search ?? '') ?>" placeholder="Aluno ou número">
        </div>
        <div class="form__field">
            <label class="checkbox">
                <input type="checkbox" name="abertas" value="1" <?= !empty($onlyOpen) ? 'checked' : '' ?>> Somente abertas/vencidas
            </label>
        </div>
        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Filtrar</button>
            <a class="btn btn-ghost" href="/admin/cobrancas">Limpar</a>
        </div>
    </form>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Número</th>
                    <th scope="col">Aluno</th>
                    <th scope="col">Unidade</th>
                    <th scope="col">Competência</th>
                    <th scope="col">Vencimento</th>
                    <th scope="col">Valor</th>
                    <th scope="col">Status</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cobrancas as $cobranca): ?>
                    <tr>
                        <td><a href="/admin/cobrancas/<?= e((string) $cobranca['id']) ?>"><?= e($cobranca['numero']) ?></a></td>
                        <td><?= e($cobranca['aluno_nome']) ?></td>
                        <td><?= e($cobranca['unidade_nome']) ?></td>
                        <td><?= e($cobranca['competencia']) ?></td>
                        <td><?= e($cobranca['data_vencimento']) ?></td>
                        <td>R$ <?= e(number_format((float) $cobranca['valor_total'], 2, ',', '.')) ?></td>
                        <td><span class="badge <?= in_array($cobranca['status'], ['PAGA', 'PARCIAL'], true) ? 'badge--success' : 'badge--warning' ?>"><?= e($cobranca['status']) ?></span></td>
                        <td class="table__actions">
                            <a class="btn btn-ghost btn-sm" href="/admin/cobrancas/<?= e((string) $cobranca['id']) ?>">Detalhes</a>
                            <?php if ($canEdit && in_array($cobranca['status'], ['ABERTA', 'VENCIDA', 'PARCIAL'], true)): ?>
                                <a class="btn btn-primary btn-sm" href="/admin/pagamentos/novo?cobranca_id=<?= e((string) $cobranca['id']) ?>">Pagar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($cobrancas === []): ?>
                    <tr><td colspan="8" class="text-muted">Nenhuma cobrança encontrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

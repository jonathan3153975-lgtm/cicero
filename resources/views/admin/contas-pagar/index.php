<?php $canEdit = isset($can) && $can('financeiro.editar'); ?>
<section class="page-heading">
    <div>
        <h2>Contas a pagar</h2>
        <p class="text-muted">Despesas por competência, fornecedor e centro de custo.</p>
    </div>
    <?php if ($canEdit): ?>
        <a class="btn btn-primary" href="/admin/contas-pagar/nova">Nova conta</a>
    <?php endif; ?>
</section>

<section class="card">
    <form method="get" action="/admin/contas-pagar" class="form-grid">
        <div class="form__field">
            <label for="busca">Buscar</label>
            <input class="input" id="busca" name="busca" value="<?= e($search ?? '') ?>" placeholder="Descrição ou fornecedor">
        </div>
        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Buscar</button>
            <a class="btn btn-ghost" href="/admin/contas-pagar">Limpar</a>
        </div>
    </form>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Descrição</th>
                    <th scope="col">Unidade</th>
                    <th scope="col">Competência</th>
                    <th scope="col">Vencimento</th>
                    <th scope="col">Valor</th>
                    <th scope="col">Status</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contasPagar as $conta): ?>
                    <tr>
                        <td><strong><?= e($conta['descricao']) ?></strong><?= $conta['fornecedor_nome'] ? '<br><small class="text-muted">' . e($conta['fornecedor_nome']) . '</small>' : '' ?></td>
                        <td><?= e($conta['unidade_nome']) ?></td>
                        <td><?= e($conta['competencia']) ?></td>
                        <td><?= e($conta['data_vencimento']) ?></td>
                        <td>R$ <?= e(number_format((float) $conta['valor'], 2, ',', '.')) ?></td>
                        <td><span class="badge <?= $conta['status'] === 'PAGA' ? 'badge--success' : 'badge--warning' ?>"><?= e($conta['status']) ?></span></td>
                        <td class="table__actions">
                            <?php if ($canEdit && in_array($conta['status'], ['ABERTA', 'VENCIDA'], true)): ?>
                                <form method="post" action="/admin/contas-pagar/<?= e((string) $conta['id']) ?>/pagar" class="inline-form">
                                    <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">
                                    <select class="input input-sm" name="conta_bancaria_id" required>
                                        <option value="">Conta</option>
                                        <?php foreach ($contas as $item): ?>
                                            <?php if ((int) $item['unidade_id'] === (int) $conta['unidade_id']): ?>
                                                <option value="<?= e((string) $item['id']) ?>"><?= e($item['banco']) ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                    <input class="input input-sm" type="date" name="data_pagamento" value="<?= e(date('Y-m-d')) ?>" required>
                                    <button class="btn btn-primary btn-sm" type="submit">Pagar</button>
                                </form>
                                <form method="post" action="/admin/contas-pagar/<?= e((string) $conta['id']) ?>/cancelar" class="inline-form">
                                    <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">
                                    <button class="btn btn-ghost btn-sm" type="submit" data-confirm="Cancelar esta conta?">Cancelar</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($contasPagar === []): ?>
                    <tr><td colspan="7" class="text-muted">Nenhuma conta encontrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

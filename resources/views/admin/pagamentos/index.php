<?php $canEdit = isset($can) && $can('financeiro.editar'); ?>
<section class="page-heading">
    <div>
        <h2>Pagamentos</h2>
        <p class="text-muted">Recebimentos confirmados e estornos.</p>
    </div>
    <?php if ($canEdit): ?>
        <a class="btn btn-primary" href="/admin/pagamentos/novo">Registrar pagamento</a>
    <?php endif; ?>
</section>

<section class="card">
    <form method="get" action="/admin/pagamentos" class="form-grid">
        <div class="form__field">
            <label for="busca">Buscar</label>
            <input class="input" id="busca" name="busca" value="<?= e($search ?? '') ?>" placeholder="Nome do aluno">
        </div>
        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Buscar</button>
            <a class="btn btn-ghost" href="/admin/pagamentos">Limpar</a>
        </div>
    </form>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Aluno</th>
                    <th scope="col">Cobrança</th>
                    <th scope="col">Unidade</th>
                    <th scope="col">Data</th>
                    <th scope="col">Valor</th>
                    <th scope="col">Status</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pagamentos as $pagamento): ?>
                    <tr>
                        <td><?= e((string) $pagamento['id']) ?></td>
                        <td><?= e($pagamento['aluno_nome']) ?></td>
                        <td><?= e($pagamento['cobranca_numero'] ?? '—') ?></td>
                        <td><?= e($pagamento['unidade_nome']) ?></td>
                        <td><?= e($pagamento['data_pagamento']) ?></td>
                        <td>R$ <?= e(number_format((float) $pagamento['valor'], 2, ',', '.')) ?></td>
                        <td><span class="badge <?= $pagamento['status'] === 'CONFIRMADO' ? 'badge--success' : 'badge--warning' ?>"><?= e($pagamento['status']) ?></span></td>
                        <td class="table__actions">
                            <?php if ($canEdit && $pagamento['status'] === 'CONFIRMADO'): ?>
                                <form method="post" action="/admin/pagamentos/<?= e((string) $pagamento['id']) ?>/estornar" class="inline-form">
                                    <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">
                                    <input class="input input-sm" name="motivo" placeholder="Motivo do estorno" required>
                                    <button class="btn btn-ghost btn-sm" type="submit" data-confirm="Confirmar estorno deste pagamento?">Estornar</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($pagamentos === []): ?>
                    <tr><td colspan="8" class="text-muted">Nenhum pagamento encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

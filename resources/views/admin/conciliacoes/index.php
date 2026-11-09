<?php $canEdit = isset($can) && $can('financeiro.editar'); ?>
<section class="page-heading">
    <div>
        <h2>Conciliação bancária</h2>
        <p class="text-muted">Importe o extrato e concilie com os movimentos de caixa.</p>
    </div>
    <?php if ($canEdit): ?>
        <a class="btn btn-primary" href="/admin/conciliacoes/nova">Nova conciliação</a>
    <?php endif; ?>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Conta</th>
                    <th scope="col">Unidade</th>
                    <th scope="col">Competência</th>
                    <th scope="col">Importado em</th>
                    <th scope="col">Itens</th>
                    <th scope="col">Status</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($conciliacoes as $conciliacao): ?>
                    <tr>
                        <td><strong><?= e($conciliacao['banco']) ?></strong><?= $conciliacao['conta'] ? ' · ' . e($conciliacao['conta']) : '' ?></td>
                        <td><?= e($conciliacao['unidade_nome']) ?></td>
                        <td><?= e($conciliacao['competencia']) ?></td>
                        <td><?= e($conciliacao['importado_em'] ?? '—') ?></td>
                        <td><?= e((string) $conciliacao['itens_conciliados']) ?> / <?= e((string) $conciliacao['total_itens']) ?></td>
                        <td><span class="badge <?= $conciliacao['status'] === 'FECHADA' ? 'badge--success' : 'badge--warning' ?>"><?= e($conciliacao['status']) ?></span></td>
                        <td class="table__actions">
                            <a class="btn btn-ghost btn-sm" href="/admin/conciliacoes/<?= e((string) $conciliacao['id']) ?>">Abrir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($conciliacoes === []): ?>
                    <tr><td colspan="7" class="text-muted">Nenhuma conciliação registrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

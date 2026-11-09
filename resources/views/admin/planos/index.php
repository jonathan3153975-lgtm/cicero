<?php $canEdit = isset($can) && $can('plano.editar'); ?>
<section class="page-heading">
    <div>
        <h2>Planos</h2>
        <p class="text-muted">Catálogo comercial e preços por unidade.</p>
    </div>
    <?php if (isset($can) && $can('plano.criar')): ?>
        <a class="btn btn-primary" href="/admin/planos/novo">Novo plano</a>
    <?php endif; ?>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Plano</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Duração</th>
                    <th scope="col">Modalidades</th>
                    <th scope="col">Preços</th>
                    <th scope="col">Status</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($planos as $plano): ?>
                    <tr>
                        <td><strong><?= e($plano['nome']) ?></strong></td>
                        <td><?= e($plano['tipo']) ?></td>
                        <td><?= $plano['duracao_meses'] !== null ? e((string) $plano['duracao_meses']) . ' meses' : '—' ?></td>
                        <td><?= e((string) $plano['modality_count']) ?></td>
                        <td><?= e((string) $plano['price_count']) ?></td>
                        <td><span class="badge <?= $plano['status'] === 'ATIVO' ? 'badge--success' : 'badge--warning' ?>"><?= e($plano['status']) ?></span></td>
                        <td class="table__actions">
                            <?php if ($canEdit): ?>
                                <a class="btn btn-ghost btn-sm" href="/admin/planos/<?= e((string) $plano['id']) ?>/editar">Editar</a>
                                <a class="btn btn-ghost btn-sm" href="/admin/planos/<?= e((string) $plano['id']) ?>/precos">Preços</a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($planos === []): ?>
                    <tr><td colspan="7" class="text-muted">Nenhum plano encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

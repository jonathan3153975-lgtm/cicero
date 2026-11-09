<?php $canEdit = isset($can) && $can('regiao.editar'); ?>
<section class="page-heading">
    <div>
        <h2>Regiões</h2>
        <p class="text-muted">Agrupamento administrativo opcional de unidades próximas.</p>
    </div>
    <?php if (isset($can) && $can('regiao.criar')): ?>
        <a class="btn btn-primary" href="/admin/regioes/novo">Nova região</a>
    <?php endif; ?>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <?php if ($isPlatform): ?><th scope="col">Rede</th><?php endif; ?>
                    <th scope="col">Região</th>
                    <th scope="col">Status</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($regioes as $regiao): ?>
                    <tr>
                        <?php if ($isPlatform): ?><td><?= e($regiao['rede_nome'] ?? '—') ?></td><?php endif; ?>
                        <td><strong><?= e($regiao['nome']) ?></strong></td>
                        <td><span class="badge <?= $regiao['status'] === 'ATIVO' ? 'badge--success' : 'badge--warning' ?>"><?= e($regiao['status']) ?></span></td>
                        <td class="table__actions">
                            <?php if ($canEdit): ?>
                                <a class="btn btn-ghost btn-sm" href="/admin/regioes/<?= e((string) $regiao['id']) ?>/editar">Editar</a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($regioes === []): ?>
                    <tr><td colspan="<?= $isPlatform ? 4 : 3 ?>" class="text-muted">Nenhuma região encontrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php $canEdit = isset($can) && $can('rede.editar'); ?>
<section class="page-heading">
    <div>
        <h2>Redes</h2>
        <p class="text-muted">Empresas contratantes e seus planos.</p>
    </div>
    <?php if (isset($can) && $can('rede.criar')): ?>
        <a class="btn btn-primary" href="/admin/redes/novo">Nova rede</a>
    <?php endif; ?>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Nome fantasia</th>
                    <th scope="col">Razão social</th>
                    <th scope="col">CNPJ</th>
                    <th scope="col">Cidade</th>
                    <th scope="col">Status</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($redes as $rede): ?>
                    <tr>
                        <td><strong><?= e($rede['nome_fantasia']) ?></strong></td>
                        <td><?= e($rede['razao_social']) ?></td>
                        <td><?= e($rede['cnpj']) ?></td>
                        <td><?= e($rede['cidade'] ?? '—') ?></td>
                        <td><span class="badge <?= $rede['status'] === 'ATIVA' ? 'badge--success' : 'badge--warning' ?>"><?= e($rede['status']) ?></span></td>
                        <td class="table__actions">
                            <?php if ($canEdit): ?>
                                <a class="btn btn-ghost btn-sm" href="/admin/redes/<?= e((string) $rede['id']) ?>/editar">Editar</a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($redes === []): ?>
                    <tr><td colspan="6" class="text-muted">Nenhuma rede encontrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

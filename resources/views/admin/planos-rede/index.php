<?php $canEdit = isset($can) && $can('plano_rede.editar'); ?>
<section class="page-heading">
    <div>
        <h2>Planos de rede</h2>
        <p class="text-muted">Planos comerciais disponíveis para contratação pelas redes.</p>
    </div>
    <?php if (isset($can) && $can('plano_rede.criar')): ?>
        <a class="btn btn-primary" href="/admin/planos-rede/novo">Novo plano</a>
    <?php endif; ?>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Nome</th>
                    <th scope="col">Descrição</th>
                    <th scope="col">Status</th>
                    <th scope="col">Atualizado em</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($planos as $plano): ?>
                    <?php
                    $descricao = trim(strip_tags((string) ($plano['descricao'] ?? '')));
                    $resumo = $descricao !== '' ? mb_strimwidth($descricao, 0, 140, '…', 'UTF-8') : '—';
                    ?>
                    <tr>
                        <td><strong><?= e($plano['nome']) ?></strong></td>
                        <td><?= e($resumo) ?></td>
                        <td><span class="badge <?= $plano['status'] === 'ATIVO' ? 'badge--success' : 'badge--warning' ?>"><?= e($plano['status']) ?></span></td>
                        <td><?= e($plano['updated_at'] ?? '—') ?></td>
                        <td class="table__actions">
                            <?php if ($canEdit): ?>
                                <a class="btn btn-ghost btn-sm" href="/admin/planos-rede/<?= e((string) $plano['id']) ?>/editar">Editar</a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($planos === []): ?>
                    <tr><td colspan="5" class="text-muted">Nenhum plano de rede cadastrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

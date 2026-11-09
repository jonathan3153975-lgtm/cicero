<?php $canEdit = isset($can) && $can('modalidade.editar'); ?>
<section class="page-heading">
    <div>
        <h2>Modalidades</h2>
        <p class="text-muted">Atividades e modalidades incluídas nos planos.</p>
    </div>
    <?php if ($canEdit): ?>
        <a class="btn btn-primary" href="/admin/modalidades/nova">Nova modalidade</a>
    <?php endif; ?>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Nome</th>
                    <th scope="col">Descrição</th>
                    <th scope="col">Abrangência</th>
                    <th scope="col">Status</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modalidades as $modalidade): ?>
                    <tr>
                        <td><strong><?= e($modalidade['nome']) ?></strong></td>
                        <td><?= e($modalidade['descricao'] ?? '—') ?></td>
                        <td><?= $modalidade['rede_id'] === null ? 'Global' : e($modalidade['rede_nome'] ?? 'Rede') ?></td>
                        <td><span class="badge <?= $modalidade['status'] === 'ATIVO' ? 'badge--success' : 'badge--warning' ?>"><?= e($modalidade['status']) ?></span></td>
                        <td class="table__actions">
                            <?php if ($canEdit): ?>
                                <a class="btn btn-ghost btn-sm" href="/admin/modalidades/<?= e((string) $modalidade['id']) ?>/editar">Editar</a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($modalidades === []): ?>
                    <tr><td colspan="5" class="text-muted">Nenhuma modalidade encontrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

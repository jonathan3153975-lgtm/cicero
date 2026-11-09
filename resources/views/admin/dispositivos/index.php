<?php $canEdit = isset($can) && $can('dispositivo.gerenciar'); ?>
<section class="page-heading">
    <div>
        <h2>Dispositivos de acesso</h2>
        <p class="text-muted">Catracas e leitores vinculados às unidades.</p>
    </div>
    <?php if ($canEdit): ?>
        <a class="btn btn-primary" href="/admin/dispositivos/novo">Novo dispositivo</a>
    <?php endif; ?>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Dispositivo</th>
                    <th scope="col">Unidade</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Identificador</th>
                    <th scope="col">Direção</th>
                    <th scope="col">Status</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dispositivos as $dispositivo): ?>
                    <tr>
                        <td><strong><?= e($dispositivo['nome']) ?></strong></td>
                        <td><?= e($dispositivo['unidade_nome']) ?></td>
                        <td><?= e($dispositivo['tipo']) ?></td>
                        <td><code><?= e($dispositivo['identificador']) ?></code></td>
                        <td><?= e($dispositivo['direcao']) ?></td>
                        <td><span class="badge <?= $dispositivo['status'] === 'ATIVO' ? 'badge--success' : 'badge--warning' ?>"><?= e($dispositivo['status']) ?></span></td>
                        <td class="table__actions">
                            <?php if ($canEdit): ?>
                                <a class="btn btn-ghost btn-sm" href="/admin/dispositivos/<?= e((string) $dispositivo['id']) ?>/editar">Editar</a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($dispositivos === []): ?>
                    <tr><td colspan="7" class="text-muted">Nenhum dispositivo cadastrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php $canEdit = isset($can) && $can('unidade.editar'); ?>
<section class="page-heading">
    <div>
        <h2>Unidades</h2>
        <p class="text-muted">Academias físicas vinculadas às redes.</p>
    </div>
    <?php if (isset($can) && $can('unidade.criar')): ?>
        <a class="btn btn-primary" href="/admin/unidades/novo">Nova unidade</a>
    <?php endif; ?>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Unidade</th>
                    <th scope="col">Código</th>
                    <th scope="col">Rede</th>
                    <th scope="col">Cidade</th>
                    <th scope="col">Situação</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unidades as $unidade): ?>
                    <tr>
                        <td><strong><?= e($unidade['nome']) ?></strong></td>
                        <td><?= e($unidade['codigo_interno']) ?></td>
                        <td><?= e($unidade['rede_nome'] ?? '—') ?></td>
                        <td><?= e($unidade['cidade'] ?? '—') ?></td>
                        <td><span class="badge <?= $unidade['situacao'] === 'ATIVA' ? 'badge--success' : 'badge--warning' ?>"><?= e($unidade['situacao']) ?></span></td>
                        <td class="table__actions">
                            <?php if ($canEdit): ?>
                                <a class="btn btn-ghost btn-sm" href="/admin/unidades/<?= e((string) $unidade['id']) ?>/editar">Editar</a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($unidades === []): ?>
                    <tr><td colspan="6" class="text-muted">Nenhuma unidade encontrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

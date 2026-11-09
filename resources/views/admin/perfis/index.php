<?php $canEditRoute = isset($can) && $can('perfil.editar'); ?>
<section class="page-heading">
    <div>
        <h2>Perfis</h2>
        <p class="text-muted">Perfis de sistema e perfis personalizados.</p>
    </div>
    <?php if (isset($can) && $can('perfil.criar')): ?>
        <a class="btn btn-primary" href="/admin/perfis/novo">Novo perfil</a>
    <?php endif; ?>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Perfil</th>
                    <th scope="col">Slug</th>
                    <th scope="col">Nível</th>
                    <th scope="col">Escopo</th>
                    <th scope="col">Permissões</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Status</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($perfis as $perfil): ?>
                    <tr>
                        <td><strong><?= e($perfil['nome']) ?></strong></td>
                        <td><code><?= e($perfil['slug']) ?></code></td>
                        <td><?= e((string) $perfil['nivel']) ?></td>
                        <td><?= e($perfil['escopo']) ?></td>
                        <td><?= e((string) $perfil['permission_count']) ?></td>
                        <td><span class="badge"><?= $perfil['sistema'] ? 'Sistema' : 'Personalizado' ?></span></td>
                        <td><span class="badge <?= $perfil['status'] === 'ATIVO' ? 'badge--success' : 'badge--warning' ?>"><?= e($perfil['status']) ?></span></td>
                        <td class="table__actions">
                            <?php if ($canEditRoute && !empty($perfil['can_edit'])): ?>
                                <a class="btn btn-ghost btn-sm" href="/admin/perfis/<?= e((string) $perfil['id']) ?>/editar">Editar</a>
                            <?php else: ?>
                                <span class="text-muted">Somente leitura</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

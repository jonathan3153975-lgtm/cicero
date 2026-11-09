<?php $canEdit = isset($can) && $can('usuario.editar'); ?>
<section class="page-heading">
    <div>
        <h2>Usuários</h2>
        <p class="text-muted">Cadastros únicos e vínculos com redes e unidades.</p>
    </div>
    <?php if (isset($can) && $can('usuario.criar')): ?>
        <a class="btn btn-primary" href="/admin/usuarios/novo">Novo usuário</a>
    <?php endif; ?>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Nome</th>
                    <th scope="col">E-mail</th>
                    <th scope="col">Status</th>
                    <th scope="col">Último login</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><strong><?= e($usuario['nome']) ?></strong></td>
                        <td><?= e($usuario['email']) ?></td>
                        <td><span class="badge <?= $usuario['status'] === 'ATIVO' ? 'badge--success' : 'badge--danger' ?>"><?= e($usuario['status']) ?></span></td>
                        <td><?= e($usuario['ultimo_login_em'] ?? '—') ?></td>
                        <td class="table__actions">
                            <?php if ($canEdit): ?>
                                <a class="btn btn-ghost btn-sm" href="/admin/usuarios/<?= e((string) $usuario['id']) ?>/editar">Editar</a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($usuarios === []): ?>
                    <tr><td colspan="5" class="text-muted">Nenhum usuário encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

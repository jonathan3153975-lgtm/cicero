<section class="page-heading">
    <div>
        <h2>Permissões</h2>
        <p class="text-muted">Catálogo <code>modulo.acao</code>. Alterações entram por seed ou migration.</p>
    </div>
    <span class="badge"><?= e((string) $total) ?> permissões</span>
</section>

<?php foreach ($groups as $module => $permissions): ?>
    <section class="card">
        <header class="card__header">
            <div>
                <h3><?= e($module) ?></h3>
                <p class="text-muted"><?= e((string) count($permissions)) ?> permissões</p>
            </div>
        </header>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Slug</th>
                        <th scope="col">Ação</th>
                        <th scope="col">Descrição</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($permissions as $permission): ?>
                        <tr>
                            <td><code><?= e($permission['slug']) ?></code></td>
                            <td><?= e($permission['acao']) ?></td>
                            <td class="text-muted"><?= e($permission['descricao'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endforeach; ?>

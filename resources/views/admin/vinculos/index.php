<?php $canRevoke = isset($can) && $can('vinculo.editar'); ?>
<section class="page-heading">
    <div>
        <h2>Vínculos</h2>
        <p class="text-muted">Perfis atribuídos com escopo de rede, unidade ou região.</p>
    </div>
    <?php if (isset($can) && $can('vinculo.criar')): ?>
        <a class="btn btn-primary" href="/admin/vinculos/novo">Novo vínculo</a>
    <?php endif; ?>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Usuário</th>
                    <th scope="col">Perfil</th>
                    <th scope="col">Escopo</th>
                    <th scope="col">Nível</th>
                    <th scope="col">Status</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vinculos as $vinculo): ?>
                    <?php
                    $escopo = array_values(array_filter([
                        $vinculo['rede_nome'] ?? null,
                        $vinculo['unidade_nome'] ?? null,
                        $vinculo['regiao_nome'] ?? null,
                    ]));
                    ?>
                    <tr>
                        <td>
                            <strong><?= e($vinculo['usuario_nome']) ?></strong><br>
                            <small class="text-muted"><?= e($vinculo['usuario_email']) ?></small>
                        </td>
                        <td><code><?= e($vinculo['perfil_slug']) ?></code></td>
                        <td><?= e($escopo === [] ? 'Plataforma' : implode(' · ', $escopo)) ?></td>
                        <td><?= e((string) $vinculo['nivel']) ?></td>
                        <td><span class="badge <?= $vinculo['status'] === 'ATIVO' ? 'badge--success' : 'badge--warning' ?>"><?= e($vinculo['status']) ?></span></td>
                        <td class="table__actions">
                            <?php if ($canRevoke && $vinculo['status'] === 'ATIVO'): ?>
                                <form method="post" action="/admin/vinculos/<?= e((string) $vinculo['id']) ?>/revogar">
                                    <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">
                                    <button class="btn btn-ghost btn-sm" type="submit">Revogar</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($vinculos === []): ?>
                    <tr><td colspan="6" class="text-muted">Nenhum vínculo encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php $canEdit = isset($can) && $can('pessoa.editar'); ?>
<section class="page-heading">
    <div>
        <h2>Pessoas</h2>
        <p class="text-muted">Cadastro unificado de alunos, responsáveis, funcionários e fornecedores.</p>
    </div>
    <?php if (isset($can) && $can('pessoa.criar')): ?>
        <a class="btn btn-primary" href="/admin/pessoas/novo">Nova pessoa</a>
    <?php endif; ?>
</section>

<section class="card">
    <form method="get" action="/admin/pessoas" class="form-grid">
        <div class="form__field">
            <label for="busca">Buscar</label>
            <input class="input" id="busca" name="busca" value="<?= e($search ?? '') ?>" placeholder="Nome, CPF/CNPJ ou e-mail">
        </div>
        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Buscar</button>
            <a class="btn btn-ghost" href="/admin/pessoas">Limpar</a>
        </div>
    </form>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Nome</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">CPF/CNPJ</th>
                    <th scope="col">E-mail</th>
                    <th scope="col">Telefone</th>
                    <th scope="col">Status</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pessoas as $pessoa): ?>
                    <tr>
                        <td><strong><?= e($pessoa['nome']) ?></strong></td>
                        <td><?= e($pessoa['tipo']) ?></td>
                        <td><?= e($pessoa['cpf'] ?: ($pessoa['cnpj'] ?: '—')) ?></td>
                        <td><?= e($pessoa['email_principal'] ?? '—') ?></td>
                        <td><?= e($pessoa['telefone_principal'] ?? '—') ?></td>
                        <td><span class="badge <?= $pessoa['status'] === 'ATIVO' ? 'badge--success' : 'badge--warning' ?>"><?= e($pessoa['status']) ?></span></td>
                        <td class="table__actions">
                            <?php if ($canEdit): ?>
                                <a class="btn btn-ghost btn-sm" href="/admin/pessoas/<?= e((string) $pessoa['id']) ?>/editar">Editar</a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($pessoas === []): ?>
                    <tr><td colspan="7" class="text-muted">Nenhuma pessoa encontrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

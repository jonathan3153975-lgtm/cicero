<?php $canEdit = isset($can) && $can('aluno.editar'); ?>
<section class="page-heading">
    <div>
        <h2>Alunos</h2>
        <p class="text-muted">Alunos vinculados à rede e à unidade.</p>
    </div>
    <?php if (isset($can) && $can('aluno.criar')): ?>
        <a class="btn btn-primary" href="/admin/alunos/novo">Novo aluno</a>
    <?php endif; ?>
</section>

<section class="card">
    <form method="get" action="/admin/alunos" class="form-grid">
        <div class="form__field">
            <label for="busca">Buscar</label>
            <input class="input" id="busca" name="busca" value="<?= e($search ?? '') ?>" placeholder="Nome, CPF, e-mail ou código">
        </div>
        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Buscar</button>
            <a class="btn btn-ghost" href="/admin/alunos">Limpar</a>
        </div>
    </form>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Aluno</th>
                    <th scope="col">Unidade</th>
                    <th scope="col">Código</th>
                    <th scope="col">Entrada</th>
                    <th scope="col">Status</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alunos as $aluno): ?>
                    <tr>
                        <td>
                            <strong><?= e($aluno['nome']) ?></strong><br>
                            <small class="text-muted"><?= e($aluno['cpf'] ?: ($aluno['email_principal'] ?? '—')) ?></small>
                        </td>
                        <td><?= e($aluno['unidade_nome']) ?></td>
                        <td><?= e($aluno['codigo'] ?? '—') ?></td>
                        <td><?= e($aluno['data_entrada'] ?? '—') ?></td>
                        <td><span class="badge <?= $aluno['status'] === 'ATIVO' ? 'badge--success' : 'badge--warning' ?>"><?= e($aluno['status']) ?></span></td>
                        <td class="table__actions">
                            <?php if ($canEdit): ?>
                                <a class="btn btn-ghost btn-sm" href="/admin/alunos/<?= e((string) $aluno['id']) ?>/editar">Editar</a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($alunos === []): ?>
                    <tr><td colspan="6" class="text-muted">Nenhum aluno encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

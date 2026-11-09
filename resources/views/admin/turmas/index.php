<?php $canEdit = isset($can) && $can('turma.editar'); ?>
<section class="page-heading">
    <div>
        <h2>Turmas</h2>
        <p class="text-muted">Turmas, capacidade e horários por unidade.</p>
    </div>
    <?php if (isset($can) && $can('turma.criar')): ?>
        <a class="btn btn-primary" href="/admin/turmas/novo">Nova turma</a>
    <?php endif; ?>
</section>

<section class="card">
    <form method="get" action="/admin/turmas" class="form-grid">
        <div class="form__field">
            <label for="busca">Buscar</label>
            <input class="input" id="busca" name="busca" value="<?= e($search ?? '') ?>" placeholder="Nome da turma">
        </div>
        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Buscar</button>
            <a class="btn btn-ghost" href="/admin/turmas">Limpar</a>
        </div>
    </form>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Turma</th>
                    <th scope="col">Unidade</th>
                    <th scope="col">Modalidade</th>
                    <th scope="col">Capacidade</th>
                    <th scope="col">Sala</th>
                    <th scope="col">Status</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($turmas as $turma): ?>
                    <tr>
                        <td><strong><?= e($turma['nome']) ?></strong></td>
                        <td><?= e($turma['unidade_nome']) ?></td>
                        <td><?= e($turma['modalidade_nome'] ?? '—') ?></td>
                        <td><?= e($turma['capacidade'] !== null ? (string) $turma['capacidade'] : '—') ?></td>
                        <td><?= e($turma['sala'] ?? '—') ?></td>
                        <td><span class="badge <?= $turma['status'] === 'ATIVA' ? 'badge--success' : 'badge--warning' ?>"><?= e($turma['status']) ?></span></td>
                        <td class="table__actions">
                            <?php if ($canEdit): ?>
                                <a class="btn btn-ghost btn-sm" href="/admin/turmas/<?= e((string) $turma['id']) ?>/editar">Editar</a>
                                <a class="btn btn-ghost btn-sm" href="/admin/turmas/<?= e((string) $turma['id']) ?>/horarios">Horários</a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($turmas === []): ?>
                    <tr><td colspan="7" class="text-muted">Nenhuma turma encontrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

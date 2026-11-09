<?php $canEdit = isset($can) && $can('matricula.editar'); ?>
<section class="page-heading">
    <div>
        <h2>Matrículas</h2>
        <p class="text-muted">Matrículas ativas, suspensas e encerradas.</p>
    </div>
    <?php if (isset($can) && $can('matricula.criar')): ?>
        <a class="btn btn-primary" href="/admin/matriculas/novo">Nova matrícula</a>
    <?php endif; ?>
</section>

<section class="card">
    <form method="get" action="/admin/matriculas" class="form-grid">
        <div class="form__field">
            <label for="busca">Buscar</label>
            <input class="input" id="busca" name="busca" value="<?= e($search ?? '') ?>" placeholder="Nome ou CPF do aluno">
        </div>
        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Buscar</button>
            <a class="btn btn-ghost" href="/admin/matriculas">Limpar</a>
        </div>
    </form>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Aluno</th>
                    <th scope="col">Plano</th>
                    <th scope="col">Unidade</th>
                    <th scope="col">Início</th>
                    <th scope="col">Encerramento</th>
                    <th scope="col">Status</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($matriculas as $matricula): ?>
                    <tr>
                        <td><strong><?= e($matricula['aluno_nome']) ?></strong></td>
                        <td><?= e($matricula['plano_nome']) ?></td>
                        <td><?= e($matricula['unidade_nome']) ?></td>
                        <td><?= e($matricula['data_inicio'] ?? $matricula['data_matricula']) ?></td>
                        <td><?= e($matricula['data_encerramento'] ?? '—') ?></td>
                        <td><span class="badge <?= $matricula['status'] === 'ATIVA' ? 'badge--success' : 'badge--warning' ?>"><?= e($matricula['status']) ?></span></td>
                        <td class="table__actions">
                            <?php if ($canEdit): ?>
                                <a class="btn btn-ghost btn-sm" href="/admin/matriculas/<?= e((string) $matricula['id']) ?>/editar">Editar</a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($matriculas === []): ?>
                    <tr><td colspan="7" class="text-muted">Nenhuma matrícula encontrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php $canCancel = isset($can) && $can('reserva.cancelar'); ?>
<section class="page-heading">
    <div>
        <h2>Reservas</h2>
        <p class="text-muted">Reservas de horários de turma por aluno.</p>
    </div>
    <?php if (isset($can) && $can('reserva.criar')): ?>
        <a class="btn btn-primary" href="/admin/reservas/nova">Nova reserva</a>
    <?php endif; ?>
</section>

<section class="card">
    <form method="get" action="/admin/reservas" class="form-grid">
        <div class="form__field">
            <label for="busca">Buscar</label>
            <input class="input" id="busca" name="busca" value="<?= e($search ?? '') ?>" placeholder="Nome do aluno">
        </div>
        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Buscar</button>
            <a class="btn btn-ghost" href="/admin/reservas">Limpar</a>
        </div>
    </form>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Data</th>
                    <th scope="col">Aluno</th>
                    <th scope="col">Turma</th>
                    <th scope="col">Horário</th>
                    <th scope="col">Unidade</th>
                    <th scope="col">Status</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservas as $reserva): ?>
                    <tr>
                        <td><?= e($reserva['data']) ?></td>
                        <td><strong><?= e($reserva['aluno_nome']) ?></strong></td>
                        <td><?= e($reserva['turma_nome']) ?></td>
                        <td><?= e($reserva['hora_inicio']) ?>–<?= e($reserva['hora_fim']) ?></td>
                        <td><?= e($reserva['unidade_nome']) ?></td>
                        <td><span class="badge <?= $reserva['status'] === 'CONFIRMADA' ? 'badge--success' : 'badge--warning' ?>"><?= e($reserva['status']) ?></span></td>
                        <td class="table__actions">
                            <?php if ($canCancel && $reserva['status'] === 'CONFIRMADA'): ?>
                                <form method="post" action="/admin/reservas/<?= e((string) $reserva['id']) ?>/cancelar">
                                    <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">
                                    <button class="btn btn-ghost btn-sm" type="submit">Cancelar</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($reservas === []): ?>
                    <tr><td colspan="7" class="text-muted">Nenhuma reserva encontrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

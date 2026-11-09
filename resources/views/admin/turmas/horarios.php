<?php
$old = $old ?? [];
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $default);
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
$dias = [1 => 'Segunda', 2 => 'Terça', 3 => 'Quarta', 4 => 'Quinta', 5 => 'Sexta', 6 => 'Sábado', 7 => 'Domingo'];
?>
<section class="page-heading">
    <div>
        <h2>Horários — <?= e($turma['nome']) ?></h2>
        <p class="text-muted"><?= e($turma['unidade_nome']) ?></p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/turmas/<?= e((string) $turma['id']) ?>/editar">Editar turma</a>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Dia</th>
                    <th scope="col">Início</th>
                    <th scope="col">Fim</th>
                    <th scope="col">Professor</th>
                    <th scope="col">Status</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($horarios as $horario): ?>
                    <tr>
                        <td><?= e($dias[(int) $horario['dia_semana']] ?? (string) $horario['dia_semana']) ?></td>
                        <td><?= e($horario['hora_inicio']) ?></td>
                        <td><?= e($horario['hora_fim']) ?></td>
                        <td><?= e($horario['professor_nome'] ?? '—') ?></td>
                        <td><span class="badge <?= $horario['status'] === 'ATIVO' ? 'badge--success' : 'badge--warning' ?>"><?= e($horario['status']) ?></span></td>
                        <td class="table__actions">
                            <?php if ($horario['status'] === 'ATIVO'): ?>
                                <form method="post" action="/admin/turmas/<?= e((string) $turma['id']) ?>/horarios/<?= e((string) $horario['id']) ?>/remover">
                                    <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">
                                    <button class="btn btn-ghost btn-sm" type="submit">Remover</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($horarios === []): ?>
                    <tr><td colspan="6" class="text-muted">Nenhum horário cadastrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card">
    <header class="card__header">
        <div>
            <h3>Adicionar horário</h3>
            <p class="text-muted">Horários sobrepostos na mesma turma são bloqueados.</p>
        </div>
    </header>

    <form method="post" action="/admin/turmas/<?= e((string) $turma['id']) ?>/horarios" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">

        <div class="form-grid">
            <div class="form__field">
                <label for="dia_semana">Dia da semana</label>
                <select class="input <?= $err('dia_semana') ? 'is-invalid' : '' ?>" id="dia_semana" name="dia_semana" required>
                    <option value="">Selecione</option>
                    <?php foreach ($dias as $dia => $label): ?>
                        <option value="<?= e((string) $dia) ?>" <?= $val('dia_semana') === (string) $dia ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($err('dia_semana')): ?><p class="form__error"><?= e($err('dia_semana')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="hora_inicio">Hora inicial</label>
                <input class="input <?= $err('hora_inicio') ? 'is-invalid' : '' ?>" type="time" id="hora_inicio" name="hora_inicio" value="<?= e($val('hora_inicio')) ?>" required>
                <?php if ($err('hora_inicio')): ?><p class="form__error"><?= e($err('hora_inicio')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="hora_fim">Hora final</label>
                <input class="input <?= $err('hora_fim') ? 'is-invalid' : '' ?>" type="time" id="hora_fim" name="hora_fim" value="<?= e($val('hora_fim')) ?>" required>
                <?php if ($err('hora_fim')): ?><p class="form__error"><?= e($err('hora_fim')) ?></p><?php endif; ?>
            </div>
        </div>

        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Adicionar horário</button>
        </div>
    </form>
</section>

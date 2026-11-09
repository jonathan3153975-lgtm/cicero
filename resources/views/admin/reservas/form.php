<?php
$old = $old ?? [];
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $default);
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
$dias = [1 => 'Segunda', 2 => 'Terça', 3 => 'Quarta', 4 => 'Quinta', 5 => 'Sexta', 6 => 'Sábado', 7 => 'Domingo'];
?>
<section class="page-heading">
    <div>
        <h2>Nova reserva</h2>
        <p class="text-muted">Selecione aluno, horário e data.</p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/reservas">Voltar</a>
</section>

<section class="card">
    <form method="post" action="/admin/reservas" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">

        <div class="form-grid">
            <div class="form__field">
                <label for="aluno_id">Aluno</label>
                <select class="input <?= $err('aluno_id') ? 'is-invalid' : '' ?>" id="aluno_id" name="aluno_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($alunos as $aluno): ?>
                        <option value="<?= e((string) $aluno['id']) ?>" <?= $val('aluno_id') === (string) $aluno['id'] ? 'selected' : '' ?>>
                            <?= e($aluno['nome']) ?> · <?= e($aluno['unidade_nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($err('aluno_id')): ?><p class="form__error"><?= e($err('aluno_id')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="turma_horario_id">Horário da turma</label>
                <select class="input <?= $err('turma_horario_id') ? 'is-invalid' : '' ?>" id="turma_horario_id" name="turma_horario_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($horarios as $horario): ?>
                        <option value="<?= e((string) $horario['id']) ?>" <?= $val('turma_horario_id') === (string) $horario['id'] ? 'selected' : '' ?>>
                            <?= e($horario['turma_nome']) ?> · <?= e($dias[(int) $horario['dia_semana']] ?? '') ?> · <?= e($horario['hora_inicio']) ?>–<?= e($horario['hora_fim']) ?> · <?= e($horario['unidade_nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($err('turma_horario_id')): ?><p class="form__error"><?= e($err('turma_horario_id')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="data">Data da reserva</label>
                <input class="input <?= $err('data') ? 'is-invalid' : '' ?>" type="date" id="data" name="data" value="<?= e($val('data', date('Y-m-d'))) ?>" required>
                <?php if ($err('data')): ?><p class="form__error"><?= e($err('data')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="observacao">Observação</label>
                <input class="input" id="observacao" name="observacao" value="<?= e($val('observacao')) ?>">
            </div>
        </div>

        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Reservar</button>
            <a class="btn btn-ghost" href="/admin/reservas">Cancelar</a>
        </div>
    </form>
</section>

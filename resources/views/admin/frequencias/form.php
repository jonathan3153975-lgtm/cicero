<?php
$old = $old ?? [];
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $default);
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
?>
<section class="page-heading">
    <div>
        <h2>Registrar frequência</h2>
        <p class="text-muted">Registre presença ou ausência de um aluno.</p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/frequencias">Voltar</a>
</section>

<section class="card">
    <form method="post" action="/admin/frequencias/registrar" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">

        <div class="form-grid">
            <div class="form__field">
                <label for="turma_id">Turma</label>
                <select class="input <?= $err('turma_id') ? 'is-invalid' : '' ?>" id="turma_id" name="turma_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($turmas as $turma): ?>
                        <option value="<?= e((string) $turma['id']) ?>" <?= $val('turma_id') === (string) $turma['id'] ? 'selected' : '' ?>>
                            <?= e($turma['nome']) ?> · <?= e($turma['unidade_nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($err('turma_id')): ?><p class="form__error"><?= e($err('turma_id')) ?></p><?php endif; ?>
            </div>
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
                <label for="data">Data</label>
                <input class="input <?= $err('data') ? 'is-invalid' : '' ?>" type="date" id="data" name="data" value="<?= e($val('data', date('Y-m-d'))) ?>" required>
                <?php if ($err('data')): ?><p class="form__error"><?= e($err('data')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="presenca">Presença</label>
                <select class="input" id="presenca" name="presenca">
                    <option value="1" <?= $val('presenca', '1') === '1' ? 'selected' : '' ?>>Presente</option>
                    <option value="0" <?= $val('presenca') === '0' ? 'selected' : '' ?>>Ausente</option>
                </select>
            </div>
            <div class="form__field">
                <label for="origem">Origem</label>
                <select class="input" id="origem" name="origem">
                    <?php foreach (['MANUAL', 'CATRACA', 'APP'] as $origem): ?>
                        <option value="<?= e($origem) ?>" <?= $val('origem', 'MANUAL') === $origem ? 'selected' : '' ?>><?= e($origem) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Registrar</button>
            <a class="btn btn-ghost" href="/admin/frequencias">Cancelar</a>
        </div>
    </form>
</section>

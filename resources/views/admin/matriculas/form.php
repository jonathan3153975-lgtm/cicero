<?php
$old = $old ?? [];
$matricula = $matricula ?? [];
$isEdit = $matricula !== [];
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $matricula[$key] ?? $default);
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
$action = $isEdit ? '/admin/matriculas/' . $matricula['id'] : '/admin/matriculas';
?>
<section class="page-heading">
    <div>
        <h2><?= e($title) ?></h2>
        <p class="text-muted"><?= $isEdit ? 'Situação e datas da matrícula.' : 'Vincule o aluno a um preço de plano ativo.' ?></p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/matriculas">Voltar</a>
</section>

<section class="card">
    <form method="post" action="<?= e($action) ?>" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">

        <?php if (!$isEdit): ?>
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
                    <label for="plano_unidade_preco_id">Preço do plano</label>
                    <select class="input <?= $err('plano_unidade_preco_id') ? 'is-invalid' : '' ?>" id="plano_unidade_preco_id" name="plano_unidade_preco_id" required>
                        <option value="">Selecione</option>
                        <?php foreach ($precos as $preco): ?>
                            <option value="<?= e((string) $preco['id']) ?>" <?= $val('plano_unidade_preco_id') === (string) $preco['id'] ? 'selected' : '' ?>>
                                <?= e($preco['plano_nome']) ?> · <?= e($preco['unidade_nome']) ?> · R$ <?= e(number_format((float) $preco['valor_mensal'], 2, ',', '.')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($err('plano_unidade_preco_id')): ?><p class="form__error"><?= e($err('plano_unidade_preco_id')) ?></p><?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="form-grid">
                <div class="form__field">
                    <label>Aluno</label>
                    <input class="input" value="<?= e($matricula['aluno_nome']) ?>" disabled>
                </div>
                <div class="form__field">
                    <label>Plano</label>
                    <input class="input" value="<?= e($matricula['plano_nome']) ?>" disabled>
                </div>
                <div class="form__field">
                    <label>Unidade</label>
                    <input class="input" value="<?= e($matricula['unidade_nome']) ?>" disabled>
                </div>
            </div>
        <?php endif; ?>

        <div class="form-grid">
            <div class="form__field">
                <label for="data_matricula">Data da matrícula</label>
                <input class="input <?= $err('data_matricula') ? 'is-invalid' : '' ?>" type="date" id="data_matricula" name="data_matricula" value="<?= e($val('data_matricula', date('Y-m-d'))) ?>" required>
                <?php if ($err('data_matricula')): ?><p class="form__error"><?= e($err('data_matricula')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="data_inicio">Data de início</label>
                <input class="input <?= $err('data_inicio') ? 'is-invalid' : '' ?>" type="date" id="data_inicio" name="data_inicio" value="<?= e($val('data_inicio', date('Y-m-d'))) ?>" required>
                <?php if ($err('data_inicio')): ?><p class="form__error"><?= e($err('data_inicio')) ?></p><?php endif; ?>
            </div>
            <?php if (!$isEdit): ?>
                <div class="form__field">
                    <label for="dia_vencimento">Dia de vencimento</label>
                    <input class="input" type="number" min="1" max="31" id="dia_vencimento" name="dia_vencimento" value="<?= e($val('dia_vencimento', '10')) ?>">
                </div>
            <?php endif; ?>
            <div class="form__field">
                <label for="status">Status</label>
                <select class="input" id="status" name="status">
                    <?php foreach (['ATIVA', 'SUSPENSA', 'CANCELADA', 'ENCERRADA', 'TRANSFERIDA'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= $val('status', 'ATIVA') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($isEdit): ?>
                <div class="form__field">
                    <label for="data_encerramento">Data de encerramento</label>
                    <input class="input" type="date" id="data_encerramento" name="data_encerramento" value="<?= e($val('data_encerramento')) ?>">
                </div>
                <div class="form__field">
                    <label for="motivo_encerramento">Motivo do encerramento</label>
                    <input class="input" id="motivo_encerramento" name="motivo_encerramento" value="<?= e($val('motivo_encerramento')) ?>">
                </div>
            <?php endif; ?>
            <div class="form__field">
                <label for="observacoes">Observações</label>
                <textarea class="input" id="observacoes" name="observacoes" rows="3"><?= e($val('observacoes')) ?></textarea>
            </div>
            <?php if (!$isEdit): ?>
                <div class="form__field">
                    <label class="checkbox"><input type="checkbox" name="renovacao_automatica" value="1"> Renovação automática</label>
                </div>
            <?php endif; ?>
        </div>

        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Salvar</button>
            <a class="btn btn-ghost" href="/admin/matriculas">Cancelar</a>
        </div>
    </form>
</section>

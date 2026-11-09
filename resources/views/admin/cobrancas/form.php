<?php
$old = $old ?? [];
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $default);
$competencia = date('Y-m');
?>
<section class="page-heading">
    <div>
        <h2><?= e($title) ?></h2>
        <p class="text-muted">Gere a mensalidade de uma matrícula ou crie uma cobrança avulsa.</p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/cobrancas">Voltar</a>
</section>

<section class="card">
    <h3>Gerar mensalidade da matrícula</h3>
    <form method="post" action="/admin/cobrancas" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">
        <input type="hidden" name="modo" value="matricula">
        <div class="form-grid">
            <div class="form__field">
                <label for="matricula_id">Matrícula</label>
                <select class="input <?= $err('matricula_id') ? 'is-invalid' : '' ?>" id="matricula_id" name="matricula_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($matriculas as $matricula): ?>
                        <option value="<?= e((string) $matricula['id']) ?>">
                            <?= e($matricula['aluno_nome']) ?> · <?= e($matricula['plano_nome']) ?> · <?= e($matricula['unidade_nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($err('matricula_id')): ?><p class="form__error"><?= e($err('matricula_id')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="competencia_matricula">Competência</label>
                <input class="input <?= $err('competencia') ? 'is-invalid' : '' ?>" id="competencia_matricula" type="month" name="competencia" value="<?= e($competencia) ?>" required>
                <?php if ($err('competencia')): ?><p class="form__error"><?= e($err('competencia')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="vencimento_matricula">Vencimento</label>
                <input class="input <?= $err('data_vencimento') ? 'is-invalid' : '' ?>" id="vencimento_matricula" type="date" name="data_vencimento" value="<?= e(date('Y-m-10')) ?>" required>
                <?php if ($err('data_vencimento')): ?><p class="form__error"><?= e($err('data_vencimento')) ?></p><?php endif; ?>
            </div>
        </div>
        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Gerar mensalidade</button>
        </div>
    </form>
</section>

<section class="card">
    <h3>Cobrança avulsa</h3>
    <form method="post" action="/admin/cobrancas" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">
        <input type="hidden" name="modo" value="manual">
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
                <label for="tipo">Tipo</label>
                <select class="input" id="tipo" name="tipo">
                    <?php foreach (['MENSALIDADE', 'SERVICO', 'MATERIAL', 'OUTRO'] as $tipo): ?>
                        <option value="<?= e($tipo) ?>" <?= $val('tipo', 'SERVICO') === $tipo ? 'selected' : '' ?>><?= e($tipo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form__field">
                <label for="competencia">Competência</label>
                <input class="input <?= $err('competencia') ? 'is-invalid' : '' ?>" id="competencia" type="month" name="competencia" value="<?= e($val('competencia', $competencia)) ?>" required>
                <?php if ($err('competencia')): ?><p class="form__error"><?= e($err('competencia')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="data_vencimento">Vencimento</label>
                <input class="input <?= $err('data_vencimento') ? 'is-invalid' : '' ?>" id="data_vencimento" type="date" name="data_vencimento" value="<?= e($val('data_vencimento', date('Y-m-10'))) ?>" required>
                <?php if ($err('data_vencimento')): ?><p class="form__error"><?= e($err('data_vencimento')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="valor">Valor</label>
                <input class="input <?= $err('valor') ? 'is-invalid' : '' ?>" id="valor" type="number" step="0.01" min="0.01" name="valor" value="<?= e($val('valor')) ?>" required>
                <?php if ($err('valor')): ?><p class="form__error"><?= e($err('valor')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="valor_desconto">Desconto</label>
                <input class="input <?= $err('valor_desconto') ? 'is-invalid' : '' ?>" id="valor_desconto" type="number" step="0.01" min="0" name="valor_desconto" value="<?= e($val('valor_desconto', '0')) ?>">
                <?php if ($err('valor_desconto')): ?><p class="form__error"><?= e($err('valor_desconto')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="descricao">Descrição</label>
                <input class="input" id="descricao" name="descricao" value="<?= e($val('descricao')) ?>" placeholder="Opcional">
            </div>
        </div>
        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Criar cobrança</button>
        </div>
    </form>
</section>

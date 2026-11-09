<?php
$old = $old ?? [];
$item = $item ?? [];
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $item[$key] ?? $default);
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
$action = $item !== [] ? '/admin/turmas/' . $item['id'] : '/admin/turmas';
?>
<section class="page-heading">
    <div>
        <h2><?= e($title) ?></h2>
        <p class="text-muted">Dados da turma e capacidade.</p>
    </div>
    <div class="page-heading__actions">
        <?php if ($item !== []): ?>
            <a class="btn btn-ghost btn-sm" href="/admin/turmas/<?= e((string) $item['id']) ?>/horarios">Horários</a>
        <?php endif; ?>
        <a class="btn btn-ghost btn-sm" href="/admin/turmas">Voltar</a>
    </div>
</section>

<section class="card">
    <form method="post" action="<?= e($action) ?>" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">

        <div class="form-grid">
            <?php if ($currentUnidadeId === null): ?>
                <div class="form__field">
                    <label for="unidade_id">Unidade</label>
                    <select class="input <?= $err('unidade_id') ? 'is-invalid' : '' ?>" id="unidade_id" name="unidade_id" required>
                        <option value="">Selecione</option>
                        <?php foreach ($unidades as $unidade): ?>
                            <option value="<?= e((string) $unidade['id']) ?>" <?= $val('unidade_id') === (string) $unidade['id'] ? 'selected' : '' ?>><?= e($unidade['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($err('unidade_id')): ?><p class="form__error"><?= e($err('unidade_id')) ?></p><?php endif; ?>
                </div>
            <?php else: ?>
                <input type="hidden" name="unidade_id" value="<?= e((string) $currentUnidadeId) ?>">
            <?php endif; ?>

            <div class="form__field">
                <label for="modalidade_id">Modalidade</label>
                <select class="input" id="modalidade_id" name="modalidade_id">
                    <option value="">Sem modalidade</option>
                    <?php foreach ($modalidades as $modalidade): ?>
                        <option value="<?= e((string) $modalidade['id']) ?>" <?= $val('modalidade_id') === (string) $modalidade['id'] ? 'selected' : '' ?>><?= e($modalidade['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form__field">
                <label for="nome">Nome da turma</label>
                <input class="input <?= $err('nome') ? 'is-invalid' : '' ?>" id="nome" name="nome" value="<?= e($val('nome')) ?>" required>
                <?php if ($err('nome')): ?><p class="form__error"><?= e($err('nome')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="capacidade">Capacidade</label>
                <input class="input <?= $err('capacidade') ? 'is-invalid' : '' ?>" type="number" min="0" id="capacidade" name="capacidade" value="<?= e($val('capacidade')) ?>">
                <?php if ($err('capacidade')): ?><p class="form__error"><?= e($err('capacidade')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="sala">Sala</label>
                <input class="input" id="sala" name="sala" value="<?= e($val('sala')) ?>">
            </div>
            <div class="form__field">
                <label for="status">Status</label>
                <select class="input" id="status" name="status">
                    <?php foreach (['ATIVA', 'INATIVA'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= $val('status', 'ATIVA') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Salvar</button>
            <a class="btn btn-ghost" href="/admin/turmas">Cancelar</a>
        </div>
    </form>
</section>

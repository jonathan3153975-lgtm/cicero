<?php
$old = $old ?? [];
$item = $item ?? [];
$selectedModalities = array_map('intval', $selectedModalities ?? []);
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $item[$key] ?? $default);
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
$action = $item !== [] ? '/admin/planos/' . $item['id'] : '/admin/planos';
$isEdit = $item !== [];
$checked = array_flip($selectedModalities);
?>
<section class="page-heading">
    <div>
        <h2><?= e($title) ?></h2>
        <p class="text-muted">Dados do plano e modalidades incluídas.</p>
    </div>
    <div class="page-heading__actions">
        <?php if ($isEdit): ?>
            <a class="btn btn-ghost btn-sm" href="/admin/planos/<?= e((string) $item['id']) ?>/precos">Preços</a>
        <?php endif; ?>
        <a class="btn btn-ghost btn-sm" href="/admin/planos">Voltar</a>
    </div>
</section>

<section class="card">
    <form method="post" action="<?= e($action) ?>" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">

        <div class="form-grid">
            <?php if ($isPlatform): ?>
                <div class="form__field">
                    <label for="rede_id">Rede</label>
                    <select class="input" id="rede_id" name="rede_id">
                        <option value="">Global (sem rede)</option>
                        <?php foreach ($redes as $rede): ?>
                            <option value="<?= e((string) $rede['id']) ?>" <?= $val('rede_id') === (string) $rede['id'] ? 'selected' : '' ?>><?= e($rede['nome_fantasia']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <input type="hidden" name="rede_id" value="<?= e((string) $currentRedeId) ?>">
            <?php endif; ?>

            <div class="form__field">
                <label for="nome">Nome do plano</label>
                <input class="input <?= $err('nome') ? 'is-invalid' : '' ?>" id="nome" name="nome" value="<?= e($val('nome')) ?>" required>
                <?php if ($err('nome')): ?><p class="form__error"><?= e($err('nome')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="tipo">Tipo</label>
                <select class="input" id="tipo" name="tipo">
                    <?php foreach (['MENSAL', 'ANUAL', 'PACOTE', 'AVULSO'] as $tipo): ?>
                        <option value="<?= e($tipo) ?>" <?= $val('tipo', 'MENSAL') === $tipo ? 'selected' : '' ?>><?= e($tipo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form__field">
                <label for="duracao_meses">Duração (meses)</label>
                <input class="input <?= $err('duracao_meses') ? 'is-invalid' : '' ?>" type="number" min="0" id="duracao_meses" name="duracao_meses" value="<?= e($val('duracao_meses')) ?>">
                <?php if ($err('duracao_meses')): ?><p class="form__error"><?= e($err('duracao_meses')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="aulas_semana">Aulas por semana</label>
                <input class="input <?= $err('aulas_semana') ? 'is-invalid' : '' ?>" type="number" min="0" id="aulas_semana" name="aulas_semana" value="<?= e($val('aulas_semana')) ?>">
                <?php if ($err('aulas_semana')): ?><p class="form__error"><?= e($err('aulas_semana')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="limite_modalidades">Limite de modalidades</label>
                <input class="input <?= $err('limite_modalidades') ? 'is-invalid' : '' ?>" type="number" min="0" id="limite_modalidades" name="limite_modalidades" value="<?= e($val('limite_modalidades')) ?>">
                <?php if ($err('limite_modalidades')): ?><p class="form__error"><?= e($err('limite_modalidades')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="status">Status</label>
                <select class="input" id="status" name="status">
                    <?php foreach (['ATIVO', 'INATIVO'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= $val('status', 'ATIVO') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form__field">
                <label for="descricao">Descrição</label>
                <textarea class="input" id="descricao" name="descricao" rows="3"><?= e($val('descricao')) ?></textarea>
            </div>
        </div>

        <div class="form__field">
            <label>Modalidades</label>
            <?php if ($err('modalidades')): ?><p class="form__error"><?= e($err('modalidades')) ?></p><?php endif; ?>
            <div class="checkbox-list">
                <?php foreach ($modalidades as $modalidade): ?>
                    <label class="checkbox">
                        <input type="checkbox" name="modalidades[]" value="<?= e((string) $modalidade['id']) ?>" <?= isset($checked[(int) $modalidade['id']]) ? 'checked' : '' ?>>
                        <span><?= e($modalidade['nome']) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Salvar</button>
            <a class="btn btn-ghost" href="/admin/planos">Cancelar</a>
        </div>
    </form>
</section>

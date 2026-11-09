<?php
$old = $old ?? [];
$item = $item ?? [];
$selected = $selected ?? [];
$oldPermissions = $old['permissoes'] ?? null;
$checked = is_array($oldPermissions) ? $oldPermissions : $selected;
$checkedMap = array_flip($checked);
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $item[$key] ?? $default);
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
$action = $item !== [] ? '/admin/perfis/' . $item['id'] : '/admin/perfis';
$maxLevel = (int) ($maxLevel ?? 10);
$allowedEscopos = $allowedEscopos ?? ['UNIDADE', 'REGIAO', 'REDE', 'PLATAFORMA'];
?>
<section class="page-heading">
    <div>
        <h2><?= e($title) ?></h2>
        <p class="text-muted">Nível, escopo e permissões do perfil.</p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/perfis">Voltar</a>
</section>

<section class="card">
    <form method="post" action="<?= e($action) ?>" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">

        <div class="form-grid">
            <div class="form__field">
                <label for="slug">Identificador (slug)</label>
                <input class="input <?= $err('slug') ? 'is-invalid' : '' ?>" id="slug" name="slug" value="<?= e($val('slug')) ?>" required>
                <?php if ($err('slug')): ?><p class="form__error"><?= e($err('slug')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="nome">Nome</label>
                <input class="input <?= $err('nome') ? 'is-invalid' : '' ?>" id="nome" name="nome" value="<?= e($val('nome')) ?>" required>
                <?php if ($err('nome')): ?><p class="form__error"><?= e($err('nome')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="nivel">Nível</label>
                <input class="input <?= $err('nivel') ? 'is-invalid' : '' ?>" type="number" min="1" max="<?= e((string) $maxLevel) ?>" id="nivel" name="nivel" value="<?= e($val('nivel', '10')) ?>" required>
                <?php if ($err('nivel')): ?><p class="form__error"><?= e($err('nivel')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="escopo">Escopo</label>
                <select class="input" id="escopo" name="escopo">
                    <?php foreach ($allowedEscopos as $escopo): ?>
                        <option value="<?= e($escopo) ?>" <?= $val('escopo', 'UNIDADE') === $escopo ? 'selected' : '' ?>><?= e($escopo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form__field">
                <label for="status">Status</label>
                <select class="input" id="status" name="status">
                    <?php foreach (['ATIVO', 'INATIVO'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= $val('status', 'ATIVO') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($item !== [] && !empty($item['sistema'])): ?>
                <div class="form__field">
                    <label>Tipo</label>
                    <input class="input" value="Perfil de sistema" disabled>
                </div>
            <?php endif; ?>
        </div>

        <div class="form__field">
            <label>Permissões</label>
            <?php if ($err('permissoes')): ?><p class="form__error"><?= e($err('permissoes')) ?></p><?php endif; ?>
            <div class="permission-groups">
                <?php foreach ($permissionGroups as $module => $permissions): ?>
                    <fieldset class="permission-group">
                        <legend><?= e($module) ?></legend>
                        <div class="checkbox-list">
                            <?php foreach ($permissions as $permission): ?>
                                <label class="checkbox">
                                    <input type="checkbox" name="permissoes[]" value="<?= e($permission['slug']) ?>" <?= isset($checkedMap[$permission['slug']]) ? 'checked' : '' ?>>
                                    <span><code><?= e($permission['slug']) ?></code></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                <?php endforeach; ?>
            </div>
        </div>

        <p class="text-muted">
            O backend valida o nível e as permissões concedidas. Não é permitido
            conceder permissões ou níveis superiores aos do seu perfil.
        </p>

        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Salvar</button>
            <a class="btn btn-ghost" href="/admin/perfis">Cancelar</a>
        </div>
    </form>
</section>

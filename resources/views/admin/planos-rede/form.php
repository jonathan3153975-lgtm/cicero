<?php
$old = $old ?? [];
$item = $item ?? [];
$isEdit = $item !== [];
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $item[$key] ?? $default);
$action = $isEdit ? '/admin/planos-rede/' . $item['id'] : '/admin/planos-rede';
?>
<section class="page-heading">
    <div>
        <h2><?= e($title) ?></h2>
        <p class="text-muted">Nome e descrição comercial do plano oferecido às redes.</p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/planos-rede">Voltar</a>
</section>

<section class="card">
    <form method="post" action="<?= e($action) ?>" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">
        <div class="row">
            <div class="col-sm-12 col-md-8">
                <div class="form__field">
                    <label for="nome">Nome</label>
                    <input class="input <?= $err('nome') ? 'is-invalid' : '' ?>" id="nome" name="nome" value="<?= e($val('nome')) ?>" required>
                    <?php if ($err('nome')): ?><p class="form__error"><?= e($err('nome')) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="status">Status</label>
                    <select class="input" id="status" name="status">
                        <?php foreach (['ATIVO', 'INATIVO'] as $status): ?>
                            <option value="<?= e($status) ?>" <?= $val('status', 'ATIVO') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="form__field">
                    <label for="descricao">Descrição</label>
                    <div class="rich-text" data-rich-text>
                        <div class="rich-text__toolbar" role="toolbar" aria-label="Formatação da descrição">
                            <button class="rich-text__button" type="button" data-command="bold" title="Negrito"><strong>B</strong></button>
                            <button class="rich-text__button" type="button" data-command="italic" title="Itálico"><em>I</em></button>
                            <button class="rich-text__button" type="button" data-command="insertUnorderedList" title="Lista com marcadores">•</button>
                            <button class="rich-text__button" type="button" data-command="insertOrderedList" title="Lista numerada">1.</button>
                            <button class="rich-text__button" type="button" data-command="formatBlock" data-value="h3" title="Título">H</button>
                            <button class="rich-text__button" type="button" data-command="removeFormat" title="Limpar formatação">Limpar</button>
                        </div>
                        <div
                            class="input rich-text__editor <?= $err('descricao') ? 'is-invalid' : '' ?>"
                            data-rich-editor
                            contenteditable="true"
                            role="textbox"
                            aria-multiline="true"
                            aria-labelledby="descricao"
                            data-placeholder="Descreva o plano, limites, benefícios e condições comerciais."
                        ></div>
                        <textarea class="input rich-text__fallback <?= $err('descricao') ? 'is-invalid' : '' ?>" id="descricao" name="descricao" rows="7" data-rich-input><?= e($val('descricao')) ?></textarea>
                    </div>
                    <?php if ($err('descricao')): ?><p class="form__error"><?= e($err('descricao')) ?></p><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Salvar</button>
            <a class="btn btn-ghost" href="/admin/planos-rede">Cancelar</a>
        </div>
    </form>
</section>

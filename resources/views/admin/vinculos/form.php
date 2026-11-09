<?php
$old = $old ?? [];
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $default);
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
?>
<section class="page-heading">
    <div>
        <h2><?= e($title) ?></h2>
        <p class="text-muted">Atribua um perfil a um usuário dentro do escopo permitido.</p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/vinculos">Voltar</a>
</section>

<section class="card">
    <form method="post" action="/admin/vinculos" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">

        <div class="form-grid">
            <div class="form__field">
                <label for="usuario_id">Usuário</label>
                <select class="input <?= $err('usuario_id') ? 'is-invalid' : '' ?>" id="usuario_id" name="usuario_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?= e((string) $usuario['id']) ?>" <?= $val('usuario_id') === (string) $usuario['id'] ? 'selected' : '' ?>>
                            <?= e($usuario['nome']) ?> · <?= e($usuario['email']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($err('usuario_id')): ?><p class="form__error"><?= e($err('usuario_id')) ?></p><?php endif; ?>
            </div>

            <div class="form__field">
                <label for="perfil_id">Perfil</label>
                <select class="input <?= $err('perfil_id') ? 'is-invalid' : '' ?>" id="perfil_id" name="perfil_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($perfis as $perfil): ?>
                        <option value="<?= e((string) $perfil['id']) ?>" <?= $val('perfil_id') === (string) $perfil['id'] ? 'selected' : '' ?>>
                            <?= e($perfil['nome']) ?> (nível <?= e((string) $perfil['nivel']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($err('perfil_id')): ?><p class="form__error"><?= e($err('perfil_id')) ?></p><?php endif; ?>
            </div>

            <?php if ($isPlatform): ?>
                <div class="form__field">
                    <label for="rede_id">Rede</label>
                    <select class="input" id="rede_id" name="rede_id">
                        <option value="">Sem rede (escopo global)</option>
                        <?php foreach ($redes as $rede): ?>
                            <option value="<?= e((string) $rede['id']) ?>" <?= $val('rede_id') === (string) $rede['id'] ? 'selected' : '' ?>><?= e($rede['nome_fantasia']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <input type="hidden" name="rede_id" value="<?= e((string) $currentRedeId) ?>">
            <?php endif; ?>

            <?php if ($currentUnidadeId === null): ?>
                <div class="form__field">
                    <label for="unidade_id">Unidade</label>
                    <select class="input" id="unidade_id" name="unidade_id">
                        <option value="">Sem unidade</option>
                        <?php foreach ($unidades as $unidade): ?>
                            <option value="<?= e((string) $unidade['id']) ?>" <?= $val('unidade_id') === (string) $unidade['id'] ? 'selected' : '' ?>>
                                <?= e($unidade['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <input type="hidden" name="unidade_id" value="<?= e((string) $currentUnidadeId) ?>">
            <?php endif; ?>

            <?php if ($currentUnidadeId === null && ($regioes ?? []) !== []): ?>
                <div class="form__field">
                    <label for="regiao_id">Região</label>
                    <select class="input" id="regiao_id" name="regiao_id">
                        <option value="">Sem região</option>
                        <?php foreach ($regioes as $regiao): ?>
                            <option value="<?= e((string) $regiao['id']) ?>" <?= $val('regiao_id') === (string) $regiao['id'] ? 'selected' : '' ?>>
                                <?= e($regiao['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <?php if ($err('vinculo')): ?><p class="form__error"><?= e($err('vinculo')) ?></p><?php endif; ?>
        </div>

        <p class="text-muted">
            Para perfis de plataforma, deixe rede, unidade e região vazias. O escopo
            é validado no backend e não pode exceder o seu.
        </p>

        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Salvar</button>
            <a class="btn btn-ghost" href="/admin/vinculos">Cancelar</a>
        </div>
    </form>
</section>

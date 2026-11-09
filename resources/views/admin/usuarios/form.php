<?php
$old = $old ?? [];
$item = $item ?? [];
$redes = $redes ?? [];
$regioes = $regioes ?? [];
$unidades = $unidades ?? [];
$isPlatform = $isPlatform ?? false;
$currentRedeId = $currentRedeId ?? null;
$currentRegiaoId = $currentRegiaoId ?? null;
$currentUnidadeId = $currentUnidadeId ?? null;
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $item[$key] ?? $default);
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
$action = $item !== [] ? '/admin/usuarios/' . $item['id'] : '/admin/usuarios';
$isEdit = $item !== [];
$selectedRede = $val('rede_id', $currentRedeId !== null ? (string) $currentRedeId : '');
$selectedRegiao = $val('regiao_id', $currentRegiaoId !== null ? (string) $currentRegiaoId : '');
$selectedUnidade = $val('unidade_id', $currentUnidadeId !== null ? (string) $currentUnidadeId : '');
?>
<section class="page-heading">
    <div>
        <h2><?= e($title) ?></h2>
        <p class="text-muted"><?= $isEdit ? 'Atualize os dados do usuário.' : 'Crie o usuário vinculando a uma pessoa cadastrada.' ?></p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/usuarios">Voltar</a>
</section>

<section class="card">
    <form method="post" action="<?= e($action) ?>" class="form" id="usuario-form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">

        <!-- Linha 1: Filtros Rede, Região, Unidade (apenas no create) -->
        <?php if (!$isEdit): ?>
        <div class="row">
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="rede_id">Rede <span class="text-danger">*</span></label>
                    <select class="input <?= $err('rede_id') ? 'is-invalid' : '' ?>" id="rede_id" name="rede_id" data-rede-select required>
                        <option value="">Selecione a rede</option>
                        <?php foreach ($redes as $rede): ?>
                            <option value="<?= e((string) $rede['id']) ?>" <?= $selectedRede === (string) $rede['id'] ? 'selected' : '' ?>>
                                <?= e($rede['nome_fantasia']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($err('rede_id')): ?><p class="form__error"><?= e($err('rede_id')) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="regiao_id">Região</label>
                    <select class="input <?= $err('regiao_id') ? 'is-invalid' : '' ?>" id="regiao_id" name="regiao_id" data-regiao-select>
                        <option value="">Todas as regiões</option>
                        <?php foreach ($regioes as $regiao): ?>
                            <option value="<?= e((string) $regiao['id']) ?>" data-rede-id="<?= e((string) $regiao['rede_id']) ?>" <?= $selectedRegiao === (string) $regiao['id'] ? 'selected' : '' ?>>
                                <?= e($regiao['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($err('regiao_id')): ?><p class="form__error"><?= e($err('regiao_id')) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="unidade_id">Unidade</label>
                    <select class="input <?= $err('unidade_id') ? 'is-invalid' : '' ?>" id="unidade_id" name="unidade_id">
                        <option value="">Todas as unidades</option>
                        <?php foreach ($unidades as $unidade): ?>
                            <option value="<?= e((string) $unidade['id']) ?>" data-regiao-id="<?= e((string) ($unidade['regiao_id'] ?? '')) ?>" <?= $selectedUnidade === (string) $unidade['id'] ? 'selected' : '' ?>>
                                <?= e($unidade['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($err('unidade_id')): ?><p class="form__error"><?= e($err('unidade_id')) ?></p><?php endif; ?>
                </div>
            </div>
        </div>
        <?php else: ?>
            <?php if ($isPlatform): ?>
                <input type="hidden" name="rede_id" value="<?= e((string) $currentRedeId) ?>">
            <?php else: ?>
                <input type="hidden" name="rede_id" value="<?= e((string) $currentRedeId) ?>">
            <?php endif; ?>
            <?php if ($currentUnidadeId !== null): ?>
                <input type="hidden" name="unidade_id" value="<?= e((string) $currentUnidadeId) ?>">
            <?php endif; ?>
        <?php endif; ?>

        <!-- Busca de Pessoa (CPF/CNPJ) - Obrigatório no create -->
        <?php if (!$isEdit): ?>
        <div class="row">
            <div class="col-sm-12">
                <div class="form__field">
                    <label for="pessoa_search">Vincular Pessoa <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" class="input" id="pessoa_search" name="pessoa_search" placeholder="Digite nome, CPF ou CNPJ para buscar pessoa cadastrada..." autocomplete="off" required>
                        <input type="hidden" id="pessoa_id" name="pessoa_id" value="<?= e($val('pessoa_id')) ?>">
                        <span class="input-group__text" id="pessoa_search_status"></span>
                    </div>
                    <div id="pessoa_search_results" class="form__suggestions" style="display: none;"></div>
                    <?php if ($err('pessoa_id')): ?><p class="form__error"><?= e($err('pessoa_id')) ?></p><?php endif; ?>
                    <p class="text-muted">Obrigatório vincular uma pessoa cadastrada. Busque por nome, CPF ou CNPJ.</p>
                </div>
            </div>
        </div>
        <?php else: ?>
            <input type="hidden" name="pessoa_id" value="<?= e($item['pessoa_id'] ?? '') ?>">
        <?php endif; ?>

        <!-- Linha 2: Nome (col-md-6), E-mail (col-md-4), Status -->
        <div class="row">
            <div class="col-sm-12 col-md-6">
                <div class="form__field">
                    <label for="nome">Nome</label>
                    <input class="input <?= $err('nome') ? 'is-invalid' : '' ?>" id="nome" name="nome" value="<?= e($val('nome')) ?>" required>
                    <?php if ($err('nome')): ?><p class="form__error"><?= e($err('nome')) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="email">E-mail</label>
                    <input class="input <?= $err('email') ? 'is-invalid' : '' ?>" type="email" id="email" name="email" value="<?= e($val('email')) ?>" required>
                    <?php if ($err('email')): ?><p class="form__error"><?= e($err('email')) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-2">
                <div class="form__field">
                    <label for="status">Status</label>
                    <select class="input" id="status" name="status">
                        <?php foreach (['ATIVO', 'INATIVO'] as $status): ?>
                            <option value="<?= e($status) ?>" <?= $val('status', 'ATIVO') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Senha (temporário - será substituído por envio de senha temporária por e-mail) -->
        <div class="row">
            <div class="col-sm-12 col-md-6">
                <div class="form__field">
                    <label for="password"><?= $isEdit ? 'Nova senha (opcional)' : 'Senha temporária' ?></label>
                    <input class="input <?= $err('password') ? 'is-invalid' : '' ?>" type="password" id="password" name="password" <?= $isEdit ? '' : 'required' ?> minlength="8" autocomplete="new-password">
                    <?php if ($err('password')): ?><p class="form__error"><?= e($err('password')) ?></p><?php endif; ?>
                    <?php if (!$isEdit): ?>
                        <p class="text-muted">Senha temporária. Futuramente será enviada por e-mail com validação no primeiro login.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Salvar</button>
            <a class="btn btn-ghost" href="/admin/usuarios">Cancelar</a>
        </div>
    </form>
</section>

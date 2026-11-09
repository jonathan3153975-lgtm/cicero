<?php
$old = $old ?? [];
$item = $item ?? [];
$regioes = $regioes ?? [];
$currentRedeId = $currentRedeId ?? null;
$currentRegiaoId = $currentRegiaoId ?? null;
$currentUnidadeId = $currentUnidadeId ?? null;
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $item[$key] ?? $default);
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
$action = $item !== [] ? '/admin/dispositivos/' . $item['id'] : '/admin/dispositivos';
$selectedRegiao = $val('regiao_id', $currentRegiaoId !== null ? (string) $currentRegiaoId : '');
$selectedUnidade = $val('unidade_id', $currentUnidadeId !== null ? (string) $currentUnidadeId : '');
?>
<section class="page-heading">
    <div>
        <h2><?= e($title) ?></h2>
        <p class="text-muted">Dados do dispositivo de acesso.</p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/dispositivos">Voltar</a>
</section>

<section class="card">
    <form method="post" action="<?= e($action) ?>" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">

        <!-- Linha 1: Região e Unidade (cascata como no módulo Unidades) -->
        <div class="row">
            <div class="col-sm-12 col-md-6">
                <div class="form__field">
                    <label for="regiao_id">Região</label>
                    <select class="input <?= $err('regiao_id') ? 'is-invalid' : '' ?>" id="regiao_id" name="regiao_id" data-regiao-select>
                        <option value="">Selecione a região (opcional)</option>
                        <?php foreach ($regioes as $regiao): ?>
                            <option value="<?= e((string) $regiao['id']) ?>" data-rede-id="<?= e((string) $regiao['rede_id']) ?>" <?= $selectedRegiao === (string) $regiao['id'] ? 'selected' : '' ?>>
                                <?= e($regiao['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($err('regiao_id')): ?><p class="form__error"><?= e($err('regiao_id')) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-6">
                <div class="form__field">
                    <label for="unidade_id">Unidade</label>
                    <select class="input <?= $err('unidade_id') ? 'is-invalid' : '' ?>" id="unidade_id" name="unidade_id" required>
                        <option value="">Selecione a unidade</option>
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

        <!-- Linha 2: Nome (col-md-8), Tipo (col-md-4) -->
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
                    <label for="tipo">Tipo</label>
                    <input class="input" id="tipo" name="tipo" value="<?= e($val('tipo', 'CATRACA')) ?>">
                </div>
            </div>
        </div>

        <!-- Linha 3: Direção, Identificador, Status (mesmo espaço = col-md-4 cada) -->
        <div class="row">
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="direcao">Direção</label>
                    <select class="input" id="direcao" name="direcao">
                        <?php foreach (['AMBOS', 'ENTRADA', 'SAIDA'] as $direcao): ?>
                            <option value="<?= e($direcao) ?>" <?= $val('direcao', 'AMBOS') === $direcao ? 'selected' : '' ?>><?= e($direcao) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="identificador">Identificador</label>
                    <input class="input <?= $err('identificador') ? 'is-invalid' : '' ?>" id="identificador" name="identificador" value="<?= e($val('identificador')) ?>" required>
                    <?php if ($err('identificador')): ?><p class="form__error"><?= e($err('identificador')) ?></p><?php endif; ?>
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
        </div>

        <!-- Espaço reservado para futuros campos -->
        <div class="row" id="dispositivo-campos-extras" style="display: none;">
            <!-- Futuros campos serão adicionados aqui -->
        </div>

        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Salvar</button>
            <a class="btn btn-ghost" href="/admin/dispositivos">Cancelar</a>
        </div>
    </form>
</section>

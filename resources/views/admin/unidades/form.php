<?php
$old = $old ?? [];
$item = $item ?? [];
$redes = $redes ?? [];
$regioes = $regioes ?? [];
$isPlatform = $isPlatform ?? false;
$currentRedeId = $currentRedeId ?? null;
$regiaoSelecionada = $regiaoSelecionada ?? null;
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $item[$key] ?? $default);
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
$action = $item !== [] ? '/admin/unidades/' . $item['id'] : '/admin/unidades';
$selectedRede = $val('rede_id', $currentRedeId !== null ? (string) $currentRedeId : '');
$selectedRegiao = $val('regiao_id', $regiaoSelecionada !== null ? (string) $regiaoSelecionada : '');
?>
<section class="page-heading">
    <div>
        <h2><?= e($title) ?></h2>
        <p class="text-muted">Identificação, endereço, contato e operação da unidade.</p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/unidades">Voltar</a>
</section>

<section class="card">
    <form method="post" action="<?= e($action) ?>" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">

        <div class="row">
            <div class="col-sm-12 col-md-6">
                <div class="form__field">
                    <label for="rede_id">Rede</label>
                    <select class="input <?= $err('rede_id') ? 'is-invalid' : '' ?>" id="rede_id" name="rede_id" data-rede-select>
                        <option value="">Selecione</option>
                        <?php foreach ($redes as $rede): ?>
                            <option value="<?= e((string) $rede['id']) ?>" <?= $selectedRede === (string) $rede['id'] ? 'selected' : '' ?>>
                                <?= e($rede['nome_fantasia']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($err('rede_id')): ?><p class="form__error"><?= e($err('rede_id')) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-6">
                <div class="form__field">
                    <label for="regiao_id">Região</label>
                    <select class="input <?= $err('regiao_id') ? 'is-invalid' : '' ?>" id="regiao_id" name="regiao_id" data-regiao-select>
                        <option value="">Sem região (vinculada apenas à rede)</option>
                        <?php foreach ($regioes as $regiao): ?>
                            <option value="<?= e((string) $regiao['id']) ?>" data-rede-id="<?= e((string) $regiao['rede_id']) ?>" <?= $selectedRegiao === (string) $regiao['id'] ? 'selected' : '' ?>>
                                <?= e($regiao['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($err('regiao_id')): ?><p class="form__error"><?= e($err('regiao_id')) ?></p><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12 col-md-6">
                <div class="form__field">
                    <label for="nome">Nome</label>
                    <input class="input <?= $err('nome') ? 'is-invalid' : '' ?>" id="nome" name="nome" value="<?= e($val('nome')) ?>" required>
                    <?php if ($err('nome')): ?><p class="form__error"><?= e($err('nome')) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-3">
                <div class="form__field">
                    <label for="codigo_interno">Código interno</label>
                    <input class="input <?= $err('codigo_interno') ? 'is-invalid' : '' ?>" id="codigo_interno" name="codigo_interno" value="<?= e($val('codigo_interno')) ?>" placeholder="Gerado automaticamente" readonly>
                    <p class="text-muted">Gerado automaticamente.</p>
                    <?php if ($err('codigo_interno')): ?><p class="form__error"><?= e($err('codigo_interno')) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-3">
                <div class="form__field">
                    <label>Ativo</label>
                    <label class="checkbox">
                        <input type="hidden" name="ativo" value="0">
                        <input type="checkbox" name="ativo" value="1" <?= $val('situacao', 'ATIVA') === 'ATIVA' ? 'checked' : '' ?>>
                        Unidade ativa
                    </label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12 col-md-3">
                <div class="form__field">
                    <label for="cep">CEP</label>
                    <input class="input <?= $err('cep') ? 'is-invalid' : '' ?>" id="cep" name="cep" value="<?= e($val('cep')) ?>" data-mask="cep" data-cep inputmode="numeric" maxlength="9">
                    <p class="text-muted" data-cep-feedback></p>
                    <?php if ($err('cep')): ?><p class="form__error"><?= e($err('cep')) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-6">
                <div class="form__field">
                    <label for="logradouro">Logradouro</label>
                    <input class="input" id="logradouro" name="logradouro" value="<?= e($val('logradouro')) ?>">
                </div>
            </div>
            <div class="col-sm-12 col-md-3">
                <div class="form__field">
                    <label for="numero">Número</label>
                    <input class="input" id="numero" name="numero" value="<?= e($val('numero')) ?>">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12 col-md-2">
                <div class="form__field">
                    <label for="complemento">Complemento</label>
                    <input class="input" id="complemento" name="complemento" value="<?= e($val('complemento')) ?>">
                </div>
            </div>
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="bairro">Bairro</label>
                    <input class="input" id="bairro" name="bairro" value="<?= e($val('bairro')) ?>">
                </div>
            </div>
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="cidade">Cidade</label>
                    <input class="input" id="cidade" name="cidade" value="<?= e($val('cidade')) ?>">
                </div>
            </div>
            <div class="col-sm-12 col-md-2">
                <div class="form__field">
                    <label for="estado">Estado</label>
                    <input class="input <?= $err('estado') ? 'is-invalid' : '' ?>" id="estado" name="estado" maxlength="2" value="<?= e($val('estado')) ?>">
                    <?php if ($err('estado')): ?><p class="form__error"><?= e($err('estado')) ?></p><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="telefone">Telefone</label>
                    <input class="input" id="telefone" name="telefone" value="<?= e($val('telefone')) ?>" data-mask="phone" inputmode="tel" maxlength="15">
                </div>
            </div>
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="whatsapp">WhatsApp</label>
                    <input class="input" id="whatsapp" name="whatsapp" value="<?= e($val('whatsapp')) ?>" data-mask="phone" inputmode="tel" maxlength="15">
                </div>
            </div>
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="email">E-mail</label>
                    <input class="input <?= $err('email') ? 'is-invalid' : '' ?>" type="email" id="email" name="email" value="<?= e($val('email')) ?>">
                    <?php if ($err('email')): ?><p class="form__error"><?= e($err('email')) ?></p><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12 col-md-3">
                <div class="form__field">
                    <label for="horario_funcionamento">Horário de funcionamento</label>
                    <input class="input" id="horario_funcionamento" name="horario_funcionamento" value="<?= e($val('horario_funcionamento')) ?>" placeholder="06:00-22:00">
                </div>
            </div>
            <div class="col-sm-12 col-md-3">
                <div class="form__field">
                    <label for="capacidade_maxima">Capacidade</label>
                    <input class="input <?= $err('capacidade_maxima') ? 'is-invalid' : '' ?>" type="number" min="0" id="capacidade_maxima" name="capacidade_maxima" value="<?= e($val('capacidade_maxima')) ?>">
                    <?php if ($err('capacidade_maxima')): ?><p class="form__error"><?= e($err('capacidade_maxima')) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-3">
                <div class="form__field">
                    <label for="metragem">Metragem (m²)</label>
                    <input class="input <?= $err('metragem') ? 'is-invalid' : '' ?>" type="number" min="0" step="0.01" id="metragem" name="metragem" value="<?= e($val('metragem')) ?>">
                    <?php if ($err('metragem')): ?><p class="form__error"><?= e($err('metragem')) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-3">
                <div class="form__field">
                    <label for="data_abertura">Data de abertura</label>
                    <input class="input" type="date" id="data_abertura" name="data_abertura" value="<?= e($val('data_abertura')) ?>">
                </div>
            </div>
        </div>

        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Salvar</button>
            <a class="btn btn-ghost" href="/admin/unidades">Cancelar</a>
        </div>
    </form>
</section>

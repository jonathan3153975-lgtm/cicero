<?php
$old = $old ?? [];
$item = $item ?? [];
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $item[$key] ?? $default);
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
$action = $item !== [] ? '/admin/unidades/' . $item['id'] : '/admin/unidades';
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

        <div class="form-grid">
            <?php if ($isPlatform): ?>
                <div class="form__field">
                    <label for="rede_id">Rede</label>
                    <select class="input <?= $err('rede_id') ? 'is-invalid' : '' ?>" id="rede_id" name="rede_id">
                        <option value="">Selecione</option>
                        <?php foreach ($redes as $rede): ?>
                            <option value="<?= e((string) $rede['id']) ?>" <?= $val('rede_id') === (string) $rede['id'] ? 'selected' : '' ?>><?= e($rede['nome_fantasia']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($err('rede_id')): ?><p class="form__error"><?= e($err('rede_id')) ?></p><?php endif; ?>
                </div>
            <?php else: ?>
                <input type="hidden" name="rede_id" value="<?= e((string) $currentRedeId) ?>">
                <div class="form__field">
                    <label>Rede</label>
                    <input class="input" value="Rede do contexto atual" disabled>
                </div>
            <?php endif; ?>

            <div class="form__field">
                <label for="nome">Nome</label>
                <input class="input <?= $err('nome') ? 'is-invalid' : '' ?>" id="nome" name="nome" value="<?= e($val('nome')) ?>" required>
                <?php if ($err('nome')): ?><p class="form__error"><?= e($err('nome')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="codigo_interno">Código interno</label>
                <input class="input <?= $err('codigo_interno') ? 'is-invalid' : '' ?>" id="codigo_interno" name="codigo_interno" value="<?= e($val('codigo_interno')) ?>" required>
                <?php if ($err('codigo_interno')): ?><p class="form__error"><?= e($err('codigo_interno')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="situacao">Situação</label>
                <select class="input" id="situacao" name="situacao">
                    <?php foreach (['ATIVA', 'INATIVA', 'EM_IMPLANTACAO'] as $situacao): ?>
                        <option value="<?= e($situacao) ?>" <?= $val('situacao', 'ATIVA') === $situacao ? 'selected' : '' ?>><?= e($situacao) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form__field">
                <label for="cep">CEP</label>
                <input class="input" id="cep" name="cep" value="<?= e($val('cep')) ?>">
            </div>
            <div class="form__field">
                <label for="logradouro">Logradouro</label>
                <input class="input" id="logradouro" name="logradouro" value="<?= e($val('logradouro')) ?>">
            </div>
            <div class="form__field">
                <label for="numero">Número</label>
                <input class="input" id="numero" name="numero" value="<?= e($val('numero')) ?>">
            </div>
            <div class="form__field">
                <label for="complemento">Complemento</label>
                <input class="input" id="complemento" name="complemento" value="<?= e($val('complemento')) ?>">
            </div>
            <div class="form__field">
                <label for="bairro">Bairro</label>
                <input class="input" id="bairro" name="bairro" value="<?= e($val('bairro')) ?>">
            </div>
            <div class="form__field">
                <label for="cidade">Cidade</label>
                <input class="input" id="cidade" name="cidade" value="<?= e($val('cidade')) ?>">
            </div>
            <div class="form__field">
                <label for="estado">Estado</label>
                <input class="input <?= $err('estado') ? 'is-invalid' : '' ?>" id="estado" name="estado" maxlength="2" value="<?= e($val('estado')) ?>">
                <?php if ($err('estado')): ?><p class="form__error"><?= e($err('estado')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="telefone">Telefone</label>
                <input class="input" id="telefone" name="telefone" value="<?= e($val('telefone')) ?>">
            </div>
            <div class="form__field">
                <label for="whatsapp">WhatsApp</label>
                <input class="input" id="whatsapp" name="whatsapp" value="<?= e($val('whatsapp')) ?>">
            </div>
            <div class="form__field">
                <label for="email">E-mail</label>
                <input class="input <?= $err('email') ? 'is-invalid' : '' ?>" type="email" id="email" name="email" value="<?= e($val('email')) ?>">
                <?php if ($err('email')): ?><p class="form__error"><?= e($err('email')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="data_abertura">Data de abertura</label>
                <input class="input" type="date" id="data_abertura" name="data_abertura" value="<?= e($val('data_abertura')) ?>">
            </div>
            <div class="form__field">
                <label for="horario_funcionamento">Horário de funcionamento</label>
                <input class="input" id="horario_funcionamento" name="horario_funcionamento" value="<?= e($val('horario_funcionamento')) ?>">
            </div>
            <div class="form__field">
                <label for="capacidade_maxima">Capacidade máxima</label>
                <input class="input <?= $err('capacidade_maxima') ? 'is-invalid' : '' ?>" type="number" min="0" id="capacidade_maxima" name="capacidade_maxima" value="<?= e($val('capacidade_maxima')) ?>">
                <?php if ($err('capacidade_maxima')): ?><p class="form__error"><?= e($err('capacidade_maxima')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="metragem">Metragem (m²)</label>
                <input class="input <?= $err('metragem') ? 'is-invalid' : '' ?>" type="number" min="0" step="0.01" id="metragem" name="metragem" value="<?= e($val('metragem')) ?>">
                <?php if ($err('metragem')): ?><p class="form__error"><?= e($err('metragem')) ?></p><?php endif; ?>
            </div>
        </div>

        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Salvar</button>
            <a class="btn btn-ghost" href="/admin/unidades">Cancelar</a>
        </div>
    </form>
</section>

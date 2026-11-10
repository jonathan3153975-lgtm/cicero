<?php
$old = $old ?? [];
$item = $item ?? [];
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $item[$key] ?? $default);
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
$action = $item !== [] ? '/admin/pessoas/' . $item['id'] : '/admin/pessoas';
$isEdit = $item !== [];
$currentTipo = $val('tipo', 'PF');
$regioes = $regioes ?? [];
$unidades = $unidades ?? [];
$currentRedeId = $currentRedeId ?? null;
$currentRegiaoId = $currentRegiaoId ?? null;
$currentUnidadeId = $currentUnidadeId ?? null;
$selectedRede = $val('rede_id', $currentRedeId !== null ? (string) $currentRedeId : '');
$selectedRegiao = $val('regiao_id', $currentRegiaoId !== null ? (string) $currentRegiaoId : '');
$selectedUnidade = $val('unidade_id', $currentUnidadeId !== null ? (string) $currentUnidadeId : '');
?>
<section class="page-heading">
    <div>
        <h2><?= e($title) ?></h2>
        <p class="text-muted">Dados cadastrais, contato e endereço principal.</p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/pessoas">Voltar</a>
</section>

<section class="card">
    <form method="post" action="<?= e($action) ?>" class="form" id="pessoa-form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">

        <!-- Linha 1: Rede, Região, Unidade (cascata) + Tipo -->
        <div class="row">
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="rede_id">Rede</label>
                    <select class="input <?= $err('rede_id') ? 'is-invalid' : '' ?>" id="rede_id" name="rede_id" data-rede-select>
                        <option value="">Selecione</option>
                        <option value="global">Global (sem rede)</option>
                        <?php foreach ($redes as $rede): ?>
                            <option value="<?= e((string) $rede['id']) ?>" <?= $selectedRede === (string) $rede['id'] ? 'selected' : '' ?>><?= e($rede['nome_fantasia']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($err('rede_id')): ?><p class="form__error"><?= e($err('rede_id')) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="regiao_id">Região</label>
                    <select class="input <?= $err('regiao_id') ? 'is-invalid' : '' ?>" id="regiao_id" name="regiao_id" data-regiao-select>
                        <option value="">Selecione a região</option>
                        <?php foreach ($regioes as $regiao): ?>
                            <option value="<?= e((string) $regiao['id']) ?>" data-rede-id="<?= e((string) $regiao['rede_id']) ?>" <?= $selectedRegiao === (string) $regiao['id'] ? 'selected' : '' ?>><?= e($regiao['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($err('regiao_id')): ?><p class="form__error"><?= e($err('regiao_id')) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="unidade_id">Unidade</label>
                    <select class="input <?= $err('unidade_id') ? 'is-invalid' : '' ?>" id="unidade_id" name="unidade_id" required>
                        <option value="">Selecione a unidade</option>
                        <?php foreach ($unidades as $unidade): ?>
                            <option value="<?= e((string) $unidade['id']) ?>" 
                                    data-regiao-id="<?= e((string) ($unidade['regiao_id'] ?? '')) ?>" 
                                    data-rede-id="<?= e((string) $unidade['rede_id']) ?>" 
                                    <?= $selectedUnidade === (string) $unidade['id'] ? 'selected' : '' ?>>
                                <?= e($unidade['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($err('unidade_id')): ?><p class="form__error"><?= e($err('unidade_id')) ?></p><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12 col-md-2">
                <div class="form__field">
                    <label for="tipo">Tipo <span class="text-danger">*</span></label>
                    <select class="input <?= $err('tipo') ? 'is-invalid' : '' ?>" id="tipo" name="tipo" required data-pessoa-tipo>
                        <?php foreach (['PF', 'PJ'] as $tipo): ?>
                            <option value="<?= e($tipo) ?>" <?= $val('tipo', 'PF') === $tipo ? 'selected' : '' ?>><?= e($tipo) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($err('tipo')): ?><p class="form__error"><?= e($err('tipo')) ?></p><?php endif; ?>
                </div>
            </div>
        </div>

        <!-- PJ FIELDS -->
        <!-- Linha: Razão Social + Nome Fantasia -->
        <div class="row pessoa-field-pj" style="<?= $currentTipo !== 'PJ' ? 'display:none;' : '' ?>">
            <div class="col-sm-12 col-md-5">
                <div class="form__field">
                    <label for="nome">Razão Social <span class="text-danger">*</span></label>
                    <input class="input <?= $err('nome') ? 'is-invalid' : '' ?>" id="nome" name="nome" value="<?= e($val('nome')) ?>" required>
                    <?php if ($err('nome')): ?><p class="form__error"><?= e($err('nome')) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-5">
                <div class="form__field">
                    <label for="nome_fantasia">Nome Fantasia</label>
                    <input class="input" id="nome_fantasia" name="nome_fantasia" value="<?= e($val('nome_fantasia')) ?>">
                </div>
            </div>
        </div>

        <!-- Linha: CNPJ, E-mail, Telefone -->
        <div class="row pessoa-field-pj" style="<?= $currentTipo !== 'PJ' ? 'display:none;' : '' ?>">
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="cnpj">CNPJ <span class="text-danger">*</span></label>
                    <input class="input <?= $err('cnpj') ? 'is-invalid' : '' ?>" id="cnpj" name="cnpj" value="<?= e($val('cnpj')) ?>" data-mask="cnpj" inputmode="numeric" maxlength="18" required>
                    <?php if ($err('cnpj')): ?><p class="form__error"><?= e($err('cnpj')) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="email_principal">E-mail</label>
                    <input class="input <?= $err('email_principal') ? 'is-invalid' : '' ?>" type="email" id="email_principal" name="email_principal" value="<?= e($val('email_principal')) ?>">
                    <?php if ($err('email_principal')): ?><p class="form__error"><?= e($err('email_principal')) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="telefone_principal">Telefone</label>
                    <input class="input" id="telefone_principal" name="telefone_principal" value="<?= e($val('telefone_principal')) ?>" data-mask="phone" inputmode="tel" maxlength="15">
                </div>
            </div>
        </div>

        <!-- PF FIELDS -->
        <!-- Linha: Nome + Nome Social -->
        <div class="row pessoa-field-pf" style="<?= $currentTipo !== 'PF' ? 'display:none;' : '' ?>">
            <div class="col-sm-12 col-md-5">
                <div class="form__field">
                    <label for="nome">Nome <span class="text-danger">*</span></label>
                    <input class="input <?= $err('nome') ? 'is-invalid' : '' ?>" id="nome" name="nome" value="<?= e($val('nome')) ?>" required>
                    <?php if ($err('nome')): ?><p class="form__error"><?= e($err('nome')) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-5">
                <div class="form__field">
                    <label for="nome_social">Nome Social</label>
                    <input class="input" id="nome_social" name="nome_social" value="<?= e($val('nome_social')) ?>">
                </div>
            </div>
        </div>

        <!-- Linha: Sexo, CPF, Data de nascimento -->
        <div class="row pessoa-field-pf" style="<?= $currentTipo !== 'PF' ? 'display:none;' : '' ?>">
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="sexo">Sexo</label>
                    <select class="input" id="sexo" name="sexo">
                        <option value="">Selecione</option>
                        <?php foreach (['M' => 'Masculino', 'F' => 'Feminino', 'O' => 'Outro', 'N' => 'Prefiro não informar'] as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= $val('sexo') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="cpf">CPF <span class="text-danger">*</span></label>
                    <input class="input <?= $err('cpf') ? 'is-invalid' : '' ?>" id="cpf" name="cpf" value="<?= e($val('cpf')) ?>" data-mask="cpf" inputmode="numeric" maxlength="14" required>
                    <?php if ($err('cpf')): ?><p class="form__error"><?= e($err('cpf')) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="data_nascimento">Data de Nascimento</label>
                    <input class="input <?= $err('data_nascimento') ? 'is-invalid' : '' ?>" type="date" id="data_nascimento" name="data_nascimento" value="<?= e($val('data_nascimento')) ?>">
                    <?php if ($err('data_nascimento')): ?><p class="form__error"><?= e($err('data_nascimento')) ?></p><?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Linha: Estado civil, E-mail, Telefone -->
        <div class="row pessoa-field-pf" style="<?= $currentTipo !== 'PF' ? 'display:none;' : '' ?>">
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="estado_civil">Estado Civil</label>
                    <select class="input" id="estado_civil" name="estado_civil">
                        <option value="">Selecione</option>
                        <?php foreach (['SOLTEIRO' => 'Solteiro(a)', 'CASADO' => 'Casado(a)', 'DIVORCIADO' => 'Divorciado(a)', 'VIUVO' => 'Viúvo(a)', 'UNIAO_ESTAVEL' => 'União Estável', 'OUTRO' => 'Outro'] as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= $val('estado_civil') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="email_principal">E-mail</label>
                    <input class="input <?= $err('email_principal') ? 'is-invalid' : '' ?>" type="email" id="email_principal" name="email_principal" value="<?= e($val('email_principal')) ?>">
                    <?php if ($err('email_principal')): ?><p class="form__error"><?= e($err('email_principal')) ?></p><?php endif; ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="telefone_principal">Telefone</label>
                    <input class="input" id="telefone_principal" name="telefone_principal" value="<?= e($val('telefone_principal')) ?>" data-mask="phone" inputmode="tel" maxlength="15">
                </div>
            </div>
        </div>

        <!-- Checkbox Ativo (sem Status select) -->
        <div class="row">
            <div class="col-sm-12 col-md-2">
                <div class="form__field form__field--checkbox">
                    <label class="checkbox checkbox--inline">
                        <input type="hidden" name="ativo" value="0">
                        <input type="checkbox" name="ativo" value="1" id="ativo" <?= $val('status', 'ATIVO') === 'ATIVO' ? 'checked' : '' ?>>
                        <span class="checkbox__label">Ativo</span>
                    </label>
                </div>
            </div>
        </div>

        <hr class="form__separator">

        <!-- Endereço - mesmo padrão de Unidades -->
        <h4 class="form__section-title">Endereço Principal</h4>

        <!-- Linha: CEP (col-md-3) + Logradouro (col-md-6) + Número (col-md-3) -->
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

        <!-- Linha: Complemento (col-md-2) + Bairro (col-md-4) + Cidade (col-md-4) + Estado (col-md-2) -->
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

        <!-- Observações - largura total -->
        <div class="form__field form__field--full">
            <label for="observacoes">Observações</label>
            <textarea class="input" id="observacoes" name="observacoes" rows="3"><?= e($val('observacoes')) ?></textarea>
        </div>

        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Salvar</button>
            <a class="btn btn-ghost" href="/admin/pessoas">Cancelar</a>
        </div>
    </form>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoSelect = document.getElementById('tipo');
    const pfFields = document.querySelectorAll('.pessoa-field-pf');
    const pjFields = document.querySelectorAll('.pessoa-field-pj');
    const cpfInput = document.getElementById('cpf');
    const cnpjInput = document.getElementById('cnpj');
    const nomeInput = document.getElementById('nome');
    const nomeSocialInput = document.getElementById('nome_social');
    const nomeFantasiaInput = document.getElementById('nome_fantasia');
    const ativoCheckbox = document.getElementById('ativo');

    function toggleFields(tipo) {
        const isPF = tipo === 'PF';
        const isPJ = tipo === 'PJ';

        // Toggle PF fields
        pfFields.forEach(field => {
            field.style.display = isPF ? '' : 'none';
            const inputs = field.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (!isPF) {
                    input.removeAttribute('required');
                } else if (input.dataset.required === 'true') {
                    input.required = true;
                }
            });
        });

        // Toggle PJ fields
        pjFields.forEach(field => {
            field.style.display = isPJ ? '' : 'none';
            const inputs = field.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (!isPJ) {
                    input.removeAttribute('required');
                } else if (input.dataset.required === 'true') {
                    input.required = true;
                }
            });
        });

        // Handle CPF/CNPJ required
        if (cpfInput) {
            cpfInput.required = isPF;
            if (!isPF) cpfInput.value = '';
        }
        if (cnpjInput) {
            cnpjInput.required = isPJ;
            if (!isPJ) cnpjInput.value = '';
        }

        // Clear name fields when switching
        if (nomeSocialInput) nomeSocialInput.value = '';
        if (nomeFantasiaInput) nomeFantasiaInput.value = '';
    }

    // Initial toggle
    if (tipoSelect) {
        toggleFields(tipoSelect.value);
    }

    // Listen for tipo change
    tipoSelect?.addEventListener('change', function() {
        toggleFields(this.value);
    });
});
</script>
<?php
$old = $old ?? [];
$aluno = $aluno ?? [];
$isEdit = $aluno !== [];
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $aluno[$key] ?? $default);
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
$action = $isEdit ? '/admin/alunos/' . $aluno['id'] : '/admin/alunos';
$canAddResponsavel = isset($can) && $can('responsavel.criar');
$canEditResponsavel = isset($can) && $can('responsavel.editar');
?>
<section class="page-heading">
    <div>
        <h2><?= e($title) ?></h2>
        <p class="text-muted"><?= $isEdit ? 'Dados do aluno, pessoa vinculada e responsáveis.' : 'Selecione uma pessoa existente ou cadastre uma nova pessoa.' ?></p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/alunos">Voltar</a>
</section>

<section class="card">
    <form method="post" action="<?= e($action) ?>" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">

        <?php if (!$isEdit): ?>
            <div class="form__field">
                <label for="pessoa_id">Pessoa existente (opcional)</label>
                <select class="input" id="pessoa_id" name="pessoa_id">
                    <option value="">Cadastrar nova pessoa</option>
                    <?php foreach ($pessoas as $pessoa): ?>
                        <option value="<?= e((string) $pessoa['id']) ?>" <?= $val('pessoa_id') === (string) $pessoa['id'] ? 'selected' : '' ?>>
                            <?= e($pessoa['nome']) ?> · <?= e($pessoa['cpf'] ?: ($pessoa['email_principal'] ?? 'sem documento')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-muted">Se selecionar uma pessoa, os campos de nova pessoa abaixo são ignorados.</p>
            </div>
        <?php endif; ?>

        <h3>Dados da pessoa</h3>
        <div class="form-grid">
            <div class="form__field">
                <label for="nome">Nome</label>
                <input class="input <?= $err('nome') ? 'is-invalid' : '' ?>" id="nome" name="nome" value="<?= e($val('nome')) ?>" <?= $isEdit ? 'required' : '' ?>>
                <?php if ($err('nome')): ?><p class="form__error"><?= e($err('nome')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="cpf">CPF</label>
                <input class="input <?= $err('cpf') ? 'is-invalid' : '' ?>" id="cpf" name="cpf" value="<?= e($val('cpf')) ?>">
                <?php if ($err('cpf')): ?><p class="form__error"><?= e($err('cpf')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="data_nascimento">Data de nascimento</label>
                <input class="input <?= $err('data_nascimento') ? 'is-invalid' : '' ?>" type="date" id="data_nascimento" name="data_nascimento" value="<?= e($val('data_nascimento')) ?>">
                <?php if ($err('data_nascimento')): ?><p class="form__error"><?= e($err('data_nascimento')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="email_principal">E-mail</label>
                <input class="input <?= $err('email_principal') ? 'is-invalid' : '' ?>" type="email" id="email_principal" name="email_principal" value="<?= e($val('email_principal')) ?>">
                <?php if ($err('email_principal')): ?><p class="form__error"><?= e($err('email_principal')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="telefone_principal">Telefone</label>
                <input class="input" id="telefone_principal" name="telefone_principal" value="<?= e($val('telefone_principal')) ?>">
            </div>
        </div>

        <h3>Dados do aluno</h3>
        <div class="form-grid">
            <?php if ($currentUnidadeId === null): ?>
                <div class="form__field">
                    <label for="unidade_id">Unidade</label>
                    <select class="input <?= $err('unidade_id') ? 'is-invalid' : '' ?>" id="unidade_id" name="unidade_id" required>
                        <option value="">Selecione</option>
                        <?php foreach ($unidades as $unidade): ?>
                            <option value="<?= e((string) $unidade['id']) ?>" <?= $val('unidade_id') === (string) $unidade['id'] ? 'selected' : '' ?>>
                                <?= e($unidade['nome']) ?><?= isset($unidade['rede_nome']) ? ' · ' . e($unidade['rede_nome']) : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($err('unidade_id')): ?><p class="form__error"><?= e($err('unidade_id')) ?></p><?php endif; ?>
                </div>
            <?php else: ?>
                <input type="hidden" name="unidade_id" value="<?= e((string) $currentUnidadeId) ?>">
                <div class="form__field">
                    <label>Unidade</label>
                    <input class="input" value="Unidade do contexto atual" disabled>
                </div>
            <?php endif; ?>

            <div class="form__field">
                <label for="codigo">Código interno</label>
                <input class="input <?= $err('codigo') ? 'is-invalid' : '' ?>" id="codigo" name="codigo" value="<?= e($val('codigo')) ?>">
                <?php if ($err('codigo')): ?><p class="form__error"><?= e($err('codigo')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="data_entrada">Data de entrada</label>
                <input class="input <?= $err('data_entrada') ? 'is-invalid' : '' ?>" type="date" id="data_entrada" name="data_entrada" value="<?= e($val('data_entrada')) ?>">
                <?php if ($err('data_entrada')): ?><p class="form__error"><?= e($err('data_entrada')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="como_conheceu">Como conheceu</label>
                <input class="input" id="como_conheceu" name="como_conheceu" value="<?= e($val('como_conheceu')) ?>">
            </div>
            <div class="form__field">
                <label for="status">Status</label>
                <select class="input" id="status" name="status">
                    <?php foreach (['ATIVO', 'INATIVO', 'BLOQUEADO'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= $val('status', 'ATIVO') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form__field">
                <label for="observacoes">Observações</label>
                <textarea class="input" id="observacoes" name="observacoes" rows="3"><?= e($val('observacoes')) ?></textarea>
            </div>
        </div>

        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Salvar</button>
            <a class="btn btn-ghost" href="/admin/alunos">Cancelar</a>
        </div>
    </form>
</section>

<?php if ($isEdit): ?>
    <section class="card">
        <header class="card__header">
            <div>
                <h3>Responsáveis</h3>
                <p class="text-muted">Responsáveis legais, financeiros e autorizados a buscar o aluno.</p>
            </div>
        </header>

        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Responsável</th>
                        <th scope="col">Parentesco</th>
                        <th scope="col">Financeiro</th>
                        <th scope="col">Autorizado a buscar</th>
                        <th scope="col">Status</th>
                        <th scope="col">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($responsaveis ?? []) as $responsavel): ?>
                        <tr>
                            <td>
                                <strong><?= e($responsavel['nome']) ?></strong><br>
                                <small class="text-muted"><?= e($responsavel['cpf'] ?: ($responsavel['email_principal'] ?? '—')) ?></small>
                            </td>
                            <td><?= e($responsavel['parentesco'] ?? '—') ?></td>
                            <td><?= !empty($responsavel['responsavel_financeiro']) ? 'Sim' : 'Não' ?></td>
                            <td><?= !empty($responsavel['autorizado_buscar']) ? 'Sim' : 'Não' ?></td>
                            <td><span class="badge <?= $responsavel['status'] === 'ATIVO' ? 'badge--success' : 'badge--warning' ?>"><?= e($responsavel['status']) ?></span></td>
                            <td class="table__actions">
                                <?php if ($canEditResponsavel && $responsavel['status'] === 'ATIVO'): ?>
                                    <form method="post" action="/admin/alunos/<?= e((string) $aluno['id']) ?>/responsaveis/<?= e((string) $responsavel['id']) ?>/remover">
                                        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">
                                        <button class="btn btn-ghost btn-sm" type="submit">Remover</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (($responsaveis ?? []) === []): ?>
                        <tr><td colspan="6" class="text-muted">Nenhum responsável cadastrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($canAddResponsavel): ?>
            <hr class="divider">
            <h3>Adicionar responsável</h3>
            <form method="post" action="/admin/alunos/<?= e((string) $aluno['id']) ?>/responsaveis" class="form">
                <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">

                <div class="form-grid">
                    <div class="form__field">
                        <label for="responsavel_pessoa_id">Pessoa existente (opcional)</label>
                        <select class="input" id="responsavel_pessoa_id" name="responsavel_pessoa_id">
                            <option value="">Cadastrar nova pessoa</option>
                            <?php foreach ($pessoas as $pessoa): ?>
                                <option value="<?= e((string) $pessoa['id']) ?>"><?= e($pessoa['nome']) ?> · <?= e($pessoa['cpf'] ?: ($pessoa['email_principal'] ?? 'sem documento')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form__field">
                        <label for="responsavel_nome">Nome do novo responsável</label>
                        <input class="input" id="responsavel_nome" name="nome" value="">
                    </div>
                    <div class="form__field">
                        <label for="responsavel_cpf">CPF</label>
                        <input class="input" id="responsavel_cpf" name="cpf" value="">
                    </div>
                    <div class="form__field">
                        <label for="responsavel_email">E-mail</label>
                        <input class="input" id="responsavel_email" name="email_principal" value="">
                    </div>
                    <div class="form__field">
                        <label for="responsavel_telefone">Telefone</label>
                        <input class="input" id="responsavel_telefone" name="telefone_principal" value="">
                    </div>
                    <div class="form__field">
                        <label for="parentesco">Parentesco</label>
                        <input class="input" id="parentesco" name="parentesco" value="">
                    </div>
                    <div class="form__field">
                        <label class="checkbox"><input type="checkbox" name="responsavel_financeiro" value="1"> Responsável financeiro</label>
                        <label class="checkbox"><input type="checkbox" name="autorizado_buscar" value="1"> Autorizado a buscar</label>
                    </div>
                </div>

                <div class="form__actions">
                    <button class="btn btn-primary" type="submit">Adicionar responsável</button>
                </div>
            </form>
        <?php endif; ?>
    </section>
<?php endif; ?>

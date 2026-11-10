<?php $canEdit = isset($can) && $can('pessoa.editar'); ?>
<?php $search = $search ?? ''; ?>
<section class="page-heading">
    <div>
        <h2>Pessoas</h2>
        <p class="text-muted">Cadastro unificado de alunos, responsáveis, funcionários e fornecedores.</p>
    </div>
    <?php if (isset($can) && $can('pessoa.criar')): ?>
        <a class="btn btn-primary" href="/admin/pessoas/novo">Nova pessoa</a>
    <?php endif; ?>
</section>

<!-- Search Bar Card -->
<section class="card card--search">
    <div class="search-form" id="pessoas-search-form">
        <div class="search-form__row">
            <div class="search-form__input-wrapper">
                <label for="busca" class="visually-hidden">Buscar pessoas</label>
                <input 
                    class="input search-form__input" 
                    id="busca" 
                    name="busca" 
                    value="<?= e($search) ?>" 
                    placeholder="Nome, CPF/CNPJ ou e-mail..."
                    autocomplete="off"
                >
            </div>
            <div class="search-form__actions">
                <button class="btn btn-primary search-form__btn" type="button" id="btn-buscar">
                    <svg viewBox="0 0 24 24" aria-hidden="true" width="18" height="18"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                    <span class="btn__text">Buscar</span>
                </button>
                <button class="btn btn-ghost search-form__btn" type="button" id="btn-limpar">
                    <svg viewBox="0 0 24 24" aria-hidden="true" width="18" height="18"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                    <span class="btn__text">Limpar</span>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Results Card -->
<section class="card card--results" id="pessoas-results-card">
    <?php if ($pessoas === []): ?>
        <div class="pessoas-empty">
            <svg viewBox="0 0 24 24" aria-hidden="true" class="pessoas-empty__icon"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
            <h3><?= $search !== '' ? 'Nenhuma pessoa encontrada' : 'Nenhuma pessoa cadastrada' ?></h3>
            <p><?= $search !== '' ? 'Tente alterar os termos da busca.' : 'Cadastre a primeira pessoa clicando em "Nova pessoa".' ?></p>
        </div>
    <?php else: ?>
        <!-- Table View (Desktop) -->
        <div class="pessoas-table-wrapper" style="max-height: 70vh; overflow:auto;">
            <table class="pessoas-table" aria-label="Lista de pessoas">
                <thead>
                    <tr>
                        <th scope="col">Nome</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Documento</th>
                        <th scope="col">E-mail</th>
                        <th scope="col">Telefone</th>
                        <th scope="col">Status</th>
                        <th scope="col"><span class="visually-hidden">Ações</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pessoas as $pessoa): ?>
                        <tr>
                            <td class="pessoas-table__nome">
                                <div class="pessoas-table__nome-main">
                                    <span class="pessoas-table__avatar"><?= e(mb_substr($pessoa['nome'], 0, 1)) ?></span>
                                    <span><?= e($pessoa['nome']) ?></span>
                                </div>
                                <?php if (!empty($pessoa['nome_social']) || !empty($pessoa['nome_fantasia'])): ?>
                                    <span class="pessoas-table__nome-alt">
                                        <?= e($pessoa['nome_social'] ?: $pessoa['nome_fantasia']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $pessoa['tipo'] === 'PF' ? 'badge--info' : 'badge--secondary' ?>">
                                    <?= e($pessoa['tipo']) ?>
                                </span>
                            </td>
                            <td><?= e($pessoa['cpf'] ?: $pessoa['cnpj'] ?: '—') ?></td>
                            <td><?= e($pessoa['email_principal'] ?: '—') ?></td>
                            <td><?= e($pessoa['telefone_principal'] ?: '—') ?></td>
                            <td>
                                <span class="badge <?= $pessoa['status'] === 'ATIVO' ? 'badge--success' : ($pessoa['status'] === 'BLOQUEADO' ? 'badge--danger' : 'badge--warning') ?>">
                                    <?= e($pessoa['status']) ?>
                                </span>
                            </td>
                            <td class="pessoas-table__actions">
                                <?php if ($canEdit): ?>
                                    <a class="btn btn-ghost btn-sm btn-icon" href="/admin/pessoas/<?= e((string) $pessoa['id']) ?>/editar" aria-label="Editar <?= e($pessoa['nome']) ?>">
                                        <svg viewBox="0 0 24 24" aria-hidden="true" width="16" height="16"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Card View (Mobile) -->
        <div class="pessoas-grid" id="pessoas-grid">
            <?php foreach ($pessoas as $pessoa): ?>
                <article class="pessoa-card">
                    <header class="pessoa-card__header">
                        <div class="pessoa-card__avatar">
                            <span class="pessoa-card__avatar-letter"><?= e(mb_substr($pessoa['nome'], 0, 1)) ?></span>
                        </div>
                        <div class="pessoa-card__main">
                            <h4 class="pessoa-card__nome"><?= e($pessoa['nome']) ?></h4>
                            <span class="badge pessoa-card__tipo <?= $pessoa['tipo'] === 'PF' ? 'badge--info' : 'badge--secondary' ?>"><?= e($pessoa['tipo']) ?></span>
                        </div>
                        <div class="pessoa-card__status">
                            <span class="badge <?= $pessoa['status'] === 'ATIVO' ? 'badge--success' : ($pessoa['status'] === 'BLOQUEADO' ? 'badge--danger' : 'badge--warning') ?>">
                                <?= e($pessoa['status']) ?>
                            </span>
                        </div>
                    </header>
                    <div class="pessoa-card__details">
                        <?php if (!empty($pessoa['cpf']) || !empty($pessoa['cnpj'])): ?>
                            <dl class="pessoa-card__field">
                                <dt>Documento</dt>
                                <dd><?= e($pessoa['cpf'] ?: $pessoa['cnpj']) ?></dd>
                            </dl>
                        <?php endif; ?>
                        <?php if (!empty($pessoa['email_principal'])): ?>
                            <dl class="pessoa-card__field">
                                <dt>E-mail</dt>
                                <dd><?= e($pessoa['email_principal']) ?></dd>
                            </dl>
                        <?php endif; ?>
                        <?php if (!empty($pessoa['telefone_principal'])): ?>
                            <dl class="pessoa-card__field">
                                <dt>Telefone</dt>
                                <dd><?= e($pessoa['telefone_principal']) ?></dd>
                            </dl>
                        <?php endif; ?>
                    </div>
                    <footer class="pessoa-card__actions">
                        <?php if ($canEdit): ?>
                            <a class="btn btn-ghost btn-sm" href="/admin/pessoas/<?= e((string) $pessoa['id']) ?>/editar">
                                <svg viewBox="0 0 24 24" aria-hidden="true" width="16" height="16"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                <span>Editar</span>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </footer>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
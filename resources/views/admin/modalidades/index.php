<?php $canEdit = isset($can) && $can('modalidade.editar'); ?>
<section class="page-heading">
    <div>
        <h2>Modalidades</h2>
        <p class="text-muted">Atividades e modalidades incluídas nos planos.</p>
    </div>
    <?php if ($canEdit): ?>
        <a class="btn btn-primary" href="/admin/modalidades/nova">Nova modalidade</a>
    <?php endif; ?>
</section>

<section class="card">
    <?php if ($modalidades === []): ?>
        <div class="pessoas-empty">
            <svg viewBox="0 0 24 24" aria-hidden="true" class="pessoas-empty__icon"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
            <h3>Nenhuma modalidade cadastrada</h3>
            <p>Cadastre a primeira modalidade clicando em "Nova modalidade".</p>
        </div>
    <?php else: ?>
        <!-- Table View (Desktop) -->
        <div class="table-wrapper modalidades-table-wrapper">
            <table class="table modalidades-table" aria-label="Lista de modalidades">
                <thead>
                    <tr>
                        <th scope="col">Nome</th>
                        <th scope="col">Descrição</th>
                        <th scope="col">Abrangência</th>
                        <th scope="col">Status</th>
                        <th scope="col"><span class="visually-hidden">Ações</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($modalidades as $modalidade): ?>
                        <tr>
                            <td class="modalidades-table__nome">
                                <strong><?= e($modalidade['nome']) ?></strong>
                            </td>
                            <td><?= e($modalidade['descricao'] ?? '—') ?></td>
                            <td><?= $modalidade['rede_id'] === null ? 'Global' : e($modalidade['rede_nome'] ?? 'Rede') ?></td>
                            <td><span class="badge <?= $modalidade['status'] === 'ATIVO' ? 'badge--success' : 'badge--warning' ?>"><?= e($modalidade['status']) ?></span></td>
                            <td class="table__actions modalidades-table__actions">
                                <?php if ($canEdit): ?>
                                    <a class="btn btn-ghost btn-sm btn-icon" href="/admin/modalidades/<?= e((string) $modalidade['id']) ?>/editar" aria-label="Editar <?= e($modalidade['nome']) ?>">
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
        <div class="pessoas-grid modalidades-grid" id="modalidades-grid">
            <?php foreach ($modalidades as $modalidade): ?>
                <article class="pessoa-card modalidade-card">
                    <header class="pessoa-card__header modalidade-card__header">
                        <div class="pessoa-card__main modalidade-card__main">
                            <h4 class="pessoa-card__nome modalidade-card__nome"><?= e($modalidade['nome']) ?></h4>
                            <span class="badge <?= $modalidade['status'] === 'ATIVO' ? 'badge--success' : 'badge--warning' ?>">
                                <?= e($modalidade['status']) ?>
                            </span>
                        </div>
                        <div class="pessoa-card__status modalidade-card__abragencia">
                            <span class="text-muted">
                                <?= $modalidade['rede_id'] === null ? 'Global' : e($modalidade['rede_nome'] ?? 'Rede') ?>
                            </span>
                        </div>
                    </header>
                    <?php if (!empty($modalidade['descricao'])): ?>
                        <div class="pessoa-card__details modalidade-card__details">
                            <dl class="pessoa-card__field">
                                <dt>Descrição</dt>
                                <dd><?= e($modalidade['descricao']) ?></dd>
                            </dl>
                        </div>
                    <?php endif; ?>
                    <footer class="pessoa-card__actions modalidade-card__actions">
                        <?php if ($canEdit): ?>
                            <a class="btn btn-ghost btn-sm" href="/admin/modalidades/<?= e((string) $modalidade['id']) ?>/editar">
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
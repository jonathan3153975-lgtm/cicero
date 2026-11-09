<?php
$contextLabel = is_array($context ?? null) ? ($context['label'] ?? '—') : '—';
$permissionCount = count($permissions ?? []);
$canViewSettings = isset($can) && $can('configuracao.visualizar');
?>
<section class="page-heading">
    <div>
        <h2>Olá, <?= e($user['name'] ?? 'Usuário') ?></h2>
        <p class="text-muted">Área protegida por autenticação e autorização por permissão.</p>
    </div>
    <span class="badge badge--accent">Contexto: <?= e($contextLabel) ?></span>
</section>

<section class="stats-grid" aria-label="Resumo do contexto">
    <article class="stat-card">
        <span class="stat-card__label">Contexto</span>
        <strong class="stat-card__value stat-card__value--text"><?= e($contextLabel) ?></strong>
        <span class="stat-card__hint">Rede/unidade ativa na sessão</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__label">Permissões efetivas</span>
        <strong class="stat-card__value"><?= e((string) $permissionCount) ?></strong>
        <span class="stat-card__hint">Calculadas para o contexto atual</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__label">Sessão</span>
        <strong class="stat-card__value">Ativa</strong>
        <span class="stat-card__hint">Regenerada após autenticação</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__label">Usuário</span>
        <strong class="stat-card__value stat-card__value--text"><?= e($user['email'] ?? '—') ?></strong>
        <span class="stat-card__hint">Cadastro real em <code>usuarios</code></span>
    </article>
</section>

<section class="card">
    <header class="card__header">
        <div>
            <h3>Permissões efetivas</h3>
            <p class="text-muted">União dos perfis e permissões explícitas no contexto atual.</p>
        </div>
        <span class="badge"><?= e((string) $permissionCount) ?> permissões</span>
    </header>

    <?php if (($permissions ?? []) === []): ?>
        <p class="text-muted">Nenhuma permissão efetiva neste contexto.</p>
    <?php else: ?>
        <div class="badge-list">
            <?php foreach ($permissions as $permission): ?>
                <span class="badge badge--success"><?= e($permission) ?></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="card">
    <header class="card__header">
        <div>
            <h3>Atalhos</h3>
            <p class="text-muted">Ações disponíveis para o seu contexto.</p>
        </div>
    </header>

    <div class="quick-actions">
        <a class="quick-action" href="/contexto">
            <strong>Trocar contexto</strong>
            <span class="text-muted">Redes e unidades vinculadas</span>
        </a>
        <?php if ($canViewSettings): ?>
            <a class="quick-action" href="/configuracoes">
                <strong>Configurações</strong>
                <span class="text-muted">Tokens de tema e identidade</span>
            </a>
        <?php endif; ?>
        <a class="quick-action" href="/api/v1/health" target="_blank" rel="noopener">
            <strong>Health check</strong>
            <span class="text-muted">JSON padronizado da API</span>
        </a>
    </div>
</section>

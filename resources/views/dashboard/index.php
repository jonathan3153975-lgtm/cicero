<section class="page-heading">
    <div>
        <h2>Olá, <?= e($user['name'] ?? 'Administrador') ?></h2>
        <p class="text-muted">Área protegida por autenticação e autorização por papel.</p>
    </div>
    <span class="badge badge--accent">Papel: <?= e($user['role'] ?? '—') ?></span>
</section>

<section class="stats-grid" aria-label="Resumo">
    <article class="stat-card">
        <span class="stat-card__label">Redes</span>
        <strong class="stat-card__value">—</strong>
        <span class="stat-card__hint">Modelagem na próxima etapa</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__label">Unidades</span>
        <strong class="stat-card__value">—</strong>
        <span class="stat-card__hint">Isolamento multiempresa</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__label">Usuários</span>
        <strong class="stat-card__value">1</strong>
        <span class="stat-card__hint">Usuário de demonstração</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__label">Sessão</span>
        <strong class="stat-card__value">Ativa</strong>
        <span class="stat-card__hint">Regenerada após autenticação</span>
    </article>
</section>

<section class="card">
    <header class="card__header">
        <div>
            <h3>Atalhos da fundação</h3>
            <p class="text-muted">Pontos implementados nesta etapa.</p>
        </div>
    </header>

    <div class="quick-actions">
        <a class="quick-action" href="/configuracoes">
            <strong>Configurações</strong>
            <span class="text-muted">Tokens de tema e identidade</span>
        </a>
        <a class="quick-action" href="/api/v1/health" target="_blank" rel="noopener">
            <strong>Health check</strong>
            <span class="text-muted">JSON padronizado da API</span>
        </a>
        <a class="quick-action" href="/" target="_self">
            <strong>Página inicial</strong>
            <span class="text-muted">Demonstração do Design System</span>
        </a>
    </div>
</section>

<section class="card" aria-label="Registros recentes">
    <header class="card__header">
        <div>
            <h3>Registros recentes</h3>
            <p class="text-muted">Tabela de exemplo do Design System.</p>
        </div>
        <span class="badge">Demonstração</span>
    </header>

    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Evento</th>
                    <th scope="col">Origem</th>
                    <th scope="col">Data</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Fundação criada</td>
                    <td>Sistema</td>
                    <td><?= e(date('d/m/Y H:i')) ?></td>
                    <td><span class="badge badge--success">Concluído</span></td>
                </tr>
                <tr>
                    <td>Migrations aplicadas</td>
                    <td>Banco de dados</td>
                    <td><?= e(date('d/m/Y')) ?></td>
                    <td><span class="badge badge--success">Concluído</span></td>
                </tr>
                <tr>
                    <td>Temas validados</td>
                    <td>Design System</td>
                    <td><?= e(date('d/m/Y')) ?></td>
                    <td><span class="badge badge--success">Concluído</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

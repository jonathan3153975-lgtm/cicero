<section class="page-hero">
    <div class="page-hero__content">
        <span class="badge badge--accent">Fundação implementada</span>
        <h2>Base sólida para a gestão de redes de academias</h2>
        <p class="text-muted">
            Estrutura MVC com Services e Repositories, configuração por ambiente,
            roteamento web e API, camada de dados com PDO e Design System próprio.
        </p>
        <div class="page-hero__actions">
            <a class="btn btn-primary" href="/login">Acessar painel</a>
            <a class="btn btn-ghost" href="#arquitetura">Conhecer a estrutura</a>
        </div>
    </div>

    <div class="page-hero__panel" data-health-check>
        <div class="health-card">
            <span class="health-card__dot" data-health-dot></span>
            <div class="health-card__text">
                <strong data-health-status>Verificando API…</strong>
                <small class="text-muted" data-health-meta>GET /api/v1/health</small>
            </div>
        </div>
        <div class="panel-code">
            <span>PHP 8.2+</span>
            <span>MariaDB</span>
            <span>PSR-4</span>
            <span>REST /api/v1</span>
        </div>
    </div>
</section>

<section class="stats-grid" aria-label="Indicadores da fundação">
    <article class="stat-card">
        <span class="stat-card__label">Arquitetura</span>
        <strong class="stat-card__value">MVC</strong>
        <span class="stat-card__hint">Controllers enxutos e regras em Services</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__label">Persistência</span>
        <strong class="stat-card__value">PDO</strong>
        <span class="stat-card__hint">Prepared statements e transações</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__label">Segurança</span>
        <strong class="stat-card__value">CSRF</strong>
        <span class="stat-card__hint">Sessões seguras e middleware de autorização</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__label">Interface</span>
        <strong class="stat-card__value">2 temas</strong>
        <span class="stat-card__hint">Tokens CSS configuráveis por empresa</span>
    </article>
</section>

<section class="card-grid" id="arquitetura" aria-label="Pilares da fundação">
    <article class="card">
        <h3>Organização em camadas</h3>
        <p class="text-muted">
            Controllers recebem a requisição, Services coordenam regras e Repositories
            concentram o acesso ao banco. Views apenas exibem dados escapados.
        </p>
        <ul class="feature-list">
            <li>Injeção de dependência por construtor</li>
            <li>Container com autowiring simples</li>
            <li>Tratamento centralizado de exceções</li>
        </ul>
    </article>

    <article class="card">
        <h3>Segurança por padrão</h3>
        <p class="text-muted">
            Cookies HttpOnly, regeneração de sessão no login, proteção CSRF em
            requisições de estado e escape de saída HTML.
        </p>
        <ul class="feature-list">
            <li>Hash de senha com <code>password_hash()</code></li>
            <li>Controle básico de tentativas de login</li>
            <li>Arquivos internos fora do diretório público</li>
        </ul>
    </article>

    <article class="card">
        <h3>Identidade configurável</h3>
        <p class="text-muted">
            Componentes consomem tokens CSS. As cores institucionais podem ser
            sobrescritas por variáveis de ambiente validadas pelo backend.
        </p>
        <ul class="feature-list">
            <li>Tema escuro e tema claro</li>
            <li>Layout responsivo mobile-first</li>
            <li>Personalização sem novo deploy</li>
        </ul>
    </article>
</section>

<section class="card" aria-label="Verificações da fundação">
    <header class="card__header">
        <div>
            <h3>Critérios de aceite</h3>
            <p class="text-muted">Itens verificáveis da estrutura inicial.</p>
        </div>
        <span class="badge badge--success">Em validação</span>
    </header>

    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Critério</th>
                    <th scope="col">Camada</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Autoload PSR-4 pelo namespace <code>App\</code></td>
                    <td>Composer</td>
                    <td><span class="badge badge--success">Pronto</span></td>
                </tr>
                <tr>
                    <td>Rota web e rota de API com JSON padronizado</td>
                    <td>Roteamento</td>
                    <td><span class="badge badge--success">Pronto</span></td>
                </tr>
                <tr>
                    <td>Exceções sem stack trace em produção</td>
                    <td>Erros</td>
                    <td><span class="badge badge--success">Pronto</span></td>
                </tr>
                <tr>
                    <td>Conexão MariaDB via PDO</td>
                    <td>Dados</td>
                    <td><span class="badge badge--success">Pronto</span></td>
                </tr>
                <tr>
                    <td>Troca de cores sem alterar componentes</td>
                    <td>Design System</td>
                    <td><span class="badge badge--success">Pronto</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

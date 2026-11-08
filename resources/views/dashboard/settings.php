<section class="page-heading">
    <div>
        <h2>Configurações</h2>
        <p class="text-muted">Esta rota exige autenticação e o papel <code>admin</code>.</p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/dashboard">Voltar ao painel</a>
</section>

<section class="card-grid">
    <article class="card">
        <h3>Tokens de identidade</h3>
        <p class="text-muted">
            Os componentes consomem exclusivamente variáveis CSS. A personalização
            por rede será persistida em banco na próxima etapa.
        </p>
        <div class="swatch-grid">
            <div class="swatch">
                <span class="swatch__color" style="background: var(--brand-primary)"></span>
                <span class="swatch__label">primary</span>
            </div>
            <div class="swatch">
                <span class="swatch__color" style="background: var(--brand-accent)"></span>
                <span class="swatch__label">accent</span>
            </div>
            <div class="swatch">
                <span class="swatch__color" style="background: var(--color-background)"></span>
                <span class="swatch__label">background</span>
            </div>
            <div class="swatch">
                <span class="swatch__color" style="background: var(--color-surface)"></span>
                <span class="swatch__label">surface</span>
            </div>
            <div class="swatch">
                <span class="swatch__color" style="background: var(--color-border)"></span>
                <span class="swatch__label">border</span>
            </div>
            <div class="swatch">
                <span class="swatch__color" style="background: var(--color-text)"></span>
                <span class="swatch__label">text</span>
            </div>
        </div>
    </article>

    <article class="card">
        <h3>Como personalizar</h3>
        <p class="text-muted">Defina valores hexadecimais no arquivo <code>.env</code>:</p>
        <pre class="code-block">BRAND_PRIMARY=#F55800
BRAND_ACCENT=#E0FF51
BRAND_BACKGROUND=#191919
BRAND_SURFACE=#242424</pre>
        <p class="text-muted">
            O backend valida cada valor e gera as CSS Custom Properties em um único
            ponto controlado. CSS ou JavaScript arbitrários não são aceitos.
        </p>
    </article>
</section>

<section class="card">
    <header class="card__header">
        <div>
            <h3>Estado atual</h3>
            <p class="text-muted">Resumo não sensível das configurações aplicadas.</p>
        </div>
    </header>
    <div class="table-wrapper">
        <table class="table">
            <tbody>
                <tr>
                    <th scope="row">Ambiente</th>
                    <td><?= e($app['env'] ?? 'local') ?></td>
                </tr>
                <tr>
                    <th scope="row">Depuração</th>
                    <td><?= !empty($app['debug']) ? 'Ativada (somente local)' : 'Desativada' ?></td>
                </tr>
                <tr>
                    <th scope="row">Fuso horário</th>
                    <td><?= e($app['timezone'] ?? '—') ?></td>
                </tr>
                <tr>
                    <th scope="row">Tema padrão</th>
                    <td><?= e($defaultTheme ?? 'dark') ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

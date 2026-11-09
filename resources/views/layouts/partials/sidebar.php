<?php
$canViewSettings = isset($can) && $can('configuracao.visualizar');
$currentPath = $currentPath ?? '/';
$isActive = static function (string $href) use ($currentPath): bool {
    if ($currentPath === $href) {
        return true;
    }

    return $href !== '/' && $href !== '/admin' && str_starts_with($currentPath, $href . '/');
};

$groups = [
    [
        'key' => 'estrutura',
        'label' => 'Config. estrutura',
        'icon' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z"/></svg>',
        'items' => [
            ['permission' => 'rede.visualizar', 'label' => 'Redes', 'href' => '/admin/redes'],
            ['permission' => 'plano_rede.visualizar', 'label' => 'Planos de rede', 'href' => '/admin/planos-rede'],
            ['permission' => 'regiao.visualizar', 'label' => 'Regiões', 'href' => '/admin/regioes'],
            ['permission' => 'unidade.visualizar', 'label' => 'Unidades', 'href' => '/admin/unidades'],
            ['permission' => 'financeiro.visualizar', 'label' => 'Contas bancárias', 'href' => '/admin/contas-bancarias'],
            ['permission' => 'dispositivo.visualizar', 'label' => 'Dispositivos de acesso', 'href' => '/admin/dispositivos'],
            ['permission' => 'usuario.visualizar', 'label' => 'Usuários', 'href' => '/admin/usuarios'],
            ['permission' => 'perfil.visualizar', 'label' => 'Perfis', 'href' => '/admin/perfis'],
            ['permission' => 'permissao.visualizar', 'label' => 'Permissões', 'href' => '/admin/permissoes'],
            ['permission' => 'vinculo.visualizar', 'label' => 'Vínculos', 'href' => '/admin/vinculos'],
            ['permission' => 'auditoria.visualizar', 'label' => 'Auditoria', 'href' => '/admin/auditoria'],
        ],
    ],
    [
        'key' => 'cadastros',
        'label' => 'Cadastros',
        'icon' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm7 2a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM2 20a7 7 0 0 1 14 0v1H2zm14.5 1a5.5 5.5 0 0 0-2.2-4.4A5 5 0 0 1 22 21z"/></svg>',
        'items' => [
            ['permission' => 'pessoa.visualizar', 'label' => 'Pessoas', 'href' => '/admin/pessoas'],
            ['permission' => 'modalidade.visualizar', 'label' => 'Modalidades', 'href' => '/admin/modalidades'],
            ['permission' => 'plano.visualizar', 'label' => 'Planos', 'href' => '/admin/planos'],
            ['permission' => 'aluno.visualizar', 'label' => 'Alunos', 'href' => '/admin/alunos'],
            ['permission' => 'matricula.visualizar', 'label' => 'Matrículas', 'href' => '/admin/matriculas'],
            ['permission' => 'turma.visualizar', 'label' => 'Turmas', 'href' => '/admin/turmas'],
        ],
    ],
    [
        'key' => 'financeiro',
        'label' => 'Financeiro',
        'icon' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 15.9V19h-2v-1.1a4 4 0 0 1-3.2-3.2l1.9-.5A2.2 2.2 0 0 0 12 16a2 2 0 0 0 1.3-3.5l-1.7-1.2A3.3 3.3 0 0 1 12 5a3.3 3.3 0 0 1 3.2 2.7l-1.9.5A1.4 1.4 0 0 0 12 7.2a1.2 1.2 0 0 0-.8 2.1l1.7 1.2A3.4 3.4 0 0 1 13 17.9z"/></svg>',
        'items' => [
            ['permission' => 'financeiro.visualizar', 'label' => 'Conciliação bancária', 'href' => '/admin/conciliacoes'],
            ['permission' => 'financeiro.visualizar', 'label' => 'Contas a pagar', 'href' => '/admin/contas-pagar'],
            ['permission' => 'financeiro.visualizar', 'label' => 'Cobranças', 'href' => '/admin/cobrancas'],
            ['permission' => 'financeiro.visualizar', 'label' => 'Pagamentos', 'href' => '/admin/pagamentos'],
        ],
    ],
    [
        'key' => 'administracao',
        'label' => 'Administração',
        'icon' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 4 5v6c0 5.2 3.4 9.9 8 11 4.6-1.1 8-5.8 8-11V5zm0 4.2 4 1.5v3.4c0 3.2-1.7 6.3-4 7.6-2.3-1.3-4-4.4-4-7.6V7.7z"/></svg>',
        'items' => [
            ['permission' => 'evento_acesso.visualizar', 'label' => 'Eventos de acesso', 'href' => '/admin/acessos'],
            ['permission' => 'contrato.visualizar', 'label' => 'Contratos', 'href' => '/admin/contratos'],
            ['permission' => 'reserva.visualizar', 'label' => 'Reservas', 'href' => '/admin/reservas'],
            ['permission' => 'frequencia.visualizar', 'label' => 'Frequência', 'href' => '/admin/frequencias'],
        ],
    ],
];

$visibleGroups = [];

foreach ($groups as $group) {
    $items = array_values(array_filter(
        $group['items'],
        static fn (array $item): bool => isset($can) && $can($item['permission'])
    ));

    if ($items === []) {
        continue;
    }

    $group['items'] = $items;
    $group['active'] = false;

    foreach ($items as $item) {
        if ($isActive($item['href'])) {
            $group['active'] = true;
            break;
        }
    }

    $visibleGroups[] = $group;
}
?>
<aside class="sidebar" id="sidebar" aria-label="Navegação principal" data-sidebar>
    <div class="sidebar__head">
        <a class="sidebar__brand" href="/">
            <span class="sidebar__logo" aria-hidden="true">F</span>
            <span class="sidebar__brand-text">
                <strong><?= e($app['name'] ?? 'Plataforma Fitness') ?></strong>
                <small>Gestão de academias</small>
            </span>
        </a>
        <button class="sidebar__collapse" type="button" data-sidebar-collapse aria-label="Recolher menu" title="Recolher menu">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15.4 7.4 14 6l-6 6 6 6 1.4-1.4L10.8 12z"/></svg>
        </button>
    </div>

    <nav class="sidebar__nav">
        <a class="nav-link <?= $isActive('/') ? 'is-active' : '' ?>" href="/">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/></svg>
            <span>Início</span>
        </a>

        <?php if (!empty($isAuthenticated)): ?>
            <a class="nav-link <?= $isActive('/dashboard') ? 'is-active' : '' ?>" href="/dashboard">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z"/></svg>
                <span>Painel</span>
            </a>
            <a class="nav-link <?= $isActive('/contexto') ? 'is-active' : '' ?>" href="/contexto">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm0 2c1.7 0 3.2 1.6 4.2 4H7.8C8.8 5.6 10.3 4 12 4zM4.3 8h2.2a17 17 0 0 0 0 8H4.3a8 8 0 0 1 0-8zm3.5 0h8.4a15 15 0 0 1 0 8H7.8a15 15 0 0 1 0-8zm9.7 0h2.2a8 8 0 0 1 0 8h-2.2a17 17 0 0 0 0-8zM12 20c-1.7 0-3.2-1.6-4.2-4h8.4c-1 2.4-2.5 4-4.2 4z"/></svg>
                <span>Trocar contexto</span>
            </a>
            <?php if ($canViewSettings): ?>
                <a class="nav-link <?= $isActive('/configuracoes') ? 'is-active' : '' ?>" href="/configuracoes">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 2.4 2.4 3.3-.6.9 3.2 3 1.6-1.5 3 .9 3.3-3.1 1.3-.7 3.3L12 19l-3.2 1.5-.7-3.3-3.1-1.3.9-3.3L4.4 9.6l3-1.6.9-3.2 3.3.6zM12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8z"/></svg>
                    <span>Configurações</span>
                </a>
            <?php endif; ?>

            <?php if (isset($can) && $can('usuario.visualizar')): ?>
                <a class="nav-link <?= $isActive('/admin') ? 'is-active' : '' ?>" href="/admin">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h18v4H3zm0 7h18v4H3zm0 7h18v4H3z"/></svg>
                    <span>Visão geral</span>
                </a>
            <?php endif; ?>

            <?php foreach ($visibleGroups as $group): ?>
                <?php
                $panelId = 'nav-group-' . $group['key'];
                $isOpen = $group['active'];
                ?>
                <div class="nav-group <?= $isOpen ? 'is-open is-current' : '' ?>" data-nav-group="<?= e($group['key']) ?>">
                    <button
                        class="nav-group__toggle"
                        type="button"
                        data-nav-group-toggle
                        aria-expanded="<?= $isOpen ? 'true' : 'false' ?>"
                        aria-controls="<?= e($panelId) ?>"
                    >
                        <span class="nav-group__icon"><?= $group['icon'] ?></span>
                        <span class="nav-group__label"><?= e($group['label']) ?></span>
                        <svg class="nav-group__chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m8.6 5.6 1.4-1.4 8 8-8 8-1.4-1.4 6.6-6.6z"/></svg>
                    </button>
                    <div class="nav-group__items" id="<?= e($panelId) ?>" <?= $isOpen ? '' : 'hidden' ?>>
                        <?php foreach ($group['items'] as $item): ?>
                            <a class="nav-link nav-link--sub <?= $isActive($item['href']) ? 'is-active' : '' ?>" href="<?= e($item['href']) ?>">
                                <span><?= e($item['label']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <a class="nav-link <?= $isActive('/login') ? 'is-active' : '' ?>" href="/login">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h5v-2H5V5h5zm5.6 4.4L14.2 8.8 16.4 11H8v2h8.4l-2.2 2.2 1.4 1.4L20.4 12z"/></svg>
                <span>Entrar</span>
            </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar__footer">
        <?php if (!empty($isAuthenticated)): ?>
            <form method="post" action="/logout">
                <input type="hidden" name="<?= e($csrfName ?? '_token') ?>" value="<?= e($csrfToken ?? '') ?>">
                <button class="btn btn-ghost btn-block" type="submit">Sair</button>
            </form>
        <?php else: ?>
            <span class="sidebar__hint">Fundação v1.0</span>
        <?php endif; ?>
    </div>
</aside>

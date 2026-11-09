<?php
$canViewSettings = isset($can) && $can('configuracao.visualizar');
$adminLinks = [
    ['permission' => 'rede.visualizar', 'label' => 'Redes', 'href' => '/admin/redes'],
    ['permission' => 'regiao.visualizar', 'label' => 'Regiões', 'href' => '/admin/regioes'],
    ['permission' => 'unidade.visualizar', 'label' => 'Unidades', 'href' => '/admin/unidades'],
    ['permission' => 'pessoa.visualizar', 'label' => 'Pessoas', 'href' => '/admin/pessoas'],
    ['permission' => 'aluno.visualizar', 'label' => 'Alunos', 'href' => '/admin/alunos'],
    ['permission' => 'plano.visualizar', 'label' => 'Planos', 'href' => '/admin/planos'],
    ['permission' => 'matricula.visualizar', 'label' => 'Matrículas', 'href' => '/admin/matriculas'],
    ['permission' => 'contrato.visualizar', 'label' => 'Contratos', 'href' => '/admin/contratos'],
    ['permission' => 'financeiro.visualizar', 'label' => 'Cobranças', 'href' => '/admin/cobrancas'],
    ['permission' => 'financeiro.visualizar', 'label' => 'Pagamentos', 'href' => '/admin/pagamentos'],
    ['permission' => 'financeiro.visualizar', 'label' => 'Contas a pagar', 'href' => '/admin/contas-pagar'],
    ['permission' => 'financeiro.visualizar', 'label' => 'Contas bancárias', 'href' => '/admin/contas-bancarias'],
    ['permission' => 'financeiro.visualizar', 'label' => 'Conciliação', 'href' => '/admin/conciliacoes'],
    ['permission' => 'turma.visualizar', 'label' => 'Turmas', 'href' => '/admin/turmas'],
    ['permission' => 'frequencia.visualizar', 'label' => 'Frequência', 'href' => '/admin/frequencias'],
    ['permission' => 'reserva.visualizar', 'label' => 'Reservas', 'href' => '/admin/reservas'],
    ['permission' => 'dispositivo.visualizar', 'label' => 'Dispositivos', 'href' => '/admin/dispositivos'],
    ['permission' => 'evento_acesso.visualizar', 'label' => 'Acessos', 'href' => '/admin/acessos'],
    ['permission' => 'usuario.visualizar', 'label' => 'Usuários', 'href' => '/admin/usuarios'],
    ['permission' => 'perfil.visualizar', 'label' => 'Perfis', 'href' => '/admin/perfis'],
    ['permission' => 'permissao.visualizar', 'label' => 'Permissões', 'href' => '/admin/permissoes'],
    ['permission' => 'vinculo.visualizar', 'label' => 'Vínculos', 'href' => '/admin/vinculos'],
    ['permission' => 'auditoria.visualizar', 'label' => 'Auditoria', 'href' => '/admin/auditoria'],
];
$visibleAdminLinks = array_values(array_filter(
    $adminLinks,
    static fn (array $link): bool => isset($can) && $can($link['permission'])
));
?>
<aside class="sidebar" id="sidebar" aria-label="Navegação principal">
    <a class="sidebar__brand" href="/">
        <span class="sidebar__logo" aria-hidden="true">F</span>
        <span class="sidebar__brand-text">
            <strong><?= e($app['name'] ?? 'Plataforma Fitness') ?></strong>
            <small>Gestão de academias</small>
        </span>
    </a>

    <nav class="sidebar__nav">
        <a class="nav-link <?= ($currentPath ?? '/') === '/' ? 'is-active' : '' ?>" href="/">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/></svg>
            <span>Início</span>
        </a>

        <?php if (!empty($isAuthenticated)): ?>
            <a class="nav-link <?= ($currentPath ?? '') === '/dashboard' ? 'is-active' : '' ?>" href="/dashboard">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z"/></svg>
                <span>Painel</span>
            </a>
            <a class="nav-link <?= ($currentPath ?? '') === '/contexto' ? 'is-active' : '' ?>" href="/contexto">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm0 2c1.7 0 3.2 1.6 4.2 4H7.8C8.8 5.6 10.3 4 12 4zM4.3 8h2.2a17 17 0 0 0 0 8H4.3a8 8 0 0 1 0-8zm3.5 0h8.4a15 15 0 0 1 0 8H7.8a15 15 0 0 1 0-8zm9.7 0h2.2a8 8 0 0 1 0 8h-2.2a17 17 0 0 0 0-8zM12 20c-1.7 0-3.2-1.6-4.2-4h8.4c-1 2.4-2.5 4-4.2 4z"/></svg>
                <span>Trocar contexto</span>
            </a>
            <?php if ($canViewSettings): ?>
                <a class="nav-link <?= ($currentPath ?? '') === '/configuracoes' ? 'is-active' : '' ?>" href="/configuracoes">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 2.4 2.4 3.3-.6.9 3.2 3 1.6-1.5 3 .9 3.3-3.1 1.3-.7 3.3L12 19l-3.2 1.5-.7-3.3-3.1-1.3.9-3.3L4.4 9.6l3-1.6.9-3.2 3.3.6zM12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8z"/></svg>
                    <span>Configurações</span>
                </a>
            <?php endif; ?>

            <?php if ($visibleAdminLinks !== []): ?>
                <span class="sidebar__section">Administração</span>
                <?php if (isset($can) && $can('usuario.visualizar')): ?>
                    <a class="nav-link <?= ($currentPath ?? '') === '/admin' ? 'is-active' : '' ?>" href="/admin">
                        <span>Visão geral</span>
                    </a>
                <?php endif; ?>
                <?php foreach ($visibleAdminLinks as $link): ?>
                    <a class="nav-link <?= ($currentPath ?? '') === $link['href'] ? 'is-active' : '' ?>" href="<?= e($link['href']) ?>">
                        <span><?= e($link['label']) ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php else: ?>
            <a class="nav-link <?= ($currentPath ?? '') === '/login' ? 'is-active' : '' ?>" href="/login">
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

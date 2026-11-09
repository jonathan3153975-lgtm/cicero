<?php
$canSee = static fn (string $permission): bool => isset($can) && $can($permission);
$cards = [
    ['permission' => 'rede.visualizar', 'label' => 'Redes', 'value' => $counts['redes'], 'href' => '/admin/redes'],
    ['permission' => 'regiao.visualizar', 'label' => 'Regiões', 'value' => $counts['regioes'], 'href' => '/admin/regioes'],
    ['permission' => 'unidade.visualizar', 'label' => 'Unidades', 'value' => $counts['unidades'], 'href' => '/admin/unidades'],
    ['permission' => 'usuario.visualizar', 'label' => 'Usuários', 'value' => $counts['usuarios'], 'href' => '/admin/usuarios'],
    ['permission' => 'perfil.visualizar', 'label' => 'Perfis', 'value' => $counts['perfis'], 'href' => '/admin/perfis'],
    ['permission' => 'permissao.visualizar', 'label' => 'Permissões', 'value' => $counts['permissoes'], 'href' => '/admin/permissoes'],
    ['permission' => 'vinculo.visualizar', 'label' => 'Vínculos', 'value' => null, 'href' => '/admin/vinculos'],
    ['permission' => 'auditoria.visualizar', 'label' => 'Auditoria', 'value' => null, 'href' => '/admin/auditoria'],
];
?>
<section class="page-heading">
    <div>
        <h2>Administração</h2>
        <p class="text-muted">Gestão de redes, unidades, usuários, perfis e vínculos.</p>
    </div>
    <span class="badge badge--accent">Contexto: <?= e($contextLabel) ?></span>
</section>

<section class="card-grid" aria-label="Áreas administrativas">
    <?php foreach ($cards as $card): ?>
        <?php if (!$canSee($card['permission'])): ?>
            <?php continue; ?>
        <?php endif; ?>
        <a class="card quick-action" href="<?= e($card['href']) ?>">
            <span class="stat-card__label"><?= e($card['label']) ?></span>
            <?php if ($card['value'] !== null): ?>
                <strong class="stat-card__value"><?= e((string) $card['value']) ?></strong>
            <?php else: ?>
                <strong class="stat-card__value stat-card__value--text">Acessar</strong>
            <?php endif; ?>
            <span class="text-muted">Gerenciar <?= e(mb_strtolower($card['label'])) ?></span>
        </a>
    <?php endforeach; ?>
</section>

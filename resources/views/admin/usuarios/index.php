<?php
$canCreate = isset($can) && $can('usuario.criar');
$canEdit = isset($can) && $can('usuario.editar');
$isPlatform = $isPlatform ?? false;
$filterRedeId = $filterRedeId ?? null;
$filterRegiaoId = $filterRegiaoId ?? null;
$filterUnidadeId = $filterUnidadeId ?? null;
$redes = $redes ?? [];
$regioes = $regioes ?? [];
$unidades = $unidades ?? [];
?>
<section class="page-heading">
    <div>
        <h2>Usuários</h2>
        <p class="text-muted">Cadastros únicos e vínculos com redes e unidades.</p>
    </div>
    <?php if ($canCreate): ?>
        <a class="btn btn-primary" href="/admin/usuarios/novo">Novo usuário</a>
    <?php endif; ?>
</section>

<!-- Filtros: Rede, Região, Unidade (cascata) -->
<section class="card">
    <form method="get" action="/admin/usuarios" class="form">
        <div class="row">
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="filter_rede_id">Rede</label>
                    <select class="input" id="filter_rede_id" name="rede_id" data-rede-select>
                        <option value="">Todas as redes</option>
                        <?php foreach ($redes as $rede): ?>
                            <option value="<?= e((string) $rede['id']) ?>" <?= $filterRedeId === (string) $rede['id'] ? 'selected' : '' ?>>
                                <?= e($rede['nome_fantasia']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="filter_regiao_id">Região</label>
                    <select class="input" id="filter_regiao_id" name="regiao_id" data-regiao-select>
                        <option value="">Todas as regiões</option>
                        <?php foreach ($regioes as $regiao): ?>
                            <option value="<?= e((string) $regiao['id']) ?>" data-rede-id="<?= e((string) $regiao['rede_id']) ?>" <?= $filterRegiaoId === (string) $regiao['id'] ? 'selected' : '' ?>>
                                <?= e($regiao['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-sm-12 col-md-4">
                <div class="form__field">
                    <label for="filter_unidade_id">Unidade</label>
                    <select class="input" id="filter_unidade_id" name="unidade_id">
                        <option value="">Todas as unidades</option>
                        <?php foreach ($unidades as $unidade): ?>
                            <option value="<?= e((string) $unidade['id']) ?>" data-regiao-id="<?= e((string) ($unidade['regiao_id'] ?? '')) ?>" <?= $filterUnidadeId === (string) $unidade['id'] ? 'selected' : '' ?>>
                                <?= e($unidade['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Filtrar</button>
            <a class="btn btn-ghost" href="/admin/usuarios">Limpar</a>
        </div>
    </form>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Nome</th>
                    <th scope="col">E-mail</th>
                    <th scope="col">Status</th>
                    <th scope="col">Último login</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><strong><?= e($usuario['nome']) ?></strong></td>
                        <td><?= e($usuario['email']) ?></td>
                        <td><span class="badge <?= $usuario['status'] === 'ATIVO' ? 'badge--success' : 'badge--danger' ?>"><?= e($usuario['status']) ?></span></td>
                        <td><?= e($usuario['ultimo_login_em'] ?? '—') ?></td>
                        <td class="table__actions">
                            <?php if ($canEdit): ?>
                                <a class="btn btn-ghost btn-sm" href="/admin/usuarios/<?= e((string) $usuario['id']) ?>/editar">Editar</a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($usuarios === []): ?>
                    <tr><td colspan="5" class="text-muted">Nenhum usuário encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

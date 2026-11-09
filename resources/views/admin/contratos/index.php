<?php $canEdit = isset($can) && $can('contrato.editar'); ?>
<section class="page-heading">
    <div>
        <h2>Contratos</h2>
        <p class="text-muted">Contratos gerados pelas matrículas.</p>
    </div>
</section>

<section class="card">
    <form method="get" action="/admin/contratos" class="form-grid">
        <div class="form__field">
            <label for="busca">Buscar</label>
            <input class="input" id="busca" name="busca" value="<?= e($search ?? '') ?>" placeholder="Nome do aluno ou número do contrato">
        </div>
        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Buscar</button>
            <a class="btn btn-ghost" href="/admin/contratos">Limpar</a>
        </div>
    </form>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Contrato</th>
                    <th scope="col">Aluno</th>
                    <th scope="col">Plano</th>
                    <th scope="col">Unidade</th>
                    <th scope="col">Vigência</th>
                    <th scope="col">Mensal</th>
                    <th scope="col">Status</th>
                    <th scope="col">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contratos as $contrato): ?>
                    <tr>
                        <td><code><?= e($contrato['numero']) ?></code></td>
                        <td><strong><?= e($contrato['aluno_nome']) ?></strong></td>
                        <td><?= e($contrato['plano_nome']) ?></td>
                        <td><?= e($contrato['unidade_nome']) ?></td>
                        <td><?= e($contrato['data_inicio']) ?> a <?= e($contrato['data_fim'] ?? '—') ?></td>
                        <td>R$ <?= e(number_format((float) $contrato['valor_mensal'], 2, ',', '.')) ?></td>
                        <td><span class="badge <?= $contrato['status'] === 'ATIVO' ? 'badge--success' : 'badge--warning' ?>"><?= e($contrato['status']) ?></span></td>
                        <td class="table__actions">
                            <?php if ($canEdit): ?>
                                <a class="btn btn-ghost btn-sm" href="/admin/contratos/<?= e((string) $contrato['id']) ?>/editar">Editar</a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($contratos === []): ?>
                    <tr><td colspan="8" class="text-muted">Nenhum contrato encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

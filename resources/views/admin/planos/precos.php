<?php
$old = $old ?? [];
$val = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $default);
$err = static fn (string $key): ?string => $errors[$key][0] ?? null;
?>
<section class="page-heading">
    <div>
        <h2>Preços — <?= e($plano['nome']) ?></h2>
        <p class="text-muted">Vigências e valores por unidade.</p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/planos">Voltar</a>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Unidade</th>
                    <th scope="col">Vigência</th>
                    <th scope="col">Matrícula</th>
                    <th scope="col">Mensal</th>
                    <th scope="col">Adesão</th>
                    <th scope="col">Taxa</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($precos as $preco): ?>
                    <tr>
                        <td><strong><?= e($preco['unidade_nome']) ?></strong></td>
                        <td><?= e($preco['vigencia_inicio']) ?> a <?= e($preco['vigencia_fim'] ?? '—') ?></td>
                        <td>R$ <?= e(number_format((float) $preco['valor_matricula'], 2, ',', '.')) ?></td>
                        <td>R$ <?= e(number_format((float) $preco['valor_mensal'], 2, ',', '.')) ?></td>
                        <td><?= e($preco['valor_adesao'] !== null ? 'R$ ' . number_format((float) $preco['valor_adesao'], 2, ',', '.') : '—') ?></td>
                        <td><?= e($preco['taxa_administrativa'] !== null ? 'R$ ' . number_format((float) $preco['taxa_administrativa'], 2, ',', '.') : '—') ?></td>
                        <td><span class="badge <?= $preco['status'] === 'ATIVO' ? 'badge--success' : 'badge--warning' ?>"><?= e($preco['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($precos === []): ?>
                    <tr><td colspan="7" class="text-muted">Nenhum preço cadastrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card">
    <header class="card__header">
        <div>
            <h3>Novo preço</h3>
            <p class="text-muted">Crie uma vigência de preço para uma unidade.</p>
        </div>
    </header>

    <form method="post" action="/admin/planos/<?= e((string) $plano['id']) ?>/precos" class="form">
        <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">

        <div class="form-grid">
            <?php if ($currentUnidadeId === null): ?>
                <div class="form__field">
                    <label for="unidade_id">Unidade</label>
                    <select class="input <?= $err('unidade_id') ? 'is-invalid' : '' ?>" id="unidade_id" name="unidade_id" required>
                        <option value="">Selecione</option>
                        <?php foreach ($unidades as $unidade): ?>
                            <option value="<?= e((string) $unidade['id']) ?>" <?= $val('unidade_id') === (string) $unidade['id'] ? 'selected' : '' ?>><?= e($unidade['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($err('unidade_id')): ?><p class="form__error"><?= e($err('unidade_id')) ?></p><?php endif; ?>
                </div>
            <?php else: ?>
                <input type="hidden" name="unidade_id" value="<?= e((string) $currentUnidadeId) ?>">
            <?php endif; ?>

            <div class="form__field">
                <label for="vigencia_inicio">Vigência inicial</label>
                <input class="input <?= $err('vigencia_inicio') ? 'is-invalid' : '' ?>" type="date" id="vigencia_inicio" name="vigencia_inicio" value="<?= e($val('vigencia_inicio')) ?>" required>
                <?php if ($err('vigencia_inicio')): ?><p class="form__error"><?= e($err('vigencia_inicio')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="vigencia_fim">Vigência final</label>
                <input class="input <?= $err('vigencia_fim') ? 'is-invalid' : '' ?>" type="date" id="vigencia_fim" name="vigencia_fim" value="<?= e($val('vigencia_fim')) ?>">
                <?php if ($err('vigencia_fim')): ?><p class="form__error"><?= e($err('vigencia_fim')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="valor_matricula">Valor de matrícula</label>
                <input class="input <?= $err('valor_matricula') ? 'is-invalid' : '' ?>" type="number" min="0" step="0.01" id="valor_matricula" name="valor_matricula" value="<?= e($val('valor_matricula', '0')) ?>" required>
                <?php if ($err('valor_matricula')): ?><p class="form__error"><?= e($err('valor_matricula')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="valor_mensal">Valor mensal</label>
                <input class="input <?= $err('valor_mensal') ? 'is-invalid' : '' ?>" type="number" min="0" step="0.01" id="valor_mensal" name="valor_mensal" value="<?= e($val('valor_mensal', '0')) ?>" required>
                <?php if ($err('valor_mensal')): ?><p class="form__error"><?= e($err('valor_mensal')) ?></p><?php endif; ?>
            </div>
            <div class="form__field">
                <label for="valor_adesao">Valor de adesão</label>
                <input class="input" type="number" min="0" step="0.01" id="valor_adesao" name="valor_adesao" value="<?= e($val('valor_adesao')) ?>">
            </div>
            <div class="form__field">
                <label for="taxa_administrativa">Taxa administrativa</label>
                <input class="input" type="number" min="0" step="0.01" id="taxa_administrativa" name="taxa_administrativa" value="<?= e($val('taxa_administrativa')) ?>">
            </div>
            <div class="form__field">
                <label for="status">Status</label>
                <select class="input" id="status" name="status">
                    <?php foreach (['ATIVO', 'INATIVO'] as $status): ?>
                        <option value="<?= e($status) ?>" <?= $val('status', 'ATIVO') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Salvar preço</button>
        </div>
    </form>
</section>

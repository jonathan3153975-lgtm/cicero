<?php $current = is_array($current ?? null) ? $current : null; ?>
<section class="page-heading">
    <div>
        <h2>Selecionar contexto</h2>
        <p class="text-muted">A rede e a unidade ativas definem o escopo das consultas e permissões.</p>
    </div>
</section>

<?php if (($contexts ?? []) === []): ?>
    <section class="card">
        <h3>Nenhum contexto disponível</h3>
        <p class="text-muted">Este usuário não possui vínculo ativo com redes ou unidades.</p>
    </section>
<?php else: ?>
    <section class="card-grid" aria-label="Contextos disponíveis">
        <?php foreach ($contexts as $item): ?>
            <?php
            $isCurrent = $current !== null
                && ($current['rede_id'] ?? null) === ($item['rede_id'] ?? null)
                && ($current['unidade_id'] ?? null) === ($item['unidade_id'] ?? null)
                && ($current['regiao_id'] ?? null) === ($item['regiao_id'] ?? null);
            ?>
            <form method="post" action="/contexto" class="card context-card">
                <input type="hidden" name="<?= e($csrfName) ?>" value="<?= e($csrfToken) ?>">
                <input type="hidden" name="rede_id" value="<?= e($item['rede_id'] ?? '') ?>">
                <input type="hidden" name="unidade_id" value="<?= e($item['unidade_id'] ?? '') ?>">
                <input type="hidden" name="regiao_id" value="<?= e($item['regiao_id'] ?? '') ?>">

                <span class="badge <?= $isCurrent ? 'badge--accent' : '' ?>">
                    <?= e($item['tipo'] ?? 'CONTEXTO') ?>
                </span>
                <h3><?= e($item['label'] ?? 'Contexto') ?></h3>
                <p class="text-muted">
                    <?= e($item['rede_nome'] ?? 'Plataforma') ?>
                    <?php if (($item['unidade_nome'] ?? null) !== null): ?>
                        · <?= e($item['unidade_nome']) ?>
                    <?php endif; ?>
                    <?php if (($item['regiao_nome'] ?? null) !== null): ?>
                        · <?= e($item['regiao_nome']) ?>
                    <?php endif; ?>
                </p>

                <button class="btn <?= $isCurrent ? 'btn-ghost' : 'btn-primary' ?> btn-block" type="submit">
                    <?= $isCurrent ? 'Contexto atual' : 'Selecionar contexto' ?>
                </button>
            </form>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

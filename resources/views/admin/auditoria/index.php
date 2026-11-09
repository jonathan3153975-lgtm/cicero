<section class="page-heading">
    <div>
        <h2>Auditoria</h2>
        <p class="text-muted">Últimas ações administrativas registradas.</p>
    </div>
    <span class="badge badge--accent">Contexto: <?= e($contextLabel) ?></span>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Data</th>
                    <th scope="col">Usuário</th>
                    <th scope="col">Ação</th>
                    <th scope="col">Entidade</th>
                    <th scope="col">Escopo</th>
                    <th scope="col">IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $registro): ?>
                    <tr>
                        <td><?= e($registro['created_at']) ?></td>
                        <td>
                            <?= e($registro['usuario_nome'] ?? 'Sistema') ?><br>
                            <small class="text-muted"><?= e($registro['usuario_email'] ?? '—') ?></small>
                        </td>
                        <td><code><?= e($registro['acao']) ?></code></td>
                        <td>
                            <?= e($registro['entidade']) ?>
                            <?php if ($registro['entidade_id'] !== null): ?>
                                #<?= e((string) $registro['entidade_id']) ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= e($registro['unidade_nome'] ?? $registro['rede_nome'] ?? 'Plataforma') ?>
                        </td>
                        <td><?= e($registro['ip'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($registros === []): ?>
                    <tr><td colspan="6" class="text-muted">Nenhum registro de auditoria.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

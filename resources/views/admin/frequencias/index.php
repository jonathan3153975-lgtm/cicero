<section class="page-heading">
    <div>
        <h2>Frequência</h2>
        <p class="text-muted">Presenças registradas por aluno e turma.</p>
    </div>
    <?php if (isset($can) && $can('frequencia.registrar')): ?>
        <a class="btn btn-primary" href="/admin/frequencias/registrar">Registrar frequência</a>
    <?php endif; ?>
</section>

<section class="card">
    <form method="get" action="/admin/frequencias" class="form-grid">
        <div class="form__field">
            <label for="busca">Buscar</label>
            <input class="input" id="busca" name="busca" value="<?= e($search ?? '') ?>" placeholder="Nome do aluno">
        </div>
        <div class="form__actions">
            <button class="btn btn-primary" type="submit">Buscar</button>
            <a class="btn btn-ghost" href="/admin/frequencias">Limpar</a>
        </div>
    </form>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Data</th>
                    <th scope="col">Aluno</th>
                    <th scope="col">Turma</th>
                    <th scope="col">Presença</th>
                    <th scope="col">Origem</th>
                    <th scope="col">Unidade</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($frequencias as $frequencia): ?>
                    <tr>
                        <td><?= e($frequencia['data']) ?></td>
                        <td><strong><?= e($frequencia['aluno_nome']) ?></strong></td>
                        <td><?= e($frequencia['turma_nome']) ?></td>
                        <td><span class="badge <?= $frequencia['presenca'] ? 'badge--success' : 'badge--danger' ?>"><?= $frequencia['presenca'] ? 'Presente' : 'Ausente' ?></span></td>
                        <td><?= e($frequencia['origem']) ?></td>
                        <td><?= e($frequencia['unidade_nome']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($frequencias === []): ?>
                    <tr><td colspan="6" class="text-muted">Nenhuma frequência registrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

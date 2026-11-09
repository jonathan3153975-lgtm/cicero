<section class="page-heading">
    <div>
        <h2>Eventos de acesso</h2>
        <p class="text-muted">Entradas, saídas e tentativas registradas pelas catracas.</p>
    </div>
    <a class="btn btn-ghost btn-sm" href="/admin/dispositivos">Dispositivos</a>
</section>

<section class="card">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Data/hora</th>
                    <th scope="col">Pessoa</th>
                    <th scope="col">Dispositivo</th>
                    <th scope="col">Unidade</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Autorizado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($eventos as $evento): ?>
                    <tr>
                        <td><?= e($evento['ocorrido_em']) ?></td>
                        <td><strong><?= e($evento['pessoa_nome']) ?></strong></td>
                        <td><?= e($evento['dispositivo_nome']) ?></td>
                        <td><?= e($evento['unidade_nome']) ?></td>
                        <td><?= e($evento['tipo']) ?></td>
                        <td><span class="badge <?= $evento['autorizado'] ? 'badge--success' : 'badge--danger' ?>"><?= $evento['autorizado'] ? 'Sim' : 'Não' ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($eventos === []): ?>
                    <tr><td colspan="6" class="text-muted">Nenhum evento de acesso registrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

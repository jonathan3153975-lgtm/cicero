<?php

declare(strict_types=1);

use App\Controllers\Admin\AdminDashboardController;
use App\Controllers\Admin\AlunoController;
use App\Controllers\Admin\AuditoriaController;
use App\Controllers\Admin\CobrancaController;
use App\Controllers\Admin\ConciliacaoController;
use App\Controllers\Admin\ContaBancariaController;
use App\Controllers\Admin\ContaPagarController;
use App\Controllers\Admin\ContratoController;
use App\Controllers\Admin\DispositivoAcessoController;
use App\Controllers\Admin\EventoAcessoController;
use App\Controllers\Admin\FrequenciaController;
use App\Controllers\Admin\MatriculaController;
use App\Controllers\Admin\ModalidadeController;
use App\Controllers\Admin\PagamentoController;
use App\Controllers\Admin\PerfilController;
use App\Controllers\Admin\PermissaoController;
use App\Controllers\Admin\PessoaController;
use App\Controllers\Admin\PlanoController;
use App\Controllers\Admin\PlanoRedeController;
use App\Controllers\Admin\PlanoPrecoController;
use App\Controllers\Admin\RedeController;
use App\Controllers\Admin\RegiaoController;
use App\Controllers\Admin\ReservaController;
use App\Controllers\Admin\TurmaController;
use App\Controllers\Admin\UnidadeController;
use App\Controllers\Admin\UsuarioController;
use App\Controllers\Admin\VinculoController;
use App\Controllers\Web\AuthController;
use App\Controllers\Web\ContextController;
use App\Controllers\Web\DashboardController;
use App\Controllers\Web\HomeController;
use App\Controllers\Web\SettingsController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Middleware\ScopeMiddleware;

$admin = static fn (string $permission): array => [
    AuthMiddleware::class,
    ScopeMiddleware::class,
    [PermissionMiddleware::class, [$permission]],
];

return [
    ['GET', '/', [HomeController::class, 'index']],

    ['GET', '/login', [AuthController::class, 'showLogin'], [GuestMiddleware::class]],
    ['POST', '/login', [AuthController::class, 'login'], [GuestMiddleware::class]],
    ['POST', '/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]],

    ['GET', '/contexto', [ContextController::class, 'index'], [AuthMiddleware::class]],
    ['POST', '/contexto', [ContextController::class, 'switch'], [AuthMiddleware::class]],

    ['GET', '/dashboard', [DashboardController::class, 'index'], $admin('dashboard.visualizar')],
    ['GET', '/configuracoes', [SettingsController::class, 'index'], $admin('configuracao.visualizar')],

    ['GET', '/admin', [AdminDashboardController::class, 'index'], $admin('usuario.visualizar')],

    ['GET', '/admin/planos-rede', [PlanoRedeController::class, 'index'], $admin('plano_rede.visualizar')],
    ['GET', '/admin/planos-rede/novo', [PlanoRedeController::class, 'create'], $admin('plano_rede.criar')],
    ['POST', '/admin/planos-rede', [PlanoRedeController::class, 'store'], $admin('plano_rede.criar')],
    ['GET', '/admin/planos-rede/{id}/editar', [PlanoRedeController::class, 'edit'], $admin('plano_rede.editar')],
    ['POST', '/admin/planos-rede/{id}', [PlanoRedeController::class, 'update'], $admin('plano_rede.editar')],
    ['GET', '/admin/redes', [RedeController::class, 'index'], $admin('rede.visualizar')],
    ['GET', '/admin/redes/novo', [RedeController::class, 'create'], $admin('rede.criar')],
    ['POST', '/admin/redes', [RedeController::class, 'store'], $admin('rede.criar')],
    ['GET', '/admin/redes/{id}/editar', [RedeController::class, 'edit'], $admin('rede.editar')],
    ['POST', '/admin/redes/{id}', [RedeController::class, 'update'], $admin('rede.editar')],

    ['GET', '/admin/regioes', [RegiaoController::class, 'index'], $admin('regiao.visualizar')],
    ['GET', '/admin/regioes/novo', [RegiaoController::class, 'create'], $admin('regiao.criar')],
    ['POST', '/admin/regioes', [RegiaoController::class, 'store'], $admin('regiao.criar')],
    ['GET', '/admin/regioes/{id}/editar', [RegiaoController::class, 'edit'], $admin('regiao.editar')],
    ['POST', '/admin/regioes/{id}', [RegiaoController::class, 'update'], $admin('regiao.editar')],

    ['GET', '/admin/unidades', [UnidadeController::class, 'index'], $admin('unidade.visualizar')],
    ['GET', '/admin/unidades/novo', [UnidadeController::class, 'create'], $admin('unidade.criar')],
    ['POST', '/admin/unidades', [UnidadeController::class, 'store'], $admin('unidade.criar')],
    ['GET', '/admin/unidades/{id}/editar', [UnidadeController::class, 'edit'], $admin('unidade.editar')],
    ['POST', '/admin/unidades/{id}', [UnidadeController::class, 'update'], $admin('unidade.editar')],

    ['GET', '/admin/usuarios', [UsuarioController::class, 'index'], $admin('usuario.visualizar')],
    ['GET', '/admin/usuarios/novo', [UsuarioController::class, 'create'], $admin('usuario.criar')],
    ['POST', '/admin/usuarios', [UsuarioController::class, 'store'], $admin('usuario.criar')],
    ['GET', '/admin/usuarios/buscar-pessoas', [UsuarioController::class, 'searchPessoas'], $admin('usuario.visualizar')],
    ['GET', '/admin/usuarios/{id}/editar', [UsuarioController::class, 'edit'], $admin('usuario.editar')],
    ['POST', '/admin/usuarios/{id}', [UsuarioController::class, 'update'], $admin('usuario.editar')],


    ['GET', '/admin/pessoas', [PessoaController::class, 'index'], $admin('pessoa.visualizar')],
    ['GET', '/admin/pessoas/novo', [PessoaController::class, 'create'], $admin('pessoa.criar')],
    ['POST', '/admin/pessoas', [PessoaController::class, 'store'], $admin('pessoa.criar')],
    ['GET', '/admin/pessoas/{id}/editar', [PessoaController::class, 'edit'], $admin('pessoa.editar')],
    ['POST', '/admin/pessoas/{id}', [PessoaController::class, 'update'], $admin('pessoa.editar')],

    ['GET', '/admin/alunos', [AlunoController::class, 'index'], $admin('aluno.visualizar')],
    ['GET', '/admin/alunos/novo', [AlunoController::class, 'create'], $admin('aluno.criar')],
    ['POST', '/admin/alunos', [AlunoController::class, 'store'], $admin('aluno.criar')],
    ['GET', '/admin/alunos/{id}/editar', [AlunoController::class, 'edit'], $admin('aluno.editar')],
    ['POST', '/admin/alunos/{id}', [AlunoController::class, 'update'], $admin('aluno.editar')],
    ['POST', '/admin/alunos/{id}/responsaveis', [AlunoController::class, 'addResponsavel'], $admin('responsavel.criar')],
    ['POST', '/admin/alunos/{id}/responsaveis/{responsavelId}/remover', [AlunoController::class, 'removeResponsavel'], $admin('responsavel.editar')],


    ['GET', '/admin/modalidades', [ModalidadeController::class, 'index'], $admin('modalidade.visualizar')],
    ['GET', '/admin/modalidades/nova', [ModalidadeController::class, 'create'], $admin('modalidade.criar')],
    ['POST', '/admin/modalidades', [ModalidadeController::class, 'store'], $admin('modalidade.criar')],
    ['GET', '/admin/modalidades/{id}/editar', [ModalidadeController::class, 'edit'], $admin('modalidade.editar')],
    ['POST', '/admin/modalidades/{id}', [ModalidadeController::class, 'update'], $admin('modalidade.editar')],
    ['GET', '/admin/planos', [PlanoController::class, 'index'], $admin('plano.visualizar')],
    ['GET', '/admin/planos/novo', [PlanoController::class, 'create'], $admin('plano.criar')],
    ['POST', '/admin/planos', [PlanoController::class, 'store'], $admin('plano.criar')],
    ['GET', '/admin/planos/{id}/editar', [PlanoController::class, 'edit'], $admin('plano.editar')],
    ['POST', '/admin/planos/{id}', [PlanoController::class, 'update'], $admin('plano.editar')],
    ['GET', '/admin/planos/{id}/precos', [PlanoPrecoController::class, 'index'], $admin('plano.editar')],
    ['POST', '/admin/planos/{id}/precos', [PlanoPrecoController::class, 'store'], $admin('plano.editar')],

    ['GET', '/admin/matriculas', [MatriculaController::class, 'index'], $admin('matricula.visualizar')],
    ['GET', '/admin/matriculas/novo', [MatriculaController::class, 'create'], $admin('matricula.criar')],
    ['POST', '/admin/matriculas', [MatriculaController::class, 'store'], $admin('matricula.criar')],
    ['GET', '/admin/matriculas/{id}/editar', [MatriculaController::class, 'edit'], $admin('matricula.editar')],
    ['POST', '/admin/matriculas/{id}', [MatriculaController::class, 'update'], $admin('matricula.editar')],

    ['GET', '/admin/contratos', [ContratoController::class, 'index'], $admin('contrato.visualizar')],
    ['GET', '/admin/contratos/{id}/editar', [ContratoController::class, 'edit'], $admin('contrato.editar')],
    ['POST', '/admin/contratos/{id}', [ContratoController::class, 'update'], $admin('contrato.editar')],


    ['GET', '/admin/turmas', [TurmaController::class, 'index'], $admin('turma.visualizar')],
    ['GET', '/admin/turmas/novo', [TurmaController::class, 'create'], $admin('turma.criar')],
    ['POST', '/admin/turmas', [TurmaController::class, 'store'], $admin('turma.criar')],
    ['GET', '/admin/turmas/{id}/editar', [TurmaController::class, 'edit'], $admin('turma.editar')],
    ['POST', '/admin/turmas/{id}', [TurmaController::class, 'update'], $admin('turma.editar')],
    ['GET', '/admin/turmas/{id}/horarios', [TurmaController::class, 'horarios'], $admin('turma.editar')],
    ['POST', '/admin/turmas/{id}/horarios', [TurmaController::class, 'addHorario'], $admin('turma.editar')],
    ['POST', '/admin/turmas/{id}/horarios/{horarioId}/remover', [TurmaController::class, 'removeHorario'], $admin('turma.editar')],

    ['GET', '/admin/frequencias', [FrequenciaController::class, 'index'], $admin('frequencia.visualizar')],
    ['GET', '/admin/frequencias/registrar', [FrequenciaController::class, 'create'], $admin('frequencia.registrar')],
    ['POST', '/admin/frequencias/registrar', [FrequenciaController::class, 'store'], $admin('frequencia.registrar')],

    ['GET', '/admin/reservas', [ReservaController::class, 'index'], $admin('reserva.visualizar')],
    ['GET', '/admin/reservas/nova', [ReservaController::class, 'create'], $admin('reserva.criar')],
    ['POST', '/admin/reservas', [ReservaController::class, 'store'], $admin('reserva.criar')],
    ['POST', '/admin/reservas/{id}/cancelar', [ReservaController::class, 'cancel'], $admin('reserva.cancelar')],

    ['GET', '/admin/dispositivos', [DispositivoAcessoController::class, 'index'], $admin('dispositivo.visualizar')],
    ['GET', '/admin/dispositivos/novo', [DispositivoAcessoController::class, 'create'], $admin('dispositivo.gerenciar')],
    ['POST', '/admin/dispositivos', [DispositivoAcessoController::class, 'store'], $admin('dispositivo.gerenciar')],
    ['GET', '/admin/dispositivos/{id}/editar', [DispositivoAcessoController::class, 'edit'], $admin('dispositivo.gerenciar')],
    ['POST', '/admin/dispositivos/{id}', [DispositivoAcessoController::class, 'update'], $admin('dispositivo.gerenciar')],

    ['GET', '/admin/acessos', [EventoAcessoController::class, 'index'], $admin('evento_acesso.visualizar')],


    ['GET', '/admin/cobrancas', [CobrancaController::class, 'index'], $admin('financeiro.visualizar')],
    ['GET', '/admin/cobrancas/nova', [CobrancaController::class, 'create'], $admin('financeiro.editar')],
    ['POST', '/admin/cobrancas', [CobrancaController::class, 'store'], $admin('financeiro.editar')],
    ['GET', '/admin/cobrancas/{id}', [CobrancaController::class, 'show'], $admin('financeiro.visualizar')],

    ['GET', '/admin/pagamentos', [PagamentoController::class, 'index'], $admin('financeiro.visualizar')],
    ['GET', '/admin/pagamentos/novo', [PagamentoController::class, 'create'], $admin('financeiro.editar')],
    ['POST', '/admin/pagamentos', [PagamentoController::class, 'store'], $admin('financeiro.editar')],
    ['POST', '/admin/pagamentos/{id}/estornar', [PagamentoController::class, 'reverse'], $admin('financeiro.editar')],

    ['GET', '/admin/contas-pagar', [ContaPagarController::class, 'index'], $admin('financeiro.visualizar')],
    ['GET', '/admin/contas-pagar/nova', [ContaPagarController::class, 'create'], $admin('financeiro.editar')],
    ['POST', '/admin/contas-pagar', [ContaPagarController::class, 'store'], $admin('financeiro.editar')],
    ['POST', '/admin/contas-pagar/{id}/pagar', [ContaPagarController::class, 'pay'], $admin('financeiro.editar')],
    ['POST', '/admin/contas-pagar/{id}/cancelar', [ContaPagarController::class, 'cancel'], $admin('financeiro.editar')],

    ['GET', '/admin/contas-bancarias', [ContaBancariaController::class, 'index'], $admin('financeiro.visualizar')],
    ['POST', '/admin/contas-bancarias', [ContaBancariaController::class, 'store'], $admin('financeiro.editar')],

    ['GET', '/admin/conciliacoes', [ConciliacaoController::class, 'index'], $admin('financeiro.visualizar')],
    ['GET', '/admin/conciliacoes/nova', [ConciliacaoController::class, 'create'], $admin('financeiro.editar')],
    ['POST', '/admin/conciliacoes', [ConciliacaoController::class, 'store'], $admin('financeiro.editar')],
    ['GET', '/admin/conciliacoes/{id}', [ConciliacaoController::class, 'show'], $admin('financeiro.visualizar')],
    ['POST', '/admin/conciliacoes/{id}/importar', [ConciliacaoController::class, 'import'], $admin('financeiro.editar')],
    ['POST', '/admin/conciliacoes/{id}/fechar', [ConciliacaoController::class, 'close'], $admin('financeiro.editar')],
    ['GET', '/admin/perfis', [PerfilController::class, 'index'], $admin('perfil.visualizar')],
    ['GET', '/admin/perfis/novo', [PerfilController::class, 'create'], $admin('perfil.criar')],
    ['POST', '/admin/perfis', [PerfilController::class, 'store'], $admin('perfil.criar')],
    ['GET', '/admin/perfis/{id}/editar', [PerfilController::class, 'edit'], $admin('perfil.editar')],
    ['POST', '/admin/perfis/{id}', [PerfilController::class, 'update'], $admin('perfil.editar')],

    ['GET', '/admin/permissoes', [PermissaoController::class, 'index'], $admin('permissao.visualizar')],
    ['GET', '/admin/vinculos', [VinculoController::class, 'index'], $admin('vinculo.visualizar')],
    ['GET', '/admin/vinculos/novo', [VinculoController::class, 'create'], $admin('vinculo.criar')],
    ['POST', '/admin/vinculos', [VinculoController::class, 'store'], $admin('vinculo.criar')],
    ['POST', '/admin/vinculos/{id}/revogar', [VinculoController::class, 'revoke'], $admin('vinculo.editar')],

    ['GET', '/admin/auditoria', [AuditoriaController::class, 'index'], $admin('auditoria.visualizar')],
];

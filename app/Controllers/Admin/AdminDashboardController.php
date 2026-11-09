<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Repositories\CobrancaRepository;
use App\Repositories\ContaBancariaRepository;
use App\Repositories\ContaPagarRepository;
use App\Repositories\PermissaoRepository;
use App\Repositories\RedeRepository;
use App\Repositories\RegiaoRepository;
use App\Repositories\UnidadeRepository;
use App\Repositories\UsuarioRepository;
use App\Services\AcessoService;
use App\Services\AlunoService;
use App\Services\AuditService;
use App\Services\ContratoService;
use App\Services\FrequenciaService;
use App\Services\MatriculaService;
use App\Services\PerfilService;
use App\Services\PessoaService;
use App\Services\PlanoService;
use App\Services\ReservaService;
use App\Services\TurmaService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

final class AdminDashboardController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly RedeRepository $redes,
        private readonly RegiaoRepository $regioes,
        private readonly UnidadeRepository $unidades,
        private readonly UsuarioRepository $usuarios,
        private readonly PermissaoRepository $permissoes,
        private readonly PerfilService $perfilService,
        private readonly PessoaService $pessoaService,
        private readonly AlunoService $alunoService,
        private readonly PlanoService $planoService,
        private readonly MatriculaService $matriculaService,
        private readonly ContratoService $contratoService,
        private readonly TurmaService $turmaService,
        private readonly FrequenciaService $frequenciaService,
        private readonly ReservaService $reservaService,
        private readonly AcessoService $acessoService,
        private readonly CobrancaRepository $cobrancas,
        private readonly ContaPagarRepository $contasPagar,
        private readonly ContaBancariaRepository $contasBancarias
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        $redeId = $this->contextRedeId();
        $unidadeId = $this->contextUnidadeId();
        $regiaoId = $this->contextRegiaoId();

        return $this->render('admin.index', [
            'title' => 'Administração',
            'counts' => [
                'redes' => $this->isPlatformContext() ? count($this->redes->all()) : ($redeId !== null ? 1 : 0),
                'regioes' => $regiaoId !== null
                    ? 1
                    : ($redeId !== null ? count($this->regioes->forRede($redeId)) : count($this->regioes->all())),
                'unidades' => count($this->unidades->forScope($redeId, $unidadeId, $regiaoId)),
                'usuarios' => count($this->usuarios->forScope($redeId, $unidadeId, $regiaoId)),
                'pessoas' => count($this->pessoaService->forScope($redeId, $unidadeId, $regiaoId, $this->actorId())),
                'alunos' => count($this->alunoService->forScope($redeId, $unidadeId, $regiaoId)),
                'planos' => count($this->planoService->allForScope($redeId, $this->isPlatformContext())),
                'matriculas' => count($this->matriculaService->forScope($redeId, $unidadeId, $regiaoId)),
                'contratos' => count($this->contratoService->forScope($redeId, $unidadeId, $regiaoId)),
                'turmas' => count($this->turmaService->forScope($redeId, $unidadeId, $regiaoId)),
                'frequencias' => count($this->frequenciaService->forScope($redeId, $unidadeId, $regiaoId)),
                'reservas' => count($this->reservaService->forScope($redeId, $unidadeId, $regiaoId)),
                'dispositivos' => count($this->acessoService->devicesForScope($redeId, $unidadeId, $regiaoId)),
                'perfis' => count($this->perfilService->visibleProfiles($this->actorId(), $redeId, $unidadeId, $regiaoId)),
                'cobrancas' => $this->cobrancas->countForScope($redeId, $unidadeId, $regiaoId),
                'cobrancas_abertas' => $this->cobrancas->countForScope($redeId, $unidadeId, $regiaoId, true),
                'contas_pagar' => $this->contasPagar->countForScope($redeId, $unidadeId, $regiaoId),
                'contas_bancarias' => $this->contasBancarias->countForScope($redeId, $unidadeId, $regiaoId),
                'permissoes' => count($this->permissoes->all()),
            ],
            'contextLabel' => $this->contextLabel(),
        ]);
    }
}

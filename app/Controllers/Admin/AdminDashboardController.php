<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Repositories\PermissaoRepository;
use App\Repositories\RedeRepository;
use App\Repositories\RegiaoRepository;
use App\Repositories\UnidadeRepository;
use App\Repositories\UsuarioRepository;
use App\Services\AuditService;
use App\Services\PerfilService;
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
        private readonly PerfilService $perfilService
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
                'perfis' => count($this->perfilService->visibleProfiles($this->actorId(), $redeId, $unidadeId, $regiaoId)),
                'permissoes' => count($this->permissoes->all()),
            ],
            'contextLabel' => $this->contextLabel(),
        ]);
    }
}

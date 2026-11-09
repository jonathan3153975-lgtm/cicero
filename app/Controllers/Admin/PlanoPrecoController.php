<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\PlanoRepository;
use App\Repositories\UnidadeRepository;
use App\Services\AuditService;
use App\Services\PlanoPrecoService;
use App\Services\PlanoService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

final class PlanoPrecoController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly PlanoRepository $planos,
        private readonly PlanoService $planoService,
        private readonly PlanoPrecoService $service,
        private readonly UnidadeRepository $unidades
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        $plano = $this->findPlano((int) $request->routeParam('id'));

        return $this->render('admin.planos.precos', array_merge($this->formState(), [
            'title' => 'Preços do plano',
            'plano' => $plano,
            'precos' => $this->service->forPlanoForScope(
                (int) $plano['id'],
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            ),
            'unidades' => $this->unidadeOptions(),
            'currentUnidadeId' => $this->contextUnidadeId(),
        ]));
    }

    public function store(Request $request): Response
    {
        $planoId = (int) $request->routeParam('id');
        $plano = $this->findPlano($planoId);
        $data = $request->all();

        try {
            $id = $this->service->create(
                $this->actorId(),
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId(),
                $planoId,
                $data
            );
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/planos/' . $planoId . '/precos', $exception->getErrors(), $data);
        }

        $unidadeId = $this->contextUnidadeId() ?? (int) ($data['unidade_id'] ?? 0);
        $this->audit(
            $request,
            'plano.preco.criar',
            'plano_unidade_preco',
            $id,
            null,
            $data,
            $plano['rede_id'] !== null ? (int) $plano['rede_id'] : null,
            $unidadeId > 0 ? $unidadeId : null
        );
        $this->flashSuccess('Preço cadastrado com sucesso.');

        return $this->redirect('/admin/planos/' . $planoId . '/precos');
    }

    private function findPlano(int $id): array
    {
        $plano = $this->planos->findById($id);

        if ($plano === null) {
            throw new NotFoundException('Plano não encontrado.');
        }

        $this->planoService->assertCanAccess(
            $id,
            $this->contextRedeId(),
            $this->contextUnidadeId(),
            $this->contextRegiaoId()
        );

        return $plano;
    }

    private function unidadeOptions(): array
    {
        $unidadeId = $this->contextUnidadeId();

        if ($unidadeId !== null) {
            return array_values(array_filter([$this->unidades->findById($unidadeId)]));
        }

        $regiaoId = $this->contextRegiaoId();

        if ($regiaoId !== null) {
            return $this->unidades->forRegiao($regiaoId);
        }

        $redeId = $this->contextRedeId();

        return $redeId !== null ? $this->unidades->forRede($redeId) : $this->unidades->all();
    }
}

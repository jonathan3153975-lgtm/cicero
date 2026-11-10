<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\RedeRepository;
use App\Repositories\RegiaoRepository;
use App\Services\AuditService;
use App\Services\RegiaoService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

final class RegiaoController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly RegiaoRepository $regioes,
        private readonly RedeRepository $redes,
        private readonly RegiaoService $service
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        $redeId = $this->contextRedeId();
        $unidadeId = $this->contextUnidadeId();
        $regiaoId = $this->contextRegiaoId();

        if ($regiaoId !== null) {
            $regioes = array_values(array_filter([$this->regioes->findById($regiaoId)]));
        } elseif ($unidadeId !== null) {
            $regioes = $this->regioes->forUnidade($unidadeId);
        } elseif ($redeId !== null) {
            $regioes = $this->regioes->forRede($redeId);
        } else {
            $regioes = $this->regioes->all();
        }

        return $this->render('admin.regioes.index', [
            'title' => 'Regiões',
            'regioes' => $regioes,
            'isPlatform' => $this->isPlatformContext(),
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->render('admin.regioes.form', array_merge($this->formState(), [
            'title' => 'Nova região',
            'item' => null,
            'redes' => $this->redeOptions(),
            'isPlatform' => $this->isPlatformContext(),
            'currentRedeId' => $this->contextRedeId(),
        ]));
    }

    public function store(Request $request): Response
    {
        $redeId = $this->targetRedeId($request->input('rede_id'));
        $data = $request->all();

        if ($redeId <= 0) {
            return $this->backWithErrors('/admin/regioes/novo', [
                'rede_id' => ['Selecione a rede.'],
            ], $data);
        }

        try {
            $id = $this->service->create($redeId, $data);
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/regioes/novo', $exception->getErrors(), $data);
        }

        $this->auditExplicit($request, 'regiao.criar', 'regiao', $id, null, $data, $redeId, null);
        $this->flashSuccess('Região criada com sucesso.');

        return $this->redirect('/admin/regioes');
    }

    public function edit(Request $request): Response
    {
        $item = $this->findScoped((int) $request->routeParam('id'));

        return $this->render('admin.regioes.form', array_merge($this->formState(), [
            'title' => 'Editar região',
            'item' => $item,
            'redes' => $this->redeOptions(),
            'isPlatform' => $this->isPlatformContext(),
            'currentRedeId' => $this->contextRedeId(),
        ]));
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->routeParam('id');
        $before = $this->findScoped($id);
        $data = $request->all();
        $redeId = (int) $before['rede_id'];

        if ($redeId <= 0) {
            return $this->backWithErrors('/admin/regioes/' . $id . '/editar', [
                'rede_id' => ['Selecione a rede.'],
            ], $data);
        }

        try {
            $this->service->update($id, $redeId, $data);
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/regioes/' . $id . '/editar', $exception->getErrors(), $data);
        }

        $this->auditExplicit($request, 'regiao.editar', 'regiao', $id, $before, $data, $redeId, null);
        $this->flashSuccess('Região atualizada com sucesso.');

        return $this->redirect('/admin/regioes');
    }

    private function redeOptions(): array
    {
        return $this->isPlatformContext() ? $this->redes->allActive() : array_values(array_filter([
            $this->redes->findById((int) $this->contextRedeId()),
        ]));
    }

    private function targetRedeId(mixed $input): int
    {
        if (!$this->isPlatformContext()) {
            return (int) $this->contextRedeId();
        }

        return (int) $input;
    }

    private function findScoped(int $id): array
    {
        $item = $this->regioes->findById($id);

        if ($item === null) {
            throw new NotFoundException('Região não encontrada.');
        }

        if ($this->isPlatformContext()) {
            return $item;
        }

        $regiaoId = $this->contextRegiaoId();
        $unidadeId = $this->contextUnidadeId();

        if ($regiaoId !== null && (int) $item['id'] !== $regiaoId) {
            throw new ForbiddenException('Região fora do seu contexto.');
        }

        if ($unidadeId !== null) {
            $regionIds = array_map(
                static fn (array $region): int => (int) $region['id'],
                $this->regioes->forUnidade($unidadeId)
            );

            if (!in_array($id, $regionIds, true)) {
                throw new ForbiddenException('Região fora da sua unidade.');
            }
        }

        if (
            $regiaoId === null
            && $unidadeId === null
            && (int) $item['rede_id'] !== $this->contextRedeId()
        ) {
            throw new ForbiddenException('Região fora do seu contexto.');
        }

        return $item;
    }
}

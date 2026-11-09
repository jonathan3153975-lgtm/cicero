<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\RedeRepository;
use App\Repositories\RegiaoRepository;
use App\Repositories\UnidadeRepository;
use App\Services\AuditService;
use App\Services\UnidadeService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

final class UnidadeController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly UnidadeRepository $unidades,
        private readonly RedeRepository $redes,
        private readonly RegiaoRepository $regioes,
        private readonly UnidadeService $service
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        return $this->render('admin.unidades.index', [
            'title' => 'Unidades',
            'unidades' => $this->unidades->forScope($this->contextRedeId(), $this->contextUnidadeId(), $this->contextRegiaoId()),
            'isPlatform' => $this->isPlatformContext(),
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->render('admin.unidades.form', array_merge($this->formState(), [
            'title' => 'Nova unidade',
            'item' => null,
            'redes' => $this->redeOptions(),
            'isPlatform' => $this->isPlatformContext(),
            'currentRedeId' => $this->contextRedeId(),
        ]));
    }

    public function store(Request $request): Response
    {
        $data = $request->all();
        $data['rede_id'] = $this->targetRedeId($request->input('rede_id'));

        if ((int) $data['rede_id'] <= 0) {
            return $this->backWithErrors('/admin/unidades/novo', [
                'rede_id' => ['Selecione a rede.'],
            ], $data);
        }

        try {
            $id = $this->service->create($data);

            if ($this->contextRegiaoId() !== null) {
                $this->regioes->linkUnidade((int) $this->contextRegiaoId(), $id);
            }
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/unidades/novo', $exception->getErrors(), $data);
        }

        $this->auditExplicit($request, 'unidade.criar', 'unidade', $id, null, $data, (int) $data['rede_id'], null);
        $this->flashSuccess('Unidade criada com sucesso.');

        return $this->redirect('/admin/unidades');
    }

    public function edit(Request $request): Response
    {
        $item = $this->findScoped((int) $request->routeParam('id'));

        return $this->render('admin.unidades.form', array_merge($this->formState(), [
            'title' => 'Editar unidade',
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
        $data['rede_id'] = $this->targetRedeId($request->input('rede_id'));

        if ((int) $data['rede_id'] <= 0) {
            return $this->backWithErrors('/admin/unidades/' . $id . '/editar', [
                'rede_id' => ['Selecione a rede.'],
            ], $data);
        }

        try {
            $this->service->update($id, $data);
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/unidades/' . $id . '/editar', $exception->getErrors(), $data);
        }

        $this->audit($request, 'unidade.editar', 'unidade', $id, $before, $data, (int) $data['rede_id'], $id);
        $this->flashSuccess('Unidade atualizada com sucesso.');

        return $this->redirect('/admin/unidades');
    }

    private function redeOptions(): array
    {
        return $this->isPlatformContext() ? $this->redes->all() : array_values(array_filter([
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
        $item = $this->unidades->findById($id);

        if ($item === null) {
            throw new NotFoundException('Unidade não encontrada.');
        }

        $contextUnidade = $this->contextUnidadeId();
        $contextRede = $this->contextRedeId();
        $contextRegiao = $this->contextRegiaoId();

        if ($contextUnidade !== null && (int) $item['id'] !== $contextUnidade) {
            throw new ForbiddenException('Unidade fora do seu contexto.');
        }

        if ($contextUnidade === null && $contextRegiao !== null && !$this->unidades->isInRegiao($id, $contextRegiao)) {
            throw new ForbiddenException('Unidade fora da sua região.');
        }

        if (
            $contextUnidade === null
            && $contextRegiao === null
            && $contextRede !== null
            && (int) $item['rede_id'] !== $contextRede
        ) {
            throw new ForbiddenException('Unidade fora do seu contexto.');
        }

        return $item;
    }
}

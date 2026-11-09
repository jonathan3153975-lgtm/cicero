<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\DispositivoAcessoRepository;
use App\Repositories\UnidadeRepository;
use App\Services\AcessoService;
use App\Services\AuditService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

final class DispositivoAcessoController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly DispositivoAcessoRepository $dispositivos,
        private readonly AcessoService $service,
        private readonly UnidadeRepository $unidades
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        return $this->render('admin.dispositivos.index', [
            'title' => 'Dispositivos de acesso',
            'dispositivos' => $this->service->devicesForScope(
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            ),
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->render('admin.dispositivos.form', array_merge($this->formState(), [
            'title' => 'Novo dispositivo',
            'item' => null,
            'unidades' => $this->unidadeOptions(),
            'currentUnidadeId' => $this->contextUnidadeId(),
        ]));
    }

    public function store(Request $request): Response
    {
        $data = $request->all();

        try {
            $id = $this->service->createDevice(
                $this->actorId(),
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId(),
                $data
            );
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/dispositivos/novo', $exception->getErrors(), $data);
        }

        $device = $this->dispositivos->findById($id);
        $this->audit($request, 'dispositivo.criar', 'dispositivo_acesso', $id, null, $data, (int) $device['rede_id'], (int) $device['unidade_id']);
        $this->flashSuccess('Dispositivo criado com sucesso.');

        return $this->redirect('/admin/dispositivos');
    }

    public function edit(Request $request): Response
    {
        $item = $this->findScoped((int) $request->routeParam('id'));

        return $this->render('admin.dispositivos.form', array_merge($this->formState(), [
            'title' => 'Editar dispositivo',
            'item' => $item,
            'unidades' => $this->unidadeOptions(),
            'currentUnidadeId' => $this->contextUnidadeId(),
        ]));
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->routeParam('id');
        $before = $this->findScoped($id);
        $data = $request->all();

        try {
            $this->service->updateDevice(
                $id,
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId(),
                $data
            );
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/dispositivos/' . $id . '/editar', $exception->getErrors(), $data);
        }

        $this->audit($request, 'dispositivo.editar', 'dispositivo_acesso', $id, $before, $data, (int) $before['rede_id'], (int) $before['unidade_id']);
        $this->flashSuccess('Dispositivo atualizado com sucesso.');

        return $this->redirect('/admin/dispositivos');
    }

    private function findScoped(int $id): array
    {
        $device = $this->dispositivos->findById($id);

        if ($device === null) {
            throw new NotFoundException('Dispositivo não encontrado.');
        }

        $this->service->assertDeviceAccess(
            $id,
            $this->contextRedeId(),
            $this->contextUnidadeId(),
            $this->contextRegiaoId()
        );

        return $device;
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

<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\PlanoRedeRepository;
use App\Repositories\RedeRepository;
use App\Services\AuditService;
use App\Services\RedeService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

final class RedeController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly RedeRepository $redes,
        private readonly PlanoRedeRepository $planosRede,
        private readonly RedeService $service
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        $redes = $this->isPlatformContext()
            ? $this->redes->all()
            : array_values(array_filter([$this->redes->findById((int) $this->contextRedeId())]));

        return $this->render('admin.redes.index', [
            'title' => 'Redes',
            'redes' => $redes,
        ]);
    }

    public function create(Request $request): Response
    {
        if (!$this->isPlatformContext()) {
            throw new ForbiddenException('A criação de redes é restrita à plataforma.');
        }

        return $this->render('admin.redes.form', array_merge($this->formState(), [
            'title' => 'Nova rede',
            'item' => null,
            'planosRede' => $this->planosRede->all(),
        ]));
    }

    public function store(Request $request): Response
    {
        if (!$this->isPlatformContext()) {
            throw new ForbiddenException('A criação de redes é restrita à plataforma.');
        }

        $data = $request->all();

        try {
            $id = $this->service->create($data);
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/redes/novo', $exception->getErrors(), $data);
        }

        $this->audit($request, 'rede.criar', 'rede', $id, null, $data);
        $this->flashSuccess('Rede criada com sucesso.');

        return $this->redirect('/admin/redes');
    }

    public function edit(Request $request): Response
    {
        $item = $this->findScoped((int) $request->routeParam('id'));

        return $this->render('admin.redes.form', array_merge($this->formState(), [
            'title' => 'Editar rede',
            'item' => $item,
            'planosRede' => $this->planosRede->all(),
        ]));
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->routeParam('id');
        $before = $this->findScoped($id);
        $data = $request->all();

        try {
            $this->service->update($id, $data);
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/redes/' . $id . '/editar', $exception->getErrors(), $data);
        }

        $this->audit($request, 'rede.editar', 'rede', $id, $before, $data);
        $this->flashSuccess('Rede atualizada com sucesso.');

        return $this->redirect('/admin/redes');
    }

    private function findScoped(int $id): array
    {
        $item = $this->redes->findById($id);

        if ($item === null) {
            throw new NotFoundException('Rede não encontrada.');
        }

        if (!$this->isPlatformContext() && (int) $item['id'] !== $this->contextRedeId()) {
            throw new ForbiddenException('Rede fora do seu contexto.');
        }

        return $item;
    }
}

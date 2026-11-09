<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\ModalidadeRepository;
use App\Services\AuditService;
use App\Services\ModalidadeService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

final class ModalidadeController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly ModalidadeService $service,
        private readonly ModalidadeRepository $modalidades
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        return $this->render('admin.modalidades.index', [
            'title' => 'Modalidades',
            'modalidades' => $this->service->forScope(
                $this->contextRedeId(),
                $this->isPlatformContext()
            ),
            'isPlatform' => $this->isPlatformContext(),
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->render('admin.modalidades.form', array_merge($this->formState(), [
            'title' => 'Nova modalidade',
            'item' => null,
            'isPlatform' => $this->isPlatformContext(),
        ]));
    }

    public function store(Request $request): Response
    {
        $data = $request->all();
        $redeId = $this->isPlatformContext() ? null : $this->contextRedeId();

        try {
            $id = $this->service->create($redeId, $data);
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/modalidades/nova', $exception->getErrors(), $data);
        }

        $this->audit($request, 'modalidade.criar', 'modalidade', $id, null, $data, $redeId);
        $this->flashSuccess('Modalidade criada com sucesso.');

        return $this->redirect('/admin/modalidades');
    }

    public function edit(Request $request): Response
    {
        $item = $this->findScoped((int) $request->routeParam('id'));

        return $this->render('admin.modalidades.form', array_merge($this->formState(), [
            'title' => 'Editar modalidade',
            'item' => $item,
            'isPlatform' => $this->isPlatformContext(),
        ]));
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->routeParam('id');
        $before = $this->findScoped($id);
        $data = $request->all();

        try {
            $this->service->update(
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId(),
                $id,
                $data
            );
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/modalidades/' . $id . '/editar', $exception->getErrors(), $data);
        }

        $this->audit(
            $request,
            'modalidade.editar',
            'modalidade',
            $id,
            $before,
            $data,
            $before['rede_id'] !== null ? (int) $before['rede_id'] : null
        );
        $this->flashSuccess('Modalidade atualizada com sucesso.');

        return $this->redirect('/admin/modalidades');
    }

    private function findScoped(int $id): array
    {
        $modalidade = $this->modalidades->findById($id);

        if ($modalidade === null) {
            throw new NotFoundException('Modalidade não encontrada.');
        }

        return $this->service->assertCanAccess(
            $id,
            $this->contextRedeId(),
            $this->contextUnidadeId(),
            $this->contextRegiaoId()
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\ModalidadeRepository;
use App\Repositories\TurmaRepository;
use App\Repositories\UnidadeRepository;
use App\Services\AuditService;
use App\Services\TurmaService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

final class TurmaController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly TurmaRepository $turmas,
        private readonly TurmaService $service,
        private readonly ModalidadeRepository $modalidades,
        private readonly UnidadeRepository $unidades
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('busca', ''));

        return $this->render('admin.turmas.index', [
            'title' => 'Turmas',
            'turmas' => $this->service->forScope(
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId(),
                $search
            ),
            'search' => $search,
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->render('admin.turmas.form', array_merge($this->formState(), [
            'title' => 'Nova turma',
            'item' => null,
            'unidades' => $this->unidadeOptions(),
            'modalidades' => $this->modalidadeOptions(),
            'currentUnidadeId' => $this->contextUnidadeId(),
        ]));
    }

    public function store(Request $request): Response
    {
        $data = $request->all();

        try {
            $id = $this->service->create(
                $this->actorId(),
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId(),
                $data
            );
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/turmas/novo', $exception->getErrors(), $data);
        }

        $turma = $this->turmas->findById($id);
        $this->audit($request, 'turma.criar', 'turma', $id, null, $data, (int) $turma['rede_id'], (int) $turma['unidade_id']);
        $this->flashSuccess('Turma criada com sucesso.');

        return $this->redirect('/admin/turmas');
    }

    public function edit(Request $request): Response
    {
        $item = $this->findScoped((int) $request->routeParam('id'));

        return $this->render('admin.turmas.form', array_merge($this->formState(), [
            'title' => 'Editar turma',
            'item' => $item,
            'unidades' => $this->unidadeOptions(),
            'modalidades' => $this->modalidadeOptions(),
            'currentUnidadeId' => $this->contextUnidadeId(),
        ]));
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->routeParam('id');
        $before = $this->findScoped($id);
        $data = $request->all();

        try {
            $this->service->update(
                $this->actorId(),
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId(),
                $id,
                $data
            );
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/turmas/' . $id . '/editar', $exception->getErrors(), $data);
        }

        $this->audit($request, 'turma.editar', 'turma', $id, $before, $data, (int) $before['rede_id'], (int) $before['unidade_id']);
        $this->flashSuccess('Turma atualizada com sucesso.');

        return $this->redirect('/admin/turmas');
    }

    public function horarios(Request $request): Response
    {
        $turma = $this->findScoped((int) $request->routeParam('id'));

        return $this->render('admin.turmas.horarios', array_merge($this->formState(), [
            'title' => 'Horários da turma',
            'turma' => $turma,
            'horarios' => $this->service->horariosForTurma((int) $turma['id']),
        ]));
    }

    public function addHorario(Request $request): Response
    {
        $turmaId = (int) $request->routeParam('id');
        $turma = $this->findScoped($turmaId);
        $data = $request->all();

        try {
            $id = $this->service->addHorario(
                $turmaId,
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId(),
                $data
            );
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/turmas/' . $turmaId . '/horarios', $exception->getErrors(), $data);
        }

        $this->audit($request, 'turma.horario.criar', 'turma_horario', $id, null, $data, (int) $turma['rede_id'], (int) $turma['unidade_id']);
        $this->flashSuccess('Horário adicionado com sucesso.');

        return $this->redirect('/admin/turmas/' . $turmaId . '/horarios');
    }

    public function removeHorario(Request $request): Response
    {
        $turmaId = (int) $request->routeParam('id');
        $horarioId = (int) $request->routeParam('horarioId');
        $turma = $this->findScoped($turmaId);

        try {
            $this->service->removeHorario(
                $turmaId,
                $horarioId,
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            );
        } catch (ValidationException $exception) {
            $this->flashError($exception->getErrors()['horario'][0] ?? 'Não foi possível remover o horário.');

            return $this->redirect('/admin/turmas/' . $turmaId . '/horarios');
        }

        $this->audit($request, 'turma.horario.remover', 'turma_horario', $horarioId, null, ['status' => 'INATIVO'], (int) $turma['rede_id'], (int) $turma['unidade_id']);
        $this->flashSuccess('Horário removido com sucesso.');

        return $this->redirect('/admin/turmas/' . $turmaId . '/horarios');
    }

    private function findScoped(int $id): array
    {
        $turma = $this->turmas->findById($id);

        if ($turma === null) {
            throw new NotFoundException('Turma não encontrada.');
        }

        $this->service->assertCanAccess(
            $id,
            $this->contextRedeId(),
            $this->contextUnidadeId(),
            $this->contextRegiaoId()
        );

        return $turma;
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

    private function modalidadeOptions(): array
    {
        return $this->modalidades->allActiveForRede(
            $this->contextRedeId(),
            $this->isPlatformContext()
        );
    }
}

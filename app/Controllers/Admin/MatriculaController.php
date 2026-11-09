<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Exceptions\ValidationException;
use App\Repositories\MatriculaRepository;
use App\Repositories\PlanoPrecoRepository;
use App\Services\AlunoService;
use App\Services\AuditService;
use App\Services\MatriculaService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

final class MatriculaController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly MatriculaRepository $matriculas,
        private readonly MatriculaService $service,
        private readonly AlunoService $alunos,
        private readonly PlanoPrecoRepository $precos
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('busca', ''));

        return $this->render('admin.matriculas.index', [
            'title' => 'Matrículas',
            'matriculas' => $this->service->forScope(
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
        return $this->render('admin.matriculas.form', array_merge($this->formState(), [
            'title' => 'Nova matrícula',
            'matricula' => null,
            'alunos' => $this->alunos->forScope(
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            ),
            'precos' => $this->precos->activeForScope(
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            ),
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
            return $this->backWithErrors('/admin/matriculas/novo', $exception->getErrors(), $data);
        }

        $matricula = $this->matriculas->findById($id);
        $this->audit(
            $request,
            'matricula.criar',
            'matricula',
            $id,
            null,
            $data,
            (int) $matricula['rede_id'],
            (int) $matricula['unidade_id']
        );
        $this->flashSuccess('Matrícula criada com sucesso.');

        return $this->redirect('/admin/matriculas');
    }

    public function edit(Request $request): Response
    {
        $matricula = $this->findScoped((int) $request->routeParam('id'));

        return $this->render('admin.matriculas.form', array_merge($this->formState(), [
            'title' => 'Editar matrícula',
            'matricula' => $matricula,
            'alunos' => [],
            'precos' => [],
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
            return $this->backWithErrors('/admin/matriculas/' . $id . '/editar', $exception->getErrors(), $data);
        }

        $this->audit(
            $request,
            'matricula.editar',
            'matricula',
            $id,
            $before,
            $data,
            (int) $before['rede_id'],
            (int) $before['unidade_id']
        );
        $this->flashSuccess('Matrícula atualizada com sucesso.');

        return $this->redirect('/admin/matriculas');
    }

    private function findScoped(int $id): array
    {
        return $this->service->assertCanAccess(
            $id,
            $this->contextRedeId(),
            $this->contextUnidadeId(),
            $this->contextRegiaoId()
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Exceptions\ValidationException;
use App\Repositories\AlunoRepository;
use App\Repositories\AlunoResponsavelRepository;
use App\Repositories\PessoaRepository;
use App\Repositories\UnidadeRepository;
use App\Services\AlunoService;
use App\Services\AuditService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

final class AlunoController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly AlunoRepository $alunos,
        private readonly AlunoResponsavelRepository $responsaveis,
        private readonly PessoaRepository $pessoas,
        private readonly UnidadeRepository $unidades,
        private readonly AlunoService $service
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('busca', ''));

        return $this->render('admin.alunos.index', [
            'title' => 'Alunos',
            'alunos' => $this->service->forScope(
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
        return $this->render('admin.alunos.form', array_merge($this->formState(), [
            'title' => 'Novo aluno',
            'aluno' => null,
            'responsaveis' => [],
            'pessoas' => $this->pessoaOptions(),
            'unidades' => $this->unidadeOptions(),
            'isPlatform' => $this->isPlatformContext(),
            'currentRedeId' => $this->contextRedeId(),
            'currentUnidadeId' => $this->contextUnidadeId(),
            'currentRegiaoId' => $this->contextRegiaoId(),
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
            return $this->backWithErrors('/admin/alunos/novo', $exception->getErrors(), $data);
        }

        $aluno = $this->alunos->findById($id);
        $this->audit($request, 'aluno.criar', 'aluno', $id, null, $data, (int) $aluno['rede_id'], (int) $aluno['unidade_id']);
        $this->flashSuccess('Aluno criado com sucesso.');

        return $this->redirect('/admin/alunos');
    }

    public function edit(Request $request): Response
    {
        $aluno = $this->findScoped((int) $request->routeParam('id'));

        return $this->render('admin.alunos.form', array_merge($this->formState(), [
            'title' => 'Editar aluno',
            'aluno' => $aluno,
            'responsaveis' => $this->responsaveis->forAluno((int) $aluno['id']),
            'pessoas' => $this->pessoaOptions(),
            'unidades' => $this->unidadeOptions(),
            'isPlatform' => $this->isPlatformContext(),
            'currentRedeId' => $this->contextRedeId(),
            'currentUnidadeId' => $this->contextUnidadeId(),
            'currentRegiaoId' => $this->contextRegiaoId(),
        ]));
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->routeParam('id');
        $before = $this->findScoped($id);
        $data = $request->all();

        try {
            $this->service->update(
                $id,
                $this->actorId(),
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId(),
                $data
            );
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/alunos/' . $id . '/editar', $exception->getErrors(), $data);
        }

        $this->audit($request, 'aluno.editar', 'aluno', $id, $before, $data, (int) $before['rede_id'], (int) $before['unidade_id']);
        $this->flashSuccess('Aluno atualizado com sucesso.');

        return $this->redirect('/admin/alunos');
    }

    public function addResponsavel(Request $request): Response
    {
        $alunoId = (int) $request->routeParam('id');
        $aluno = $this->findScoped($alunoId);
        $data = $request->all();

        try {
            $id = $this->service->addResponsavel(
                $alunoId,
                $this->actorId(),
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId(),
                $data
            );
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/alunos/' . $alunoId . '/editar', $exception->getErrors(), $data);
        }

        $this->audit(
            $request,
            'responsavel.criar',
            'aluno_responsavel',
            $id,
            null,
            $data,
            (int) $aluno['rede_id'],
            (int) $aluno['unidade_id']
        );
        $this->flashSuccess('Responsável adicionado com sucesso.');

        return $this->redirect('/admin/alunos/' . $alunoId . '/editar');
    }

    public function removeResponsavel(Request $request): Response
    {
        $alunoId = (int) $request->routeParam('id');
        $responsavelId = (int) $request->routeParam('responsavelId');
        $aluno = $this->findScoped($alunoId);
        $before = $this->responsaveis->findById($responsavelId);

        try {
            $this->service->removeResponsavel(
                $alunoId,
                $responsavelId,
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            );
        } catch (ValidationException $exception) {
            $this->flashError($exception->getErrors()['responsavel'][0] ?? 'Não foi possível remover o responsável.');

            return $this->redirect('/admin/alunos/' . $alunoId . '/editar');
        }

        $this->audit(
            $request,
            'responsavel.remover',
            'aluno_responsavel',
            $responsavelId,
            $before,
            ['status' => 'INATIVO'],
            (int) $aluno['rede_id'],
            (int) $aluno['unidade_id']
        );
        $this->flashSuccess('Responsável removido com sucesso.');

        return $this->redirect('/admin/alunos/' . $alunoId . '/editar');
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

    private function pessoaOptions(): array
    {
        return $this->pessoas->forScope(
            $this->contextRedeId(),
            $this->contextUnidadeId(),
            $this->contextRegiaoId(),
            $this->actorId()
        );
    }
}

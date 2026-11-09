<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Exceptions\ValidationException;
use App\Services\AlunoService;
use App\Services\AuditService;
use App\Services\FrequenciaService;
use App\Services\TurmaService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

final class FrequenciaController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly FrequenciaService $service,
        private readonly TurmaService $turmas,
        private readonly AlunoService $alunos
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('busca', ''));

        return $this->render('admin.frequencias.index', [
            'title' => 'Frequência',
            'frequencias' => $this->service->forScope(
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
        return $this->render('admin.frequencias.form', array_merge($this->formState(), [
            'title' => 'Registrar frequência',
            'turmas' => $this->turmas->forScope(
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            ),
            'alunos' => $this->alunos->forScope(
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
            $id = $this->service->register(
                $this->actorId(),
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId(),
                $data
            );
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/frequencias/registrar', $exception->getErrors(), $data);
        }

        $this->audit(
            $request,
            'frequencia.registrar',
            'frequencia',
            $id,
            null,
            $data,
            $this->contextRedeId(),
            $this->contextUnidadeId()
        );
        $this->flashSuccess('Frequência registrada com sucesso.');

        return $this->redirect('/admin/frequencias');
    }
}

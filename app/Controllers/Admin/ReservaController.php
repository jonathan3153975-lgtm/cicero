<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Exceptions\ValidationException;
use App\Services\AlunoService;
use App\Services\AuditService;
use App\Services\ReservaService;
use App\Services\TurmaService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

final class ReservaController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly ReservaService $service,
        private readonly TurmaService $turmas,
        private readonly AlunoService $alunos
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('busca', ''));

        return $this->render('admin.reservas.index', [
            'title' => 'Reservas',
            'reservas' => $this->service->forScope(
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
        $turmas = $this->turmas->forScope(
            $this->contextRedeId(),
            $this->contextUnidadeId(),
            $this->contextRegiaoId()
        );
        $horarios = [];

        foreach ($turmas as $turma) {
            foreach ($this->turmas->horariosForTurma((int) $turma['id']) as $horario) {
                if ($horario['status'] !== 'ATIVO') {
                    continue;
                }

                $horarios[] = array_merge($horario, [
                    'turma_nome' => $turma['nome'],
                    'unidade_id' => $turma['unidade_id'],
                    'unidade_nome' => $turma['unidade_nome'],
                ]);
            }
        }

        return $this->render('admin.reservas.form', array_merge($this->formState(), [
            'title' => 'Nova reserva',
            'alunos' => $this->alunos->forScope(
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            ),
            'horarios' => $horarios,
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
            return $this->backWithErrors('/admin/reservas/nova', $exception->getErrors(), $data);
        }

        $this->audit(
            $request,
            'reserva.criar',
            'reserva',
            $id,
            null,
            $data,
            $this->contextRedeId(),
            $this->contextUnidadeId()
        );
        $this->flashSuccess('Reserva criada com sucesso.');

        return $this->redirect('/admin/reservas');
    }

    public function cancel(Request $request): Response
    {
        $id = (int) $request->routeParam('id');

        try {
            $this->service->cancel(
                $id,
                $this->contextRedeId(),
                $this->contextUnidadeId(),
                $this->contextRegiaoId()
            );
        } catch (ValidationException $exception) {
            $this->flashError($exception->getErrors()['reserva'][0] ?? 'Não foi possível cancelar a reserva.');

            return $this->redirect('/admin/reservas');
        }

        $this->audit(
            $request,
            'reserva.cancelar',
            'reserva',
            $id,
            null,
            ['status' => 'CANCELADA'],
            $this->contextRedeId(),
            $this->contextUnidadeId()
        );
        $this->flashSuccess('Reserva cancelada com sucesso.');

        return $this->redirect('/admin/reservas');
    }
}

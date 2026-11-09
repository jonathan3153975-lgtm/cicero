<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\RedeRepository;
use App\Repositories\UnidadeRepository;
use App\Repositories\UsuarioRepository;
use App\Services\AuditService;
use App\Services\UsuarioService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;
use RuntimeException;

final class UsuarioController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly UsuarioRepository $usuarios,
        private readonly UnidadeRepository $unidades,
        private readonly RedeRepository $redes,
        private readonly UsuarioService $service
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        return $this->render('admin.usuarios.index', [
            'title' => 'Usuários',
            'usuarios' => $this->usuarios->forScope($this->contextRedeId(), $this->contextUnidadeId(), $this->contextRegiaoId()),
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->render('admin.usuarios.form', array_merge($this->formState(), [
            'title' => 'Novo usuário',
            'item' => null,
            'redes' => $this->redeOptions(),
            'unidades' => $this->unidadeOptions(),
            'isPlatform' => $this->isPlatformContext(),
            'currentRedeId' => $this->contextRedeId(),
            'currentUnidadeId' => $this->contextUnidadeId(),
        ]));
    }

    public function store(Request $request): Response
    {
        $data = $request->all();
        $redeId = $this->targetRedeId($request->input('rede_id'));
        $unidadeId = $this->targetUnidadeId($request->input('unidade_id'));
        $regiaoId = $this->contextRegiaoId();

        if ($regiaoId !== null && $unidadeId !== null && !$this->unidades->isInRegiao($unidadeId, $regiaoId)) {
            return $this->backWithErrors('/admin/usuarios/novo', [
                'unidade_id' => ['A unidade selecionada não pertence à sua região.'],
            ], $data);
        }

        try {
            $id = $this->service->createWithMembership($data, $redeId, $unidadeId);
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/usuarios/novo', $exception->getErrors(), $data);
        } catch (RuntimeException $exception) {
            return $this->backWithErrors('/admin/usuarios/novo', ['password' => [$exception->getMessage()]], $data);
        }

        $this->audit($request, 'usuario.criar', 'usuario', $id, null, $data, $redeId, $unidadeId);
        $this->flashSuccess('Usuário criado com sucesso.');

        return $this->redirect('/admin/usuarios');
    }

    public function edit(Request $request): Response
    {
        $item = $this->findScoped((int) $request->routeParam('id'));

        return $this->render('admin.usuarios.form', array_merge($this->formState(), [
            'title' => 'Editar usuário',
            'item' => $item,
            'redes' => $this->redeOptions(),
            'unidades' => $this->unidadeOptions(),
            'isPlatform' => $this->isPlatformContext(),
            'currentRedeId' => $this->contextRedeId(),
            'currentUnidadeId' => $this->contextUnidadeId(),
        ]));
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->routeParam('id');
        $before = $this->findScoped($id);
        $data = $request->all();

        try {
            $this->service->updateValidated($id, $data);
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/usuarios/' . $id . '/editar', $exception->getErrors(), $data);
        }

        $password = (string) ($data['password'] ?? '');

        if ($password !== '') {
            try {
                $this->service->changePassword($id, $password);
            } catch (RuntimeException $exception) {
                return $this->backWithErrors('/admin/usuarios/' . $id . '/editar', [
                    'password' => [$exception->getMessage()],
                ], $data);
            }
        }

        $this->audit($request, 'usuario.editar', 'usuario', $id, $before, [
            'nome' => $data['nome'] ?? null,
            'email' => $data['email'] ?? null,
            'status' => $data['status'] ?? null,
            'senha_alterada' => $password !== '',
        ]);
        $this->flashSuccess('Usuário atualizado com sucesso.');

        return $this->redirect('/admin/usuarios');
    }

    private function redeOptions(): array
    {
        return $this->isPlatformContext() ? $this->redes->all() : array_values(array_filter([
            $this->redes->findById((int) $this->contextRedeId()),
        ]));
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

    private function targetRedeId(mixed $input): ?int
    {
        if (!$this->isPlatformContext()) {
            return $this->contextRedeId();
        }

        $value = (int) $input;

        return $value > 0 ? $value : null;
    }

    private function targetUnidadeId(mixed $input): ?int
    {
        if ($this->contextUnidadeId() !== null) {
            return $this->contextUnidadeId();
        }

        $value = (int) $input;

        return $value > 0 ? $value : null;
    }

    private function findScoped(int $id): array
    {
        $item = $this->usuarios->findById($id);

        if ($item === null) {
            throw new NotFoundException('Usuário não encontrado.');
        }

        $this->service->assertCanManage(
            $this->actorId(),
            $id,
            $this->contextRedeId(),
            $this->contextUnidadeId(),
            $this->contextRegiaoId()
        );

        return $item;
    }
}

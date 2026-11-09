<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\PessoaRepository;
use App\Repositories\RedeRepository;
use App\Repositories\RegiaoRepository;
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
        private readonly RegiaoRepository $regioes,
        private readonly RedeRepository $redes,
        private readonly PessoaRepository $pessoas,
        private readonly UsuarioService $service
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        $filterRedeId = $this->nullableInt($request->query('rede_id'));
        $filterRegiaoId = $this->nullableInt($request->query('regiao_id'));
        $filterUnidadeId = $this->nullableInt($request->query('unidade_id'));

        // Aplicar filtros respeitando o contexto do usuário
        $redeId = $this->resolveFilterRedeId($filterRedeId);
        $regiaoId = $this->resolveFilterRegiaoId($filterRegiaoId, $redeId);
        $unidadeId = $this->resolveFilterUnidadeId($filterUnidadeId, $redeId, $regiaoId);

        return $this->render('admin.usuarios.index', array_merge($this->formState(), [
            'title' => 'Usuários',
            'usuarios' => $this->usuarios->forScope($redeId, $unidadeId, $regiaoId),
            'filterRedeId' => $filterRedeId,
            'filterRegiaoId' => $filterRegiaoId,
            'filterUnidadeId' => $filterUnidadeId,
            'redes' => $this->redeOptions(),
            'regioes' => $this->regiaoOptions($redeId),
            'unidades' => $this->unidadeOptions($redeId, $regiaoId),
            'isPlatform' => $this->isPlatformContext(),
        ]));
    }

    public function create(Request $request): Response
    {
        return $this->render('admin.usuarios.form', array_merge($this->formState(), [
            'title' => 'Novo usuário',
            'item' => null,
            'redes' => $this->redeOptions(),
            'regioes' => $this->regiaoOptions($this->contextRedeId()),
            'unidades' => $this->unidadeOptions($this->contextRedeId(), $this->contextRegiaoId()),
            'isPlatform' => $this->isPlatformContext(),
            'currentRedeId' => $this->contextRedeId(),
            'currentRegiaoId' => $this->contextRegiaoId(),
            'currentUnidadeId' => $this->contextUnidadeId(),
        ]));
    }

    public function store(Request $request): Response
    {
        $data = $request->all();
        $redeId = $this->targetRedeId($request->input('rede_id'));
        $unidadeId = $this->targetUnidadeId($request->input('unidade_id'));
        $regiaoId = $this->contextRegiaoId();
        $pessoaId = $this->nullableInt($request->input('pessoa_id'));

        if ($regiaoId !== null && $unidadeId !== null && !$this->unidades->isInRegiao($unidadeId, $regiaoId)) {
            return $this->backWithErrors('/admin/usuarios/novo', [
                'unidade_id' => ['A unidade selecionada não pertence à sua região.'],
            ], $data);
        }

        if ($pessoaId !== null) {
            $pessoa = $this->pessoas->findById($pessoaId);
            if ($pessoa === null) {
                return $this->backWithErrors('/admin/usuarios/novo', [
                    'pessoa_id' => ['Pessoa não encontrada.'],
                ], $data);
            }
            // Verificar se a pessoa já está vinculada a outro usuário
            $existingUser = $this->usuarios->findByPessoaId($pessoaId);
            if ($existingUser !== null) {
                return $this->backWithErrors('/admin/usuarios/novo', [
                    'pessoa_id' => ['Esta pessoa já está vinculada a outro usuário.'],
                ], $data);
            }
        } else {
            return $this->backWithErrors('/admin/usuarios/novo', [
                'pessoa_id' => ['Selecione uma pessoa (busque por CPF/CNPJ).'],
            ], $data);
        }

        try {
            $id = $this->service->createWithMembership($data, $redeId, $unidadeId, $pessoaId);
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
            'regioes' => $this->regiaoOptions($this->contextRedeId()),
            'unidades' => $this->unidadeOptions($this->contextRedeId(), $this->contextRegiaoId()),
            'isPlatform' => $this->isPlatformContext(),
            'currentRedeId' => $this->contextRedeId(),
            'currentRegiaoId' => $this->contextRegiaoId(),
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

    public function searchPessoas(Request $request): Response
    {
        $search = trim((string) $request->query('q', ''));
        $redeId = $this->contextRedeId();
        $unidadeId = $this->contextUnidadeId();
        $regiaoId = $this->contextRegiaoId();

        if ($search === '') {
            return $this->json(['pessoas' => []]);
        }

        // Buscar pessoas por nome, CPF, CNPJ ou email
        $pessoas = $this->pessoas->forScope($redeId, $unidadeId, $regiaoId, null, $search);

        // Formatar resultado
        $results = array_map(function (array $pessoa): array {
            return [
                'id' => (int) $pessoa['id'],
                'nome' => $pessoa['nome'],
                'cpf' => $pessoa['cpf'],
                'cnpj' => $pessoa['cnpj'],
                'email_principal' => $pessoa['email_principal'],
                'tipo' => $pessoa['tipo'],
                'display' => $pessoa['nome'] . ' (' . ($pessoa['cpf'] ? 'CPF: ' . $pessoa['cpf'] : ($pessoa['cnpj'] ? 'CNPJ: ' . $pessoa['cnpj'] : 'Sem documento')) . ')',
            ];
        }, $pessoas);

        return $this->json(['pessoas' => $results]);
    }

    private function redeOptions(): array
    {
        return $this->isPlatformContext() ? $this->redes->all() : array_values(array_filter([
            $this->redes->findById((int) $this->contextRedeId()),
        ]));
    }

    private function regiaoOptions(?int $redeId): array
    {
        if ($redeId === null) {
            return $this->regioes->all();
        }
        return $this->regioes->forRede($redeId);
    }

    private function unidadeOptions(?int $redeId, ?int $regiaoId): array
    {
        if ($regiaoId !== null) {
            return $this->unidades->forRegiao($regiaoId);
        }
        if ($redeId !== null) {
            return $this->unidades->forRede($redeId);
        }
        return $this->unidades->all();
    }

    private function resolveFilterRedeId(?int $filterRedeId): ?int
    {
        if (!$this->isPlatformContext()) {
            return $this->contextRedeId();
        }
        return $filterRedeId;
    }

    private function resolveFilterRegiaoId(?int $filterRegiaoId, ?int $redeId): ?int
    {
        if ($this->contextRegiaoId() !== null) {
            return $this->contextRegiaoId();
        }
        if ($filterRegiaoId !== null && $redeId !== null) {
            $regiao = $this->regioes->findById($filterRegiaoId);
            if ($regiao !== null && (int) $regiao['rede_id'] === $redeId) {
                return $filterRegiaoId;
            }
        }
        return null;
    }

    private function resolveFilterUnidadeId(?int $filterUnidadeId, ?int $redeId, ?int $regiaoId): ?int
    {
        if ($this->contextUnidadeId() !== null) {
            return $this->contextUnidadeId();
        }
        if ($filterUnidadeId !== null) {
            $unidade = $this->unidades->findById($filterUnidadeId);
            if ($unidade !== null) {
                if ($redeId !== null && (int) $unidade['rede_id'] !== $redeId) {
                    return null;
                }
                if ($regiaoId !== null && !$this->unidades->isInRegiao($filterUnidadeId, $regiaoId)) {
                    return null;
                }
                return $filterUnidadeId;
            }
        }
        return null;
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

    private function nullableInt(mixed $value): ?int
    {
        $value = (int) $value;
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

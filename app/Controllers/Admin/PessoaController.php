<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\PessoaRepository;
use App\Repositories\RedeRepository;
use App\Repositories\RegiaoRepository;
use App\Repositories\UnidadeRepository;
use App\Services\AuditService;
use App\Services\PessoaService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

final class PessoaController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly PessoaRepository $pessoas,
        private readonly PessoaService $service,
        private readonly RedeRepository $redes,
        private readonly RegiaoRepository $regioes,
        private readonly UnidadeRepository $unidades
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('busca', ''));
        $pessoas = $this->service->forScope(
            $this->contextRedeId(),
            $this->contextUnidadeId(),
            $this->contextRegiaoId(),
            $this->actorId(),
            $search
        );

        // AJAX request - return only results partial
        if ($request->header('X-Requested-With') === 'XMLHttpRequest') {
            return $this->renderPartial('admin.pessoas._resultados', [
                'pessoas' => $pessoas,
                'search' => $search,
                'canEdit' => isset($this->can) && $this->can('pessoa.editar'),
            ]);
        }

        return $this->render('admin.pessoas.index', [
            'title' => 'Pessoas',
            'pessoas' => $pessoas,
            'search' => $search,
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->render('admin.pessoas.form', array_merge($this->formState(), [
            'title' => 'Nova pessoa',
            'item' => null,
            'redes' => $this->redeOptions(),
            'regioes' => $this->regiaoOptions(),
            'unidades' => $this->unidadeOptions(),
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

        try {
            $id = $this->service->create($data, $redeId, $this->actorId());
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/pessoas/novo', $exception->getErrors(), $data);
        }

        $this->audit($request, 'pessoa.criar', 'pessoa', $id, null, $data, $redeId);
        $this->flashSuccess('Pessoa criada com sucesso.');

        return $this->redirect('/admin/pessoas');
    }

    public function edit(Request $request): Response
    {
        $item = $this->findScoped((int) $request->routeParam('id'));

        return $this->render('admin.pessoas.form', array_merge($this->formState(), [
            'title' => 'Editar pessoa',
            'item' => $item,
            'redes' => $this->redeOptions(),
            'regioes' => $this->regiaoOptions(),
            'unidades' => $this->unidadeOptions(),
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
        $redeId = $this->targetRedeId($request->input('rede_id'));

        try {
            $this->service->update(
                $id,
                $data,
                $redeId,
                $this->contextUnidadeId(),
                $this->contextRegiaoId(),
                $this->actorId()
            );
        } catch (ValidationException $exception) {
            return $this->backWithErrors('/admin/pessoas/' . $id . '/editar', $exception->getErrors(), $data);
        }

        $this->audit($request, 'pessoa.editar', 'pessoa', $id, $before, $data, $redeId);
        $this->flashSuccess('Pessoa atualizada com sucesso.');

        return $this->redirect('/admin/pessoas');
    }

    private function findScoped(int $id): array
    {
        $pessoa = $this->pessoas->findById($id);

        if ($pessoa === null) {
            throw new NotFoundException('Pessoa não encontrada.');
        }

        $this->service->assertCanAccess(
            $this->actorId(),
            $id,
            $this->contextRedeId(),
            $this->contextUnidadeId(),
            $this->contextRegiaoId()
        );

        return $pessoa;
    }

    private function targetRedeId(mixed $input): ?int
    {
        if (!$this->isPlatformContext()) {
            return $this->contextRedeId();
        }

        $value = (int) $input;

        return $value > 0 ? $value : null;
    }

    private function redeOptions(): array
    {
        return $this->isPlatformContext() ? $this->redes->allActive() : array_values(array_filter([
            $this->redes->findById((int) $this->contextRedeId()),
        ]));
    }

    private function regiaoOptions(): array
    {
        if ($this->isPlatformContext()) {
            return $this->regioes->allActive();
        }
        $redeId = (int) $this->contextRedeId();
        $regioes = $this->regioes->forRede($redeId);
        return array_values(array_filter($regioes, fn($r) => $r['status'] === 'ATIVO'));
    }

    private function unidadeOptions(): array
    {
        $regiaoId = $this->contextRegiaoId();
        $redeId = $this->contextRedeId();
        
        if ($regiaoId !== null) {
            $unidades = $this->unidades->forRegiao($regiaoId);
            return array_values(array_filter($unidades, fn($u) => $u['situacao'] === 'ATIVA'));
        }
        
        if ($redeId !== null) {
            $unidades = $this->unidades->forRede($redeId);
            return array_values(array_filter($unidades, fn($u) => $u['situacao'] === 'ATIVA'));
        }
        
        return $this->unidades->allActive();
    }
}

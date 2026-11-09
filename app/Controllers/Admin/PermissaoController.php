<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Repositories\PermissaoRepository;
use App\Services\AuditService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

final class PermissaoController extends AdminController
{
    public function __construct(
        View $view,
        Session $session,
        AuditService $audit,
        private readonly PermissaoRepository $permissoes
    ) {
        parent::__construct($view, $session, $audit);
    }

    public function index(Request $request): Response
    {
        $groups = [];

        foreach ($this->permissoes->all() as $permission) {
            $groups[(string) $permission['modulo']][] = $permission;
        }

        return $this->render('admin.permissoes.index', [
            'title' => 'Permissões',
            'groups' => $groups,
            'total' => count($this->permissoes->all()),
        ]);
    }
}

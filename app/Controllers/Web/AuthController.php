<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Controllers\Controller;
use App\DTOs\LoginData;
use App\Services\AuditService;
use App\Services\AuthService;
use App\Services\TenantContextService;
use App\Support\Config;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;
use App\Validators\Validator;
use Psr\Log\LoggerInterface;

final class AuthController extends Controller
{
    public function __construct(
        View $view,
        private readonly Session $session,
        private readonly Config $config,
        private readonly AuthService $auth,
        private readonly TenantContextService $contexts,
        private readonly AuditService $audit,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($view);
    }

    public function showLogin(Request $request): Response
    {
        return $this->render('auth.login', [
            'title' => 'Entrar',
            'errors' => (array) $this->session->getFlash('errors', []),
            'old' => (array) $this->session->getFlash('old', []),
        ], 'layouts.auth');
    }

    public function login(Request $request): Response
    {
        $data = LoginData::fromArray($request->all());

        $validator = (new Validator($request->all()))
            ->required('email')
            ->email('email')
            ->required('password')
            ->minLength('password', 6);

        if ($validator->fails()) {
            return $this->failedAttempt($validator->errors(), $data->email);
        }

        if ((int) $this->session->get('auth.locked_until', 0) > time()) {
            $this->logger->warning('Tentativa de login durante bloqueio temporário.', [
                'email' => $data->email,
                'ip' => $request->ip(),
            ]);

            return $this->failedAttempt(
                ['email' => 'Muitas tentativas. Aguarde alguns minutos e tente novamente.'],
                $data->email
            );
        }

        $user = $this->auth->attempt($data->email, $data->password);

        if ($user === null) {
            $this->logger->warning('Falha de autenticação.', [
                'email' => $data->email,
                'ip' => $request->ip(),
            ]);

            $this->registerFailure();

            return $this->failedAttempt(['email' => 'Credenciais inválidas.'], $data->email);
        }

        $userId = (int) $user['id'];
        $availableContexts = $this->contexts->contextsForUser($userId);

        if ($availableContexts === []) {
            $this->session->flash('errors', [
                'email' => ['Usuário sem vínculo ativo. Contate o administrador.'],
            ]);

            return $this->redirect('/login');
        }

        $this->session->remove('auth.attempts');
        $this->session->remove('auth.locked_until');
        $this->session->regenerate(true);
        $this->session->set('auth.user_id', $userId);
        $this->session->set('auth.user', [
            'id' => $userId,
            'name' => $user['nome'],
            'email' => $user['email'],
        ]);

        $this->audit->record(
            $userId,
            'auth.login',
            'usuario',
            $userId,
            null,
            null,
            null,
            null,
            $request->ip(),
            $request->userAgent()
        );

        if (count($availableContexts) > 1) {
            $this->session->flash('success', 'Selecione o contexto de atuação.');

            return $this->redirect('/contexto');
        }

        $context = $availableContexts[0];
        $this->session->set('context', $context);
        $this->session->flash('success', 'Bem-vindo de volta!');

        $intended = $this->session->getFlash('_intended');
        $destination = is_string($intended) && $intended !== '' && $intended !== '/login'
            ? $intended
            : '/dashboard';

        return $this->redirect($destination);
    }

    public function logout(Request $request): Response
    {
        $userId = (int) ($this->session->get('auth.user_id') ?? 0);
        $context = $this->session->get('context');
        $context = is_array($context) ? $context : null;

        if ($userId > 0) {
            $this->audit->record(
                $userId,
                'auth.logout',
                'usuario',
                $userId,
                null,
                null,
                $context !== null && $context['rede_id'] !== null ? (int) $context['rede_id'] : null,
                $context !== null && $context['unidade_id'] !== null ? (int) $context['unidade_id'] : null,
                $request->ip(),
                $request->userAgent()
            );
        }

        $this->session->regenerate(true);
        $this->session->remove('auth.user_id');
        $this->session->remove('auth.user');
        $this->session->remove('context');
        $this->session->flash('success', 'Sessão encerrada com sucesso.');

        return $this->redirect('/login');
    }

    private function registerFailure(): void
    {
        $attempts = (int) $this->session->get('auth.attempts', 0) + 1;
        $maxAttempts = (int) $this->config->get('security.login_max_attempts', 5);

        if ($attempts >= $maxAttempts) {
            $lockout = (int) $this->config->get('security.login_lockout_seconds', 300);
            $this->session->set('auth.locked_until', time() + $lockout);
            $this->session->set('auth.attempts', 0);

            return;
        }

        $this->session->set('auth.attempts', $attempts);
    }

    private function failedAttempt(array $errors, string $email): Response
    {
        $normalized = [];

        foreach ($errors as $field => $messages) {
            $normalized[$field] = is_array($messages) ? array_values($messages) : [$messages];
        }

        $this->session->flash('errors', $normalized);
        $this->session->flash('old', ['email' => $email]);

        return $this->redirect('/login');
    }
}

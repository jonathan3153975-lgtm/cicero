<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Controllers\Controller;
use App\DTOs\LoginData;
use App\Support\Config;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;
use App\Validators\Validator;

final class AuthController extends Controller
{
    public function __construct(
        View $view,
        private readonly Session $session,
        private readonly Config $config
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
            $this->session->flash('errors', [
                'email' => 'Muitas tentativas. Aguarde alguns minutos e tente novamente.',
            ]);

            return $this->redirect('/login');
        }

        $expectedEmail = strtolower((string) $this->config->get('security.demo_email', ''));
        $passwordHash = (string) $this->config->get('security.demo_password_hash', '');

        $emailMatches = $expectedEmail !== ''
            && hash_equals($expectedEmail, strtolower($data->email));
        $passwordMatches = $passwordHash !== ''
            && password_verify($data->password, $passwordHash);

        if (!$emailMatches || !$passwordMatches) {
            $this->registerFailure();

            return $this->failedAttempt(
                ['email' => 'Credenciais inválidas.'],
                $data->email
            );
        }

        $this->session->remove('auth.attempts');
        $this->session->remove('auth.locked_until');
        $this->session->regenerate(true);

        $this->session->set('auth.user_id', 1);
        $this->session->set('auth.user', [
            'id' => 1,
            'name' => 'Administrador',
            'email' => $expectedEmail,
            'role' => 'admin',
        ]);

        $this->session->flash('success', 'Bem-vindo de volta!');

        $intended = $this->session->getFlash('_intended');
        $destination = is_string($intended) && $intended !== '' && $intended !== '/login'
            ? $intended
            : '/dashboard';

        return $this->redirect($destination);
    }

    public function logout(Request $request): Response
    {
        $this->session->regenerate(true);
        $this->session->remove('auth.user_id');
        $this->session->remove('auth.user');
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
        $this->session->flash('errors', $errors);
        $this->session->flash('old', ['email' => $email]);

        return $this->redirect('/login');
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Config;
use App\Support\View;
use PHPUnit\Framework\TestCase;

final class LoginViewTest extends TestCase
{
    private function render(array $errors): string
    {
        $config = new Config(base_path('config'));
        $config->load();

        $view = new View($config);

        return $view->render('auth.login', [
            'title' => 'Entrar',
            'errors' => $errors,
            'old' => [],
            'csrfName' => '_token',
            'csrfToken' => 'token-de-teste',
        ], null);
    }

    public function testItRendersAStringErrorCompletely(): void
    {
        $html = $this->render(['email' => 'Credenciais inválidas.']);

        self::assertStringContainsString('Credenciais inválidas.', $html);
        self::assertStringNotContainsString('<p class="form__error">C</p>', $html);
    }

    public function testItRendersAnArrayErrorCompletely(): void
    {
        $html = $this->render(['email' => ['Credenciais inválidas.']]);

        self::assertStringContainsString('Credenciais inválidas.', $html);
    }
}

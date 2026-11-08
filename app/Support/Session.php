<?php

declare(strict_types=1);

namespace App\Support;

final class Session
{
    private bool $started = false;

    private bool $aged = false;

    public function __construct(private readonly Config $config)
    {
    }

    public function start(): void
    {
        if ($this->started) {
            return;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            $lifetime = (int) $this->config->get('session.lifetime', 120);
            $secure = (bool) $this->config->get('session.secure', false);
            $httpOnly = (bool) $this->config->get('session.http_only', true);
            $sameSite = (string) $this->config->get('session.same_site', 'Lax');

            $savePath = storage_path('sessions');

            if (is_dir($savePath) && is_writable($savePath)) {
                session_save_path($savePath);
            }

            if (!headers_sent()) {
                session_set_cookie_params([
                    'lifetime' => $lifetime * 60,
                    'path' => '/',
                    'secure' => $secure,
                    'httponly' => $httpOnly,
                    'samesite' => $sameSite,
                ]);

                session_name((string) $this->config->get('session.name', 'academia_session'));
            }

            session_start();
        }

        $this->started = true;
        $this->ageFlash();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->start();

        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->start();

        $_SESSION[$key] = $value;
    }

    public function has(string $key): bool
    {
        $this->start();

        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        $this->start();

        unset($_SESSION[$key]);
    }

    public function all(): array
    {
        $this->start();

        return $_SESSION;
    }

    public function flash(string $key, mixed $value): void
    {
        $this->start();

        $new = $_SESSION['_flash_new'] ?? [];
        $new[$key] = $value;
        $_SESSION['_flash_new'] = $new;
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        $this->start();

        return $_SESSION['_flash'][$key] ?? $default;
    }

    public function flashes(): array
    {
        $this->start();

        return $_SESSION['_flash'] ?? [];
    }

    public function regenerate(bool $deleteOldSession = true): void
    {
        $this->start();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id($deleteOldSession);
        }
    }

    public function destroy(): void
    {
        $this->start();

        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        if (!headers_sent()) {
            setcookie((string) $this->config->get('session.name', 'academia_session'), '', [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => (bool) $this->config->get('session.secure', false),
                'httponly' => (bool) $this->config->get('session.http_only', true),
                'samesite' => (string) $this->config->get('session.same_site', 'Lax'),
            ]);
        }

        $this->started = false;
        $this->aged = false;
    }

    private function ageFlash(): void
    {
        if ($this->aged) {
            return;
        }

        $_SESSION['_flash'] = $_SESSION['_flash_new'] ?? [];
        unset($_SESSION['_flash_new']);

        $this->aged = true;
    }
}

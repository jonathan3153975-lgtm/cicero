<?php

declare(strict_types=1);

namespace App\Support;

use App\Exceptions\HttpException;
use App\Exceptions\ValidationException;
use ErrorException;
use Psr\Log\LoggerInterface;
use Throwable;

final class ExceptionHandler
{
    private bool $registered = false;

    public function __construct(
        private readonly View $view,
        private readonly Config $config,
        private readonly ?LoggerInterface $logger = null
    ) {
    }

    public function register(): void
    {
        if ($this->registered) {
            return;
        }

        set_exception_handler(function (Throwable $exception): void {
            $this->render($exception, Request::capture())->send();
        });

        set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }

            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        $this->registered = true;
    }

    public function render(Throwable $exception, Request $request): Response
    {
        $status = $exception instanceof HttpException ? $exception->getStatusCode() : 500;
        $debug = (bool) $this->config->get('app.debug', false);

        $this->log($exception, $request, $status);

        $message = $exception instanceof HttpException
            ? $exception->getPublicMessage()
            : ($debug ? $exception->getMessage() : 'Erro interno do servidor.');

        $errors = $exception instanceof ValidationException ? $exception->getErrors() : [];

        if ($request->wantsJson()) {
            $payload = [
                'success' => false,
                'message' => $message,
                'errors' => $errors,
            ];

            if ($debug && !$exception instanceof HttpException) {
                $payload['debug'] = [
                    'exception' => $exception::class,
                    'message' => $exception->getMessage(),
                ];
            }

            $response = Response::json($payload, $status);

            if ($exception instanceof HttpException && $exception->getHeaders() !== []) {
                foreach ($exception->getHeaders() as $name => $value) {
                    $response = $response->withHeader($name, $value);
                }
            }

            return $response;
        }

        $template = match ($status) {
            403 => 'errors.403',
            404 => 'errors.404',
            422 => 'errors.422',
            default => 'errors.500',
        };

        $data = [
            'status' => $status,
            'message' => $message,
            'errors' => $errors,
            'exception' => $debug && !$exception instanceof HttpException ? $exception : null,
        ];

        try {
            $body = $this->view->render($template, $data, null);
        } catch (Throwable) {
            $body = sprintf(
                '<!doctype html><html lang="pt-BR"><meta charset="utf-8"><title>%d</title>'
                . '<body style="font-family:Arial;background:#191919;color:#fff;padding:40px">'
                . '<h1>%d</h1><p>%s</p></body></html>',
                $status,
                $status,
                e($message)
            );
        }

        $response = Response::html($body, $status);

        if ($exception instanceof HttpException && $exception->getHeaders() !== []) {
            foreach ($exception->getHeaders() as $name => $value) {
                $response = $response->withHeader($name, $value);
            }
        }

        return $response;
    }

    private function log(Throwable $exception, Request $request, int $status): void
    {
        if ($this->logger === null) {
            return;
        }

        $context = [
            'exception' => $exception,
            'method' => $request->method,
            'path' => $request->path,
            'ip' => $request->ip(),
        ];

        if ($status >= 500) {
            $this->logger->error($exception->getMessage(), $context);

            return;
        }

        $this->logger->warning($exception->getMessage(), $context);
    }
}

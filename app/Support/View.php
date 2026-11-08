<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;
use Throwable;

final class View
{
    private array $shared = [];

    public function __construct(private readonly Config $config)
    {
    }

    public function share(string $key, mixed $value): void
    {
        $this->shared[$key] = $value;
    }

    public function shareMany(array $data): void
    {
        $this->shared = array_merge($this->shared, $data);
    }

    public function render(string $template, array $data = [], ?string $layout = 'layouts.app'): string
    {
        $data = array_merge($this->shared, $data);

        $content = $this->renderFile($template, $data);

        if ($layout === null) {
            return $content;
        }

        $data['content'] = $content;

        return $this->renderFile($layout, $data);
    }

    private function renderFile(string $template, array $data): string
    {
        $viewsPath = (string) $this->config->get('app.views', base_path('resources/views'));
        $relative = str_replace(['.', '\\', '/'], DIRECTORY_SEPARATOR, trim($template, './\\'));
        $file = $viewsPath . DIRECTORY_SEPARATOR . $relative . '.php';

        if (!is_file($file)) {
            throw new RuntimeException(sprintf('View "%s" não encontrada.', $template));
        }

        extract($data, EXTR_SKIP);

        ob_start();

        try {
            require $file;

            return (string) ob_get_clean();
        } catch (Throwable $exception) {
            ob_end_clean();

            throw $exception;
        }
    }
}

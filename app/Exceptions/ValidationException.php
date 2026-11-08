<?php

declare(strict_types=1);

namespace App\Exceptions;

final class ValidationException extends HttpException
{
    public function __construct(
        private readonly array $errors,
        string $message = 'Dados inválidos.'
    ) {
        parent::__construct($message, 422);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

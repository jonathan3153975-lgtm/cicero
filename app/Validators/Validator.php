<?php

declare(strict_types=1);

namespace App\Validators;

final class Validator
{
    private array $errors = [];

    public function __construct(private readonly array $data)
    {
    }

    public function required(string $field, ?string $message = null): self
    {
        if ($this->value($field) === '') {
            $this->addError($field, $message ?? 'Este campo é obrigatório.');
        }

        return $this;
    }

    public function email(string $field, ?string $message = null): self
    {
        $value = $this->value($field);

        if ($value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $this->addError($field, $message ?? 'Informe um e-mail válido.');
        }

        return $this;
    }

    public function minLength(string $field, int $length, ?string $message = null): self
    {
        $value = $this->value($field);

        if ($value !== '' && mb_strlen($value) < $length) {
            $this->addError($field, $message ?? sprintf('Este campo deve ter ao menos %d caracteres.', $length));
        }

        return $this;
    }

    public function maxLength(string $field, int $length, ?string $message = null): self
    {
        $value = $this->value($field);

        if (mb_strlen($value) > $length) {
            $this->addError($field, $message ?? sprintf('Este campo deve ter no máximo %d caracteres.', $length));
        }

        return $this;
    }

    public function in(string $field, array $allowed, ?string $message = null): self
    {
        $value = $this->value($field);

        if ($value !== '' && !in_array($value, $allowed, true)) {
            $this->addError($field, $message ?? 'Valor inválido.');
        }

        return $this;
    }

    public function cnpj(string $field, ?string $message = null): self
    {
        $value = preg_replace('/\D/', '', $this->value($field)) ?? '';

        if ($value !== '' && !self::isValidCnpj($value)) {
            $this->addError($field, $message ?? 'Informe um CNPJ válido.');
        }

        return $this;
    }

    public static function isValidCnpj(string $cnpj): bool
    {
        $cnpj = preg_replace('/\D/', '', $cnpj) ?? '';

        if (mb_strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj) === 1) {
            return false;
        }

        $first = self::cnpjDigit(substr($cnpj, 0, 12), [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);
        $second = self::cnpjDigit(substr($cnpj, 0, 13), [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);

        return $first === (int) $cnpj[12] && $second === (int) $cnpj[13];
    }

    private static function cnpjDigit(string $base, array $weights): int
    {
        $sum = 0;

        foreach ($weights as $index => $weight) {
            $sum += (int) $base[$index] * $weight;
        }

        $remainder = $sum % 11;

        return $remainder < 2 ? 0 : 11 - $remainder;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    private function value(string $field): string
    {
        $value = $this->data[$field] ?? null;

        if (is_string($value)) {
            return trim($value);
        }

        if ($value === null) {
            return '';
        }

        return (string) $value;
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
}

<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Minimal rule-based validator. Usage:
 *   $v = new Validator($data);
 *   $v->required('name', 'Name')->maxLength('name', 150, 'Name');
 *   if ($v->fails()) { $errors = $v->errors(); }
 */
final class Validator
{
    private array $errors = [];

    public function __construct(private array $data)
    {
    }

    public function required(string $key, string $label): self
    {
        $value = $this->data[$key] ?? null;
        if ($value === null || (is_string($value) && trim($value) === '')) {
            $this->errors[$key][] = "{$label} is required.";
        }
        return $this;
    }

    public function maxLength(string $key, int $max, string $label): self
    {
        $value = $this->data[$key] ?? '';
        if (is_string($value) && mb_strlen($value) > $max) {
            $this->errors[$key][] = "{$label} must be {$max} characters or fewer.";
        }
        return $this;
    }

    public function email(string $key, string $label): self
    {
        $value = $this->data[$key] ?? '';
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$key][] = "{$label} must be a valid email address.";
        }
        return $this;
    }

    public function url(string $key, string $label): self
    {
        $value = $this->data[$key] ?? '';
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->errors[$key][] = "{$label} must be a valid URL.";
        }
        return $this;
    }

    public function numeric(string $key, string $label): self
    {
        $value = $this->data[$key] ?? '';
        if ($value !== '' && !is_numeric($value)) {
            $this->errors[$key][] = "{$label} must be a number.";
        }
        return $this;
    }

    public function min(string $key, float $min, string $label): self
    {
        $value = $this->data[$key] ?? null;
        if ($value !== null && is_numeric($value) && (float) $value < $min) {
            $this->errors[$key][] = "{$label} must be at least {$min}.";
        }
        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstErrorsFlat(): array
    {
        return array_map(static fn (array $messages) => $messages[0], $this->errors);
    }
}

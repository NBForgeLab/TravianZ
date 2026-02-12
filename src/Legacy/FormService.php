<?php

declare(strict_types=1);

namespace App\Legacy;

final class FormService
{
    /** @var array<string, string> */
    private array $errors = [];
    /** @var array<string, string> */
    public array $values = [];
    private int $errorCount = 0;

    public function __construct(FormStore $store)
    {
        $this->errors = $store->getErrorArray();
        $this->values = $store->getValueArray();
        $this->errorCount = count($this->errors);
        $store->clear();
    }

    public function addError(string $field, string $error): void
    {
        $this->errors[$field] = $error;
        $this->errorCount = count($this->errors);
    }

    public function getError(string $field): string
    {
        return $this->errors[$field] ?? '';
    }

    public function getValue(string $field): string
    {
        return $this->values[$field] ?? '';
    }

    public function setValue(string $field, string $value): void
    {
        $this->values[$field] = $value;
    }

    public function getDiff(string $field, string $cookie): string
    {
        $v = $this->values[$field] ?? null;
        if ($v !== null && $v !== $cookie) {
            return $v;
        }
        return $cookie;
    }

    public function getRadio(string $field, string $value): string
    {
        $v = $this->values[$field] ?? null;
        return ($v !== null && $v === $value) ? 'checked' : '';
    }

    public function returnErrors(): int
    {
        return $this->errorCount;
    }

    /**
     * @return array<string, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}

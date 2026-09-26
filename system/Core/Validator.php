<?php

declare(strict_types=1);

namespace System\Core;

final class Validator
{
    private array $errors = [];

    public function __construct(
        private readonly array $data,
        private readonly array $rules
    ) {
        $this->validate();
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function passes(): bool
    {
        return !$this->fails();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function first(?string $field = null): ?string
    {
        if ($field !== null) {
            return $this->errors[$field][0] ?? null;
        }

        foreach ($this->errors as $messages) {
            if ($messages !== []) {
                return $messages[0];
            }
        }

        return null;
    }

    private function validate(): void
    {
        foreach ($this->rules as $field => $ruleSet) {
            $value = $this->data[$field] ?? null;
            $rules = is_string($ruleSet) ? explode('|', $ruleSet) : (array) $ruleSet;

            foreach ($rules as $rule) {
                $this->applyRule((string) $field, $value, $rule);
            }
        }
    }

    private function applyRule(string $field, mixed $value, string $rule): void
    {
        [$name, $argument] = array_pad(explode(':', $rule, 2), 2, null);
        $empty = $value === null || $value === '';

        if ($name !== 'required' && $empty) {
            return;
        }

        $message = match ($name) {
            'required' => $empty ? "The {$field} field is required." : null,
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) === false ? "The {$field} must be a valid email address." : null,
            'url' => filter_var($value, FILTER_VALIDATE_URL) === false ? "The {$field} must be a valid URL." : null,
            'numeric' => !is_numeric($value) ? "The {$field} must be numeric." : null,
            'integer' => filter_var($value, FILTER_VALIDATE_INT) === false ? "The {$field} must be an integer." : null,
            'min' => $this->lengthOrValue($value) < (float) $argument ? "The {$field} must be at least {$argument}." : null,
            'max' => $this->lengthOrValue($value) > (float) $argument ? "The {$field} may not be greater than {$argument}." : null,
            'same' => ($this->data[$argument ?? ''] ?? null) !== $value ? "The {$field} must match {$argument}." : null,
            'in' => !in_array((string) $value, explode(',', (string) $argument), true) ? "The selected {$field} is invalid." : null,
            'unique' => $this->unique($value, (string) $argument) ? null : "The {$field} has already been taken.",
            'exists' => $this->exists($value, (string) $argument) ? null : "The selected {$field} is invalid.",
            default => null,
        };

        if ($message !== null) {
            $this->errors[$field][] = $message;
        }
    }

    private function lengthOrValue(mixed $value): float
    {
        return is_string($value) ? mb_strlen($value) : (float) $value;
    }

    private function unique(mixed $value, string $definition): bool
    {
        [$table, $column] = array_pad(explode(',', $definition, 2), 2, null);
        if (!$table || !$column) {
            return true;
        }

        $sql = 'SELECT 1 FROM ' . $this->identifier($table) . ' WHERE ' . $this->identifier($column) . ' = :value LIMIT 1';
        return !$this->queryExists($sql, ['value' => $value]);
    }

    private function exists(mixed $value, string $definition): bool
    {
        [$table, $column] = array_pad(explode(',', $definition, 2), 2, null);
        if (!$table || !$column) {
            return false;
        }

        $sql = 'SELECT 1 FROM ' . $this->identifier($table) . ' WHERE ' . $this->identifier($column) . ' = :value LIMIT 1';
        return $this->queryExists($sql, ['value' => $value]);
    }

    private function queryExists(string $sql, array $params): bool
    {
        $statement = db()->prepare($sql);
        $statement->execute($params);
        return $statement->fetchColumn() !== false;
    }

    private function identifier(string $value): string
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $value)) {
            throw new \InvalidArgumentException('Invalid validation database identifier.');
        }
        return $value;
    }
}

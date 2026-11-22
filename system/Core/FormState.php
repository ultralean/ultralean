<?php

declare(strict_types=1);

namespace System\Core;

use LengthException;
use RuntimeException;

final class FormState
{
    private const SESSION_KEY = '_form_states';
    private const TTL = 600;
    private const MAX_RECORDS = 10;
    private const MAX_BYTES = 65536;

    private ?array $data = null;
    private bool $loaded = false;

    public function __construct(
        private readonly Session $session,
        private readonly ?string $identifier,
        private readonly string $formName = 'default'
    ) {
        self::validateFormName($formName);
    }

    /**
     * Store filtered form input.
     */
    public static function preserve(
        Session $session,
        string $identifier,
        string $formName,
        array $data
    ): void {
        self::validateFormName($formName);

        if (!preg_match('/^[a-f0-9]{64}$/D', $identifier)) {
            throw new RuntimeException('Invalid form identifier.');
        }

        $data = self::filter($data);

        $encoded = json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($encoded === false) {
            throw new RuntimeException('Unable to encode old form input.');
        }

        if (strlen($encoded) > self::MAX_BYTES) {
            throw new LengthException(
                'Old form input exceeds the 64 KB limit.'
            );
        }

        $records = $session->get(self::SESSION_KEY, []);

        if (!is_array($records)) {
            $records = [];
        }

        $now = time();

        self::removeExpired($records, $now);

        $key = hash('sha256', $identifier);

        $records[$key] = [
            'name' => $formName,
            'expires' => $now + self::TTL,
            'data' => $data,
        ];

        // Keep only the newest records.
        while (count($records) > self::MAX_RECORDS) {
            array_shift($records);
        }

        $session->set(self::SESSION_KEY, $records);
    }

    /**
     * Retrieve one field.
     */
    public function old(
        string $key,
        mixed $default = null
    ): mixed {
        $data = $this->all();

        return $data[$key] ?? $default;
    }

    /**
     * Retrieve all old form input.
     */
    public function all(): array
    {
        if (!$this->loaded) {
            $this->load();
        }

        return $this->data ?? [];
    }

    /**
     * Load and consume only this form's record.
     */
    private function load(): void
    {
        $this->loaded = true;
        $this->data = [];

        if ($this->identifier === null) {
            return;
        }

        if (!preg_match('/^[a-f0-9]{64}$/D', $this->identifier)) {
            return;
        }

        $records = $this->session->get(self::SESSION_KEY, []);

        if (!is_array($records)) {
            return;
        }

        $now = time();
        self::removeExpired($records, $now);

        $key = hash('sha256', $this->identifier);

        if (!isset($records[$key])) {
            $this->session->set(self::SESSION_KEY, $records);
            return;
        }

        $record = $records[$key];

        // Do not consume another form's record.
        if (
            !is_array($record) ||
            ($record['name'] ?? null) !== $this->formName
        ) {
            unset($records[$key]);
            $this->session->set(self::SESSION_KEY, $records);
            return;
        }

        if (
            !is_array($record['data'] ?? null) ||
            !is_int($record['expires'] ?? null) ||
            $record['expires'] <= $now
        ) {
            unset($records[$key]);
            $this->session->set(self::SESSION_KEY, $records);
            return;
        }

        $this->data = $record['data'];

        // One-time consumption.
        unset($records[$key]);

        $this->session->set(self::SESSION_KEY, $records);
    }

    /**
     * Remove expired and malformed records.
     */
    private static function removeExpired(
        array &$records,
        int $now
    ): void {
        foreach ($records as $key => $record) {
            if (
                !is_array($record) ||
                !isset($record['name'], $record['expires'], $record['data']) ||
                !is_string($record['name']) ||
                !is_int($record['expires']) ||
                !is_array($record['data']) ||
                $record['expires'] <= $now
            ) {
                unset($records[$key]);
            }
        }
    }

    /**
     * Remove sensitive and non-restorable values recursively.
     */
    private static function filter(array $data): array
    {
        $filtered = [];

        foreach ($data as $key => $value) {
            $name = strtolower((string) $key);

            if (
                $name === '_token' ||
                $name === '_form_key' ||
                $name === '_form_name' ||
                preg_match(
                    '/password|passwd|secret|token|api[_-]?key|authorization/i',
                    $name
                )
            ) {
                continue;
            }

            if (is_array($value)) {
                $filtered[$key] = self::filter($value);
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /**
     * Validate developer-defined form names.
     */
    public static function validateFormName(string $name): void
    {
        if (!preg_match('/^[A-Za-z0-9_.-]{1,64}$/D', $name)) {
            throw new RuntimeException('Invalid form name.');
        }
    }
}

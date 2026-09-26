<?php

declare(strict_types=1);

namespace System\Core;

final class Request
{
    private ?array $json = null;

    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = explode('?', $uri, 2)[0];
        $path = explode('#', $path, 2)[0] ?: '/';
        return $path;
    }

    public function uri(): string
    {
        return (string) ($_SERVER['REQUEST_URI'] ?? '/');
    }

    public function queryString(): string
    {
        $parts = explode('?', $this->uri(), 2);
        return isset($parts[1]) ? explode('#', $parts[1], 2)[0] : '';
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $_GET;
        }

        return $_GET[$key] ?? $default;
    }

    public function input(?string $key = null, mixed $default = null): mixed
    {
        $data = $_POST;

        if ($this->method() !== 'GET' && str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
            $data = $this->json();
        }

        if ($key === null) {
            return $data;
        }

        return $data[$key] ?? $default;
    }

    public function json(): array
    {
        if ($this->json !== null) {
            return $this->json;
        }

        $body = file_get_contents('php://input');
        $decoded = json_decode($body ?: '{}', true);
        return $this->json = is_array($decoded) ? $decoded : [];
    }

    public function all(): array
    {
        return array_replace($this->query(), $this->input());
    }

    public function header(string $name, mixed $default = null): mixed
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$key] ?? $default;
    }

    public function ip(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public function wantsJson(): bool
    {
        $accept = strtolower((string) $this->header('Accept', ''));
        return str_contains($accept, 'application/json')
            || str_starts_with($this->path(), '/api/');
    }

    public function isAjax(): bool
    {
        return strtolower((string) $this->header('X-Requested-With', '')) === 'xmlhttprequest';
    }
}

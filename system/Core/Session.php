<?php

declare(strict_types=1);

namespace System\Core;

final class Session
{
    private bool $started = false;

    public function start(): void
    {
        if ($this->started || session_status() === PHP_SESSION_ACTIVE) {
            $this->started = true;
            return;
        }

        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
            'cookie_secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'use_strict_mode' => true,
        ]);

        $this->started = true;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->start();
        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, mixed $value): static
    {
        $this->start();
        $_SESSION[$key] = $value;
        return $this;
    }

    public function has(string $key): bool
    {
        $this->start();
        return array_key_exists($key, $_SESSION);
    }

    public function forget(string $key): static
    {
        $this->start();
        unset($_SESSION[$key]);
        return $this;
    }

    public function all(): array
    {
        $this->start();
        return $_SESSION;
    }

    public function flash(string $key, mixed $value): static
    {
        $this->set('_flash.' . $key, $value);
        return $this;
    }

    public function pullFlash(string $key, mixed $default = null): mixed
    {
        $value = $this->get('_flash.' . $key, $default);
        $this->forget('_flash.' . $key);
        return $value;
    }

    public function regenerate(bool $deleteOldSession = true): bool
    {
        $this->start();
        return session_regenerate_id($deleteOldSession);
    }

    public function destroy(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']
            );
        }

        session_destroy();
        $this->started = false;
    }
}

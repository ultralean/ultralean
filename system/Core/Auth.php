<?php

declare(strict_types=1);

namespace System\Core;

use Closure;
use InvalidArgumentException;
use RuntimeException;

final class Auth
{
    private readonly Closure $findEligibleAccount;

    public function __construct(
        private readonly Session $session,
        private readonly string $sessionKey,
        callable $findEligibleAccount,
    ) {
        if (trim($sessionKey) === '') {
            throw new InvalidArgumentException(
                'Authentication session key cannot be empty.'
            );
        }

        $this->findEligibleAccount = Closure::fromCallable(
            $findEligibleAccount
        );
    }

    public function id(): int|string|null
    {
        $id = $this->session->get($this->sessionKey);

        if (is_int($id)) {
            return $id;
        }

        if (is_string($id) && $id !== '') {
            return $id;
        }

        return null;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function user(): array|object|null
    {
        $id = $this->id();

        if ($id === null) {
            return null;
        }

        $user = ($this->findEligibleAccount)($id);

        if (!is_array($user) && !is_object($user)) {
            $this->logout();
            return null;
        }

        return $user;
    }

    public function login(int|string $id): void
    {
        if ((is_string($id) && $id === '') || $id === '') {
            throw new InvalidArgumentException(
                'Authentication ID cannot be empty.'
            );
        }

        $user = ($this->findEligibleAccount)($id);

        if (!is_array($user) && !is_object($user)) {
            throw new RuntimeException(
                'Account does not exist or is not eligible to log in.'
            );
        }

        if (!$this->session->regenerate(true)) {
            throw new RuntimeException(
                'Failed to regenerate the session ID.'
            );
        }

        $this->session->set($this->sessionKey, $id);
    }

    public function logout(): void
    {
        $this->session->forget($this->sessionKey);
        $this->session->regenerate(true);
    }
}

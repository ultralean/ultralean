<?php

declare(strict_types=1);

namespace App\Auth;

use RuntimeException;
use System\Core\Session;

final class Auth
{
    private const SESSION_KEY = '_auth.user_id';

    private ?array $user = null;
    private bool $resolved = false;

    public function __construct(private readonly Session $session)
    {
    }

    public function check(): bool
    {
        return $this->id() !== null;
    }

    public function id(): int|string|null
    {
        $id = $this->session->get(self::SESSION_KEY);
        return is_int($id) || is_string($id) ? $id : null;
    }

    public function user(): ?array
    {
        if ($this->resolved) {
            return $this->user;
        }

        $this->resolved = true;
        $id = $this->id();

        if ($id === null) {
            return null;
        }

        $table = 'users';
        $sql = 'SELECT * FROM ' . $this->safeIdentifier($table) . ' WHERE id = :id LIMIT 1';
        $statement = db()->prepare($sql);
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        if (!is_array($user)) {
            $this->logout(false);
            return null;
        }

        return $this->user = $user;
    }

    public function login(array|object|int|string $user): void
    {
        if (is_array($user)) {
            if (!array_key_exists('id', $user)) {
                throw new RuntimeException('Auth login requires a user id.');
            }
            $id = $user['id'];
            $this->user = $user;
            $this->resolved = true;
        } elseif (is_object($user)) {
            if (!isset($user->id)) {
                throw new RuntimeException('Auth login requires a user id.');
            }
            $id = $user->id;
            $this->user = get_object_vars($user);
            $this->resolved = true;
        } else {
            $id = $user;
            $this->user = null;
            $this->resolved = false;
        }

        $this->session->regenerate(true);
        $this->session->set(self::SESSION_KEY, $id);
    }

    public function logout(bool $regenerate = true): void
    {
        $this->session->forget(self::SESSION_KEY);
        $this->user = null;
        $this->resolved = true;

        if ($regenerate) {
            $this->session->regenerate(true);
        }
    }

    private function safeIdentifier(string $identifier): string
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier)) {
            throw new RuntimeException('Invalid auth users table name.');
        }
        return $identifier;
    }
}

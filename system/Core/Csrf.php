<?php

declare(strict_types=1);

namespace System\Core;

final class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public function __construct(
        private readonly Session $session
    ) {
    }

    public function token(): string
    {
        $token = $this->session->get(self::SESSION_KEY);

        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));

            $this->session->set(self::SESSION_KEY, $token);
        }

        return $token;
    }

    /**
     * Generate CSRF and independent form identifiers.
     *
     * The optional name identifies the logical form on the page.
     */
    public function field(string $formName = 'default'): string
    {
        FormState::validateFormName($formName);

        $csrfToken = htmlspecialchars(
            $this->token(),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );

        $formKey = bin2hex(random_bytes(32));

        $escapedName = htmlspecialchars(
            $formName,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );

        return '<input type="hidden" name="_token" value="' .
            $csrfToken .
            '">' .
            '<input type="hidden" name="_form_key" value="' .
            $formKey .
            '">' .
            '<input type="hidden" name="_form_name" value="' .
            $escapedName .
            '">';
    }

    public function check(?string $token): bool
    {
        if (!is_string($token) || $token === '') {
            return false;
        }

        return hash_equals($this->token(), $token);
    }
}

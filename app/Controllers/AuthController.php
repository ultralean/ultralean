<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use System\Core\Auth;
use System\Core\Controller;
use System\Core\Response;

final class AuthController extends Controller
{
    private ?Auth $auth = null;

    private function authService(): Auth
    {
        return $this->auth ??= $this->createAuth();
    }

    private function createAuth(): Auth
    {
        $config = \config('auth.guards.user');

        if (!is_array($config)) {
            throw new \RuntimeException(
                'User authentication is not configured.'
            );
        }

        return new Auth(
            \session(),
            $config['session_key'],
            static function (int|string $id): ?array {
                $user = User::where('id', '=', $id)->first();

                if (
                    $user === null ||
                    ($user['status'] ?? null) !== 'active'
                ) {
                    return null;
                }

                return $user;
            }
        );
    }

    public function login(): Response
    {
        if ($this->authService()->check()) {
            return $this->redirect('/account');
        }

        return $this->view('auth/login', [
            'title' => 'User Login',
            'error' => session()->pullFlash('error'),
        ]);
    }

    public function authenticate(): Response
    {
        $email = trim(
            (string) $this->request()->input('email', '')
        );

        $password = (string) $this->request()->input('password', '');

        $user = User::where('email', '=', $email)->first();

        if (
            $user === null ||
            ($user['status'] ?? null) !== 'active' ||
            !password_verify(
                $password,
                (string) ($user['password_hash'] ?? '')
            )
        ) {
            session()->flash(
                'error',
                'The email or password is incorrect.'
            );

            return $this->redirect('/login');
        }

        $this->authService()->login($user['id']);

        return $this->redirect('/account');
    }

    public function logout(): Response
    {
        $this->authService()->logout();

        return $this->redirect('/login');
    }
}

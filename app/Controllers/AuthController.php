<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use System\Core\Controller;
use System\Core\Response;

final class AuthController extends Controller
{
    public function login(): Response
    {
        if (auth()->check()) {
            return $this->redirect('/admin');
        }

        return $this->view('auth/login', [
            'title' => 'Admin Login',
            'error' => session()->pullFlash('error'),
        ]);
    }

    public function authenticate(): Response
    {
        $username = trim((string) $this->request()->input('username', ''));
        $password = (string) $this->request()->input('password', '');

        $user = User::where('username', '=', $username)->first();

        if ($user === null || !password_verify($password, (string) ($user['password_hash'] ?? ''))) {
            session()->flash('error', 'The username or password is incorrect.');
            return $this->redirect('/login');
        }

        auth()->login($user);
        return $this->redirect('/admin');
    }

    public function logout(): Response
    {
        auth()->logout();
        return $this->redirect('/login');
    }
}

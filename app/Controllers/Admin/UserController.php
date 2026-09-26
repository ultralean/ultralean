<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\User;
use System\Core\Controller;
use System\Core\Response;

final class UserController extends Controller
{
    public function show(string $id): Response
    {
        return $this->json(['id' => $id]);
    }

    public function profile(): Response
    {
        $user = auth()->user();
        if ($user === null) {
            return $this->redirect('/login');
        }

        return $this->view('admin/profile', [
            'title' => 'Account Settings',
            'user' => $user,
            'success' => session()->pullFlash('success'),
            'error' => session()->pullFlash('error'),
        ]);
    }

    public function update(): Response
    {
        $user = auth()->user();
        if ($user === null) {
            return $this->redirect('/login');
        }

        $data = [
            'name' => trim((string) $this->request()->input('name', '')),
            'email' => trim((string) $this->request()->input('email', '')),
            'username' => trim((string) $this->request()->input('username', '')),
        ];

        $validator = validate($data, [
            'name' => 'required|max:100',
            'username' => 'required|max:100',
            'email' => 'required|email|max:190',
        ]);

        if ($validator->fails()) {
            session()->flash('error', $validator->first());
            return $this->redirect('/admin/account');
        }

        $existing = User::where('username', '=', $data['username'])->first();
        if ($existing !== null && (string) $existing['id'] !== (string) $user['id']) {
            session()->flash('error', 'That username is already in use.');
            return $this->redirect('/admin/account');
        }

        $existing = User::where('email', '=', $data['email'])->first();
        if ($existing !== null && (string) $existing['id'] !== (string) $user['id']) {
            session()->flash('error', 'That email address is already in use.');
            return $this->redirect('/admin/account');
        }

        $password = (string) $this->request()->input('password', '');
        $passwordConfirmation = (string) $this->request()->input('password_confirmation', '');

        if ($password !== '' || $passwordConfirmation !== '') {
            if (strlen($password) < 8) {
                session()->flash('error', 'The new password must contain at least 8 characters.');
                return $this->redirect('/admin/account');
            }

            if ($password !== $passwordConfirmation) {
                session()->flash('error', 'The new password and confirmation do not match.');
                return $this->redirect('/admin/account');
            }

            $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $data['updated_at'] = now();
        User::update((int) $user['id'], $data);

        // Refresh the cached authenticated user without changing the session id.
        auth()->login(User::find((int) $user['id']) ?? $user);

        session()->flash('success', 'Your account details were updated successfully.');
        return $this->redirect('/admin/account');
    }
}

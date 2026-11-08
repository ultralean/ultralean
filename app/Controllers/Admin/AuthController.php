<?php declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\AdminAccount;
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
        $config = \config('auth.guards.admin');

        if (!is_array($config)) {
            throw new \RuntimeException(
                'Admin authentication is not configured.'
            );
        }

        return new Auth(
            \session(),
            $config['session_key'],
            static function (int|string $id): ?array {
                $admin = AdminAccount::where('id', '=', $id)->first();

                if (
                    $admin === null ||
                    ($admin['status'] ?? null) !== 'active'
                ) {
                    return null;
                }

                return $admin;
            }
        );
    }

    public function login(): Response
    {
        if ($this->authService()->check()) {
            return $this->redirect(url('admin.home'));
        }

        return $this->view('admin/auth/admin_login', [
            'title' => 'Admin Login',
            'error' => session()->pullFlash('error'),
        ]);
    }

    public function authenticate(): Response
    {
        $username = trim(
            (string) $this->request()->input('username', '')
        );

        $password = (string) $this->request()->input('password', '');

        $admin = AdminAccount::where(
            'username',
            '=',
            $username
        )->first();

        if (
            $admin === null ||
            ($admin['status'] ?? null) !== 'active' ||
            !password_verify(
                $password,
                (string) ($admin['password_hash'] ?? '')
            )
        ) {
            session()->flash(
                'error',
                'The username or password is incorrect.'
            );

            return $this->redirect(url('admin.login'));
        }

        $this->authService()->login($admin['id']);

        return $this->redirect(url('admin.home'));
    }

    public function logout(): Response
    {
        $this->authService()->logout();

        return $this->redirect(url('admin.login'));
    }
}

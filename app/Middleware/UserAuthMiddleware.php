<?php

declare(strict_types=1);

namespace App\Middleware;

use System\Core\Request;
use System\Core\Response;

final class UserAuthMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (!auth('user')->check()) {
            return Response::redirect(url('login'));
        }

        return $next();
    }
}

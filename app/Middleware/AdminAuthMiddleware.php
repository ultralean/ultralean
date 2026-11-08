<?php

declare(strict_types=1);

namespace App\Middleware;

use System\Core\Request;
use System\Core\Response;

final class AdminAuthMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (!auth('admin')->check()) {
            return Response::redirect(url('admin.login'));
        }

        return $next();
    }
}

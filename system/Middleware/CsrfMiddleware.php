<?php

declare(strict_types=1);

namespace System\Middleware;

use System\Core\Request;
use System\Core\Response;
use System\Core\Csrf;

final class CsrfMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $token = $request->input('_token') ?? $request->header('X-CSRF-TOKEN');

            if (!csrf()->check(is_string($token) ? $token : null)) {
                throw new \RuntimeException('The security token is invalid or has expired.', 419);
            }
        }

        return $next();
    }
}

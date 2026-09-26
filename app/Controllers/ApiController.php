<?php

declare(strict_types=1);

namespace App\Controllers;

use System\Core\Controller;
use System\Core\Response;

final class ApiController extends Controller
{
    public function health(): Response
    {
        return Response::json([
            'ok' => true,
            'name' => (string) config('app.name', 'ultralean'),
            'php' => PHP_VERSION,
            'time' => \System\Core\Clock::nowUtc()->iso8601(),
        ]);
    }
}

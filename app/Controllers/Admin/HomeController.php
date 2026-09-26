<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use System\Core\Controller;
use System\Core\Response;

final class HomeController extends Controller
{
    public function index(): Response
    {
        return $this->view('admin/home', [
            'title' => 'Dashboard',
            'user' => auth()->user(),
            'elapsed' => number_format((hrtime(true) - UL_START_TIME) / 1_000_000, 3),
            'memory' => number_format(memory_get_usage(false) / 1024 / 1024, 2),
        ]);
    }
}

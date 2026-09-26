<?php

declare(strict_types=1);

namespace App\Controllers;

use System\Core\Controller;
use System\Core\Response;

final class HomeController extends Controller
{
    public function index(): Response
    {
        $elapsed = (hrtime(true) - UL_START_TIME) / 1_000_000;
        $memory = memory_get_usage(false) / 1024 / 1024;

        return $this->view('home', [
            'title' => 'ultralean',
            'elapsed' => number_format($elapsed, 3),
            'memory' => number_format($memory, 2),
            'php' => PHP_VERSION,
        ]);
    }

    public function about(): Response
    {
        return $this->view('about', ['title' => 'About ultralean']);
    }
}

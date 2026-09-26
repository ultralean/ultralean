<?php

declare(strict_types=1);

namespace System\Core;

abstract class Controller
{
    protected function request(): Request
    {
        return App::request();
    }

    protected function view(string $view, array $data = []): Response
    {
        return Response::make(View::render($view, $data));
    }

    protected function redirect(string $url, int $status = 302): Response
    {
        return Response::redirect($url, $status);
    }

    protected function json(mixed $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }
}

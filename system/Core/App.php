<?php

declare(strict_types=1);

namespace System\Core;

final class App
{
    private static ?Request $request = null;

    public static function run(): void
    {
        $timezone = (string) config('app.timezone', 'UTC');
        date_default_timezone_set($timezone);

        error_reporting(E_ALL);
        ini_set('display_errors', '0');

        ErrorHandler::register();

        $migrationFile = public_path('migrate.php');
        if (strtolower((string) config('app.env', 'development')) === 'production'
            && (bool) config('migrate.production_block', true)
            && is_file($migrationFile)) {
            throw new \RuntimeException(
                'The migration interface is still present. Delete or move public/migrate.php before using the site in production.',
                503
            );
        }
        self::$request = new Request();

        $routeFiles = [
            app_path('Routes/web.php'),
            app_path('Routes/api.php'),
        ];

        foreach ($routeFiles as $routes) {
            if (is_file($routes)) {
                require $routes;
            }
        }

        Router::dispatch(self::$request)->send();
    }

    public static function request(): Request
    {
        return self::$request ??= new Request();
    }
}

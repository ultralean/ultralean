<?php

declare(strict_types=1);

const UL_ROOT = __DIR__ . '/..';

if (!defined('UL_START_TIME')) {
    define('UL_START_TIME', hrtime(true));
}

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'System\\' => UL_ROOT . '/system/',
        'App\\'    => UL_ROOT . '/app/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        $relative = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relative) . '.php';

        if (is_file($file)) {
            require $file;
        }

        return;
    }
});

function base_path(string $path = ''): string
{
    return UL_ROOT . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

function app_path(string $path = ''): string
{
    return base_path('app' . ($path !== '' ? '/' . ltrim($path, '/') : ''));
}

function public_path(string $path = ''): string
{
    return base_path('public' . ($path !== '' ? '/' . ltrim($path, '/') : ''));
}

function storage_path(string $path = ''): string
{
    return app_path('Storage' . ($path !== '' ? '/' . ltrim($path, '/') : ''));
}

function config(string $key, mixed $default = null): mixed
{
    static $config = [];

    $parts = explode('.', $key);
    $file = array_shift($parts);

    if (!isset($config[$file])) {
        $path = app_path('Config/' . $file . '.php');
        $config[$file] = is_file($path) ? require $path : [];
    }

    $value = $config[$file];

    foreach ($parts as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }

    return $value;
}

require base_path('system/Helpers/helpers.php');

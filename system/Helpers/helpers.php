<?php

declare(strict_types=1);

function str_slug(string $value): string
{
    $value = preg_replace('/[^a-z0-9]+/i', '_', trim($value)) ?? '';
    return trim(strtolower($value), '_');
}

function db(?string $name = null): \PDO
{
    return \System\Core\Database::connection($name);
}

function request(): \System\Core\Request
{
    return \System\Core\App::request();
}

function url(string $name, array $params = []): string
{
    return \System\Core\Router::url($name, $params);
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function now(?string $database = null): string
{
    return \System\Core\Clock::nowUtc()->format('Y-m-d\TH:i:s\Z');
}

function session(): \System\Core\Session
{
    static $session;
    return $session ??= new \System\Core\Session();
}

function auth(): \App\Auth\Auth
{
    static $auth;
    return $auth ??= new \App\Auth\Auth(session());
}

function csrf(): \System\Core\Csrf
{
    static $csrf;
    return $csrf ??= new \System\Core\Csrf(session());
}

function csrf_token(): string
{
    return csrf()->token();
}

function csrf_field(): string
{
    return csrf()->field();
}

function validate(array $data, array $rules): \System\Core\Validator
{
    return new \System\Core\Validator($data, $rules);
}

function local_now(): string
{
    return \System\Core\Clock::now()->format('Y-m-d H:i:sP');
}

function to_utc(string|\DateTimeInterface $value, ?string $fromTimezone = null): string
{
    if ($fromTimezone !== null) {
        $date = $value instanceof \DateTimeInterface
            ? new \DateTimeImmutable($value->format('Y-m-d H:i:s.u'), new \DateTimeZone($fromTimezone))
            : new \DateTimeImmutable($value, new \DateTimeZone($fromTimezone));
        return $date->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');
    }

    return \System\Core\Clock::toUtc($value)->format('Y-m-d\TH:i:s\Z');
}

function to_local(string|\DateTimeInterface $value, ?string $timezone = null, ?string $format = null): string
{
    $date = \System\Core\Clock::toApplicationTimezone($value);
    if ($timezone !== null) {
        $date = $date->setTimezone(new \DateTimeZone($timezone));
    }
    return $date->format($format ?: 'Y-m-d H:i:sP');
}

function convert_timezone(string|\DateTimeInterface $value, string $from, string $to, ?string $format = null): string
{
    $date = $value instanceof \DateTimeInterface
        ? new \DateTimeImmutable($value->format('Y-m-d H:i:s.u'), new \DateTimeZone($from))
        : new \DateTimeImmutable($value, new \DateTimeZone($from));
    return $date->setTimezone(new \DateTimeZone($to))->format($format ?: 'Y-m-d H:i:sP');
}

function timestamp(string|\DateTimeInterface $value): \System\Core\Clock
{
    return \System\Core\Clock::from($value);
}

function db_fetch(string $sql, array $params = [], ?string $name = null): ?array
{
    return \System\Core\Database::fetch($sql, $params, $name);
}

function db_fetch_all(string $sql, array $params = [], ?string $name = null): array
{
    return \System\Core\Database::fetchAll($sql, $params, $name);
}

function db_value(string $sql, array $params = [], ?string $name = null): mixed
{
    return \System\Core\Database::value($sql, $params, $name);
}

function db_execute(string $sql, array $params = [], ?string $name = null): int
{
    return \System\Core\Database::execute($sql, $params, $name);
}

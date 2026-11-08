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

/**
 * @throws \InvalidArgumentException
 * @throws \RuntimeException
 * 
 * This function quotes an SQL identifier (e.g., table or column name) based on the database driver.
 * It ensures that the identifier is valid and properly quoted to prevent SQL injection.
 */
function sql_identifier(
    string $identifier,
    ?string $driver = null
): string {
    if (!preg_match(
        '/^[A-Za-z_][A-Za-z0-9_]*$/D',
        $identifier
    )) {
        throw new \InvalidArgumentException(
            'Invalid SQL identifier.'
        );
    }

    $driver ??= strtolower(
        (string) db()->getAttribute(\PDO::ATTR_DRIVER_NAME)
    );

    $quote = match ($driver) {
        'mysql' => '`',
        'pgsql', 'sqlite' => '"',
        default => throw new \RuntimeException(
            'Unsupported database driver.'
        ),
    };

    return $quote . $identifier . $quote;
}

/**
 * @throws \InvalidArgumentException
 * @throws \RuntimeException
 *
 * This function quotes a dot-separated SQL identifier path (e.g., "schema.table.column") based on the database driver.
 * It ensures that each part of the path is valid and properly quoted to prevent SQL injection.
 *
 * Important: quoting shop.users correctly does not mean every database supports cross-database queries in that form.
 * MySQL supports database.table; PostgreSQL generally uses schema.table within a connection;
 * SQLite uses attached_database.table. The helper quotes the path but cannot make unsupported database semantics work.
 */
function sql_identifier_path(
    string $identifierPath,
    ?string $connectionName = null
): string {
    $parts = explode('.', $identifierPath);

    if ($parts === []) {
        throw new \InvalidArgumentException(
            'SQL identifier path cannot be empty.'
        );
    }

    $pdo = db($connectionName);

    return implode('.', array_map(
        static function (string $part) use ($pdo): string {
            if (!preg_match(
                '/^[A-Za-z_][A-Za-z0-9_]*$/D',
                $part
            )) {
                throw new \InvalidArgumentException(
                    'Invalid SQL identifier path.'
                );
            }

            $quote = match (strtolower(
                (string) $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME)
            )) {
                'mysql' => '`',
                'pgsql', 'sqlite' => '"',
                default => throw new \RuntimeException(
                    'Unsupported database driver.'
                ),
            };

            return $quote . $part . $quote;
        },
        $parts
    ));
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

/**
 * @param mixed $guard
 * @throws InvalidArgumentException
 * @throws RuntimeException
 * @return System\Core\Auth
 *
 * This function retrieves an instance of the Auth class for the specified guard.
 * It uses the configuration defined in the 'auth' config file to determine the model and session key for the guard.
 * If the guard is not specified, it defaults to the guard defined in the 'defaults' section of the config.
 * The function caches instances of Auth for each guard to avoid creating multiple instances for the same guard.
 */
function auth(?string $guard = null): \System\Core\Auth
{
    static $instances = [];

    $guard ??= (string) config('auth.defaults.guard', 'user');

    if (isset($instances[$guard])) {
        return $instances[$guard];
    }

    $guards = config('auth.guards', []);
    $settings = $guards[$guard] ?? null;

    if (!is_array($settings)) {
        throw new \InvalidArgumentException(
            "Unknown authentication guard: {$guard}"
        );
    }

    $model = $settings['model'] ?? null;
    $sessionKey = $settings['session_key'] ?? null;

    if (
        !is_string($model) ||
        !class_exists($model) ||
        !is_string($sessionKey) ||
        $sessionKey === ''
    ) {
        throw new \RuntimeException(
            "Invalid authentication configuration for guard: {$guard}"
        );
    }

    return $instances[$guard] = new \System\Core\Auth(
        session(),
        $sessionKey,
        static function (int|string $id) use ($model): ?array {
            $account = $model::where('id', '=', $id)->first();

            if (
                $account === null ||
                ($account['status'] ?? null) !== 'active'
            ) {
                return null;
            }

            return $account;
        }
    );
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

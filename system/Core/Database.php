<?php

declare(strict_types=1);

namespace System\Core;

use DateTimeImmutable;
use DateTimeZone;
use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    /** @var array<string, PDO> */
    private static array $connections = [];

    private function __construct() {}

    public static function connection(?string $name = null): PDO
    {
        $name ??= (string) \config('database.default', 'mysql');

        if (isset(self::$connections[$name])) {
            return self::$connections[$name];
        }

        $config = \config('database.connections.' . $name);
        if (!is_array($config)) {
            throw new RuntimeException("Database connection not configured: {$name}");
        }

        $driver = strtolower((string) ($config['driver'] ?? ''));
        $dsn = self::dsn($driver, $config);
        $options = $config['options'] ?? [];
        $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        $options[PDO::ATTR_DEFAULT_FETCH_MODE] = PDO::FETCH_ASSOC;
        $options[PDO::ATTR_EMULATE_PREPARES] = false;

        try {
            $pdo = new PDO($dsn, (string) ($config['username'] ?? ''), (string) ($config['password'] ?? ''), $options);
            self::initializeConnection($pdo, $driver);
        } catch (PDOException $e) {
            throw new RuntimeException("Database connection [{$name}] failed: " . $e->getMessage(), 0, $e);
        }

        return self::$connections[$name] = $pdo;
    }

    public static function fetch(string $sql, array $params = [], ?string $name = null): ?array
    {
        $statement = self::connection($name)->prepare($sql);
        $statement->execute($params);
        $row = $statement->fetch();
        return $row === false ? null : $row;
    }

    public static function fetchAll(string $sql, array $params = [], ?string $name = null): array
    {
        $statement = self::connection($name)->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    public static function value(string $sql, array $params = [], ?string $name = null): mixed
    {
        $statement = self::connection($name)->prepare($sql);
        $statement->execute($params);
        $value = $statement->fetchColumn();
        return $value === false ? null : $value;
    }

    public static function execute(string $sql, array $params = [], ?string $name = null): int
    {
        $statement = self::connection($name)->prepare($sql);
        $statement->execute($params);
        return $statement->rowCount();
    }

    public static function transaction(callable $callback, ?string $name = null): mixed
    {
        $pdo = self::connection($name);
        $pdo->beginTransaction();

        try {
            $result = $callback($pdo);
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Canonical persistence timestamp. ultralean stores timestamps in UTC for
     * every database driver. PHP converts to the application timezone when
     * displaying/processing values for users.
     */
    public static function now(?string $name = null): string
    {
        return (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d\TH:i:s\Z');
    }

    public static function localNow(): string
    {
        $timezone = (string) \config('app.timezone', 'UTC');
        return (new DateTimeImmutable('now', new DateTimeZone($timezone)))->format('Y-m-d H:i:sP');
    }

    private static function dsn(string $driver, array $config): string
    {
        $database = (string) ($config['database'] ?? '');

        return match ($driver) {
            'sqlite' => 'sqlite:' . $database,
            'pgsql' => sprintf('pgsql:host=%s;port=%s;dbname=%s', $config['host'] ?? '127.0.0.1', $config['port'] ?? 5432, $database),
            'mysql' => sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $config['host'] ?? '127.0.0.1', $config['port'] ?? 3306, $database, $config['charset'] ?? 'utf8mb4'),
            default => throw new RuntimeException("Unsupported database driver: {$driver}"),
        };
    }

    private static function initializeConnection(PDO $pdo, string $driver): void
    {
        // ultralean persists timestamps as UTC. Keep DB sessions in UTC too.
        if ($driver === 'pgsql') {
            $pdo->exec("SET TIME ZONE 'UTC'");
            return;
        }

        if ($driver === 'mysql') {
            $pdo->exec("SET time_zone = '+00:00'");
            return;
        }

        if ($driver === 'sqlite') {
            $pdo->exec('PRAGMA foreign_keys = ON');
            $pdo->exec('PRAGMA busy_timeout = 5000');
        }
    }
}

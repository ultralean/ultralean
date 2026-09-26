<?php

declare(strict_types=1);

namespace System\Core;

use InvalidArgumentException;
use RuntimeException;

abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';
    protected static ?string $connection = null;
    protected static bool $timestamps = true;

    public static function find(int|string $id): ?array
    {
        return static::query()->where(static::$primaryKey, '=', $id)->first();
    }

    public static function findRecord(int|string $id): ?ModelRecord
    {
        return static::query()->where(static::$primaryKey, '=', $id)->record();
    }

    public static function where(string $column, mixed $operatorOrValue, mixed $value = null): ModelQuery
    {
        return func_num_args() === 2
            ? static::query()->where($column, '=', $operatorOrValue)
            : static::query()->where($column, (string) $operatorOrValue, $value);
    }

    public static function all(): array
    {
        return static::query()->get();
    }

    public static function create(array $data): string|int
    {
        if (static::$timestamps) {
            $now = Clock::nowUtc()->format('Y-m-d\TH:i:s\Z');
            $data['created_at'] ??= $now;
            $data['updated_at'] ??= $now;
        }
        return static::query()->insert($data);
    }

    public static function update(int|string $id, array $data): int
    {
        if (static::$timestamps) {
            $data['updated_at'] ??= Clock::nowUtc()->format('Y-m-d\TH:i:s\Z');
        }
        return static::query()->where(static::$primaryKey, '=', $id)->update($data);
    }

    public static function delete(int|string $id): int
    {
        return static::query()->where(static::$primaryKey, '=', $id)->delete();
    }

    public static function query(): ModelQuery
    {
        $table = static::$table;
        if ($table === '') {
            throw new RuntimeException(static::class . ' must define protected static $table.');
        }

        return new ModelQuery($table, static::$connection, static::$primaryKey);
    }
}

final class ModelQuery
{
    private array $wheres = [];
    private array $bindings = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private ?string $orderBy = null;
    private string $orderDirection = 'ASC';

    public function __construct(
        private readonly string $table,
        private readonly ?string $connection = null,
        private readonly string $primaryKey = 'id'
    ) {}

    public function where(string $column, mixed $operatorOrValue = '=', mixed $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operatorOrValue;
            $operatorOrValue = '=';
        }

        $operator = strtoupper(trim((string) $operatorOrValue));
        self::identifier($column);
        if (!in_array($operator, ['=', '!=', '<>', '<', '>', '<=', '>=', 'LIKE', 'NOT LIKE'], true)) {
            throw new InvalidArgumentException("Unsupported where operator: {$operator}");
        }

        $key = ':w' . count($this->bindings);
        $this->wheres[] = "{$column} {$operator} {$key}";
        $this->bindings[$key] = $value;
        return $this;
    }

    public function whereIn(string $column, array $values): self
    {
        self::identifier($column);
        if ($values === []) {
            $this->wheres[] = '1 = 0';
            return $this;
        }

        $placeholders = [];
        foreach ($values as $value) {
            $key = ':w' . count($this->bindings);
            $placeholders[] = $key;
            $this->bindings[$key] = $value;
        }

        $this->wheres[] = $column . ' IN (' . implode(', ', $placeholders) . ')';
        return $this;
    }

    public function whereNull(string $column): self
    {
        self::identifier($column);
        $this->wheres[] = $column . ' IS NULL';
        return $this;
    }

    public function whereNotNull(string $column): self
    {
        self::identifier($column);
        $this->wheres[] = $column . ' IS NOT NULL';
        return $this;
    }

    public function exists(): bool
    {
        $sql = 'SELECT 1 FROM ' . self::identifier($this->table)
            . $this->whereSql() . ' LIMIT 1';
        return Database::value($sql, $this->bindings, $this->connection) !== null;
    }

    public function firstOrFail(string $message = 'Record not found.'): array
    {
        $row = $this->first();
        if ($row === null) {
            throw new RuntimeException($message, 404);
        }
        return $row;
    }

    public function sum(string $column): int|float
    {
        return $this->aggregate('SUM', $column);
    }

    public function avg(string $column): int|float|null
    {
        return $this->aggregate('AVG', $column, true);
    }

    public function min(string $column): mixed
    {
        return $this->aggregate('MIN', $column, true);
    }

    public function max(string $column): mixed
    {
        return $this->aggregate('MAX', $column, true);
    }

    private function aggregate(string $function, string $column, bool $nullable = false): mixed
    {
        self::identifier($column);
        $sql = 'SELECT ' . $function . '(' . $column . ') FROM '
            . self::identifier($this->table) . $this->whereSql();
        $value = Database::value($sql, $this->bindings, $this->connection);
        if ($value === null || $value === false) {
            return $nullable ? null : 0;
        }
        return is_numeric($value) ? $value + 0 : $value;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        self::identifier($column);
        $direction = strtoupper($direction);
        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            throw new InvalidArgumentException('Order direction must be ASC or DESC.');
        }
        $this->orderBy = $column;
        $this->orderDirection = $direction;
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = max(0, $limit);
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = max(0, $offset);
        return $this;
    }

    public function first(): ?array
    {
        $this->limit = 1;
        $rows = $this->get();
        return $rows[0] ?? null;
    }

    public function record(): ?ModelRecord
    {
        $row = $this->first();
        return $row === null ? null : new ModelRecord($row);
    }

    public function get(): array
    {
        $sql = 'SELECT * FROM ' . self::identifier($this->table);
        $sql .= $this->whereSql();
        if ($this->orderBy !== null) {
            $sql .= ' ORDER BY ' . self::identifier($this->orderBy) . ' ' . $this->orderDirection;
        }
        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . $this->limit;
        }
        if ($this->offset !== null) {
            $sql .= ' OFFSET ' . $this->offset;
        }

        return Database::fetchAll($sql, $this->bindings, $this->connection);
    }

    public function count(): int
    {
        $sql = 'SELECT COUNT(*) FROM ' . self::identifier($this->table) . $this->whereSql();
        return (int) Database::value($sql, $this->bindings, $this->connection);
    }

    public function update(array $data): int
    {
        if ($data === []) {
            return 0;
        }

        $sets = [];
        $params = $this->bindings;
        foreach ($data as $column => $value) {
            self::identifier((string) $column);
            $key = ':u' . count($params);
            $sets[] = $column . ' = ' . $key;
            $params[$key] = $value;
        }

        if ($this->wheres === []) {
            throw new RuntimeException('Refusing to update without a WHERE clause.');
        }

        $sql = 'UPDATE ' . self::identifier($this->table) . ' SET ' . implode(', ', $sets) . $this->whereSql();
        return Database::execute($sql, $params, $this->connection);
    }

    public function delete(): int
    {
        if ($this->wheres === []) {
            throw new RuntimeException('Refusing to delete without a WHERE clause.');
        }

        $sql = 'DELETE FROM ' . self::identifier($this->table) . $this->whereSql();
        return Database::execute($sql, $this->bindings, $this->connection);
    }

    public function insert(array $data): string|int
    {
        if ($data === []) {
            throw new InvalidArgumentException('Cannot insert an empty record.');
        }

        $columns = [];
        $placeholders = [];
        $params = [];

        foreach ($data as $column => $value) {
            self::identifier((string) $column);
            $key = ':i' . count($params);
            $columns[] = $column;
            $placeholders[] = $key;
            $params[$key] = $value;
        }

        $sql = 'INSERT INTO ' . self::identifier($this->table)
            . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';

        Database::execute($sql, $params, $this->connection);
        return Database::connection($this->connection)->lastInsertId();
    }

    private function whereSql(): string
    {
        return $this->wheres === [] ? '' : ' WHERE ' . implode(' AND ', $this->wheres);
    }

    private static function identifier(string $value): string
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $value)) {
            throw new InvalidArgumentException("Invalid SQL identifier: {$value}");
        }
        return $value;
    }
}


final class ModelRecord implements \ArrayAccess, \JsonSerializable
{
    public function __construct(private array $attributes) {}

    public function __get(string $name): mixed
    {
        if (!array_key_exists($name, $this->attributes)) {
            return null;
        }

        $value = $this->attributes[$name];
        if (is_string($value) && str_ends_with($name, '_at') && $value !== '') {
            return Clock::from($value)->inApplicationTimezone();
        }

        return $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->attributes[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->attributes[] = $value;
            return;
        }
        $this->attributes[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->attributes[$offset]);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function jsonSerialize(): array
    {
        return $this->attributes;
    }
}

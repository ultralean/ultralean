<?php

declare(strict_types=1);

namespace System\Cli;

use PDO;
use System\Core\Database;
use RuntimeException;

final class Migration
{
    private const TABLE = 'ul_migrations';

    public function __construct(
        private readonly string $path,
        private readonly ?string $connection = null
    ) {
    }

    public function status(): array
    {
        $files = $this->migrationFiles();
        $applied = $this->appliedMigrations();
        $rows = [];

        foreach ($files as $file) {
            $name = $file['name'];
            $rows[] = [
                'name' => $name,
                'status' => isset($applied[$name]) ? 'Ran' : 'Pending',
                'batch' => $applied[$name]['batch'] ?? null,
                'file' => $file['file'],
            ];
        }

        return $rows;
    }

    public function migrate(): array
    {
        return $this->withLock(fn() => $this->migrateInternal());
    }

    private function migrateInternal(): array
    {
        $this->ensureRepository();
        $applied = $this->appliedMigrations();
        $batch = $this->nextBatch();
        $ran = [];

        foreach ($this->migrationFiles() as $migration) {
            if (isset($applied[$migration['name']])) {
                continue;
            }

            $this->apply($migration['name'], 'up', $batch);
            $ran[] = $migration['name'];
        }

        return $ran;
    }

    public function rollback(): array
    {
        return $this->withLock(fn() => $this->rollbackInternal());
    }

    private function rollbackInternal(): array
    {
        $this->ensureRepository();
        $pdo = self::connection();
        $batch = $this->latestBatch();

        if ($batch === null) {
            return [];
        }

        $statement = $pdo->prepare(
            'SELECT migration FROM ' . self::TABLE . ' WHERE batch = :batch ORDER BY id DESC'
        );
        $statement->execute(['batch' => $batch]);
        $names = $statement->fetchAll(PDO::FETCH_COLUMN);

        $files = $this->migrationFilesByName();
        $rolledBack = [];

        foreach ($names as $name) {
            if (!isset($files[$name])) {
                throw new RuntimeException("Migration file not found for rollback: {$name}");
            }

            $this->apply($name, 'down');
            $rolledBack[] = $name;
        }

        return $rolledBack;
    }

    public function reset(): array
    {
        return $this->withLock(fn() => $this->resetInternal());
    }

    private function resetInternal(): array
    {
        $this->ensureRepository();
        $pdo = self::connection();
        $statement = $pdo->query(
            'SELECT migration FROM ' . self::TABLE . ' ORDER BY id DESC'
        );
        $names = $statement->fetchAll(PDO::FETCH_COLUMN);
        $files = $this->migrationFilesByName();
        $reset = [];

        foreach ($names as $name) {
            if (!isset($files[$name])) {
                throw new RuntimeException("Migration file not found for reset: {$name}");
            }

            $this->runSql($this->loadSql($files[$name], 'down'));
            $this->forget($name);
            $reset[] = $name;
        }

        return $reset;
    }

    public function fresh(): array
    {
        return $this->withLock(function (): array {
            $this->resetInternal();
            return $this->migrateInternal();
        });
    }

    private function withLock(callable $callback): mixed
    {
        $directory = storage_path('cache');
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create migration lock directory.');
        }

        $handle = fopen($directory . '/migrations.lock', 'c');
        if ($handle === false) {
            throw new RuntimeException('Unable to create migration lock.');
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                throw new RuntimeException('Unable to acquire migration lock.');
            }
            return $callback();
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function ensureRepository(): void
    {
        $driver = $this->driver();

        $sql = match ($driver) {
            'mysql' => 'CREATE TABLE IF NOT EXISTS ' . self::TABLE . ' (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, migration VARCHAR(255) NOT NULL UNIQUE, batch INT NOT NULL)',
            'pgsql' => 'CREATE TABLE IF NOT EXISTS ' . self::TABLE . ' (id BIGSERIAL PRIMARY KEY, migration VARCHAR(255) NOT NULL UNIQUE, batch INTEGER NOT NULL)',
            'sqlite' => 'CREATE TABLE IF NOT EXISTS ' . self::TABLE . ' (id INTEGER PRIMARY KEY AUTOINCREMENT, migration VARCHAR(255) NOT NULL UNIQUE, batch INTEGER NOT NULL)',
            default => throw new RuntimeException("Unsupported database driver: {$driver}"),
        };

        self::connection()->exec($sql);
    }

    private function connection(): PDO
    {
        return Database::connection($this->connection);
    }

    private function driver(): string
    {
        return (string) (\config('database.connections.' . ($this->connection ?? \config('database.default', 'mysql')) . '.driver') ?? 'mysql');
    }

    private function migrationFiles(): array
    {
        if (!is_dir($this->path)) {
            return [];
        }

        $files = glob($this->path . '/*.sql') ?: [];
        $result = [];

        foreach ($files as $file) {
            $name = basename($file, '.sql');

            if (str_ends_with($name, '.mysql') || str_ends_with($name, '.pgsql') || str_ends_with($name, '.sqlite')) {
                continue;
            }

            $result[] = ['name' => $name, 'file' => $file];
        }

        usort($result, static fn(array $a, array $b): int => strcmp($a['name'], $b['name']));
        return $result;
    }

    private function migrationFilesByName(): array
    {
        $files = [];
        $driver = $this->driver();

        foreach ($this->migrationFiles() as $migration) {
            $files[$migration['name']] = $migration['file'];

            $specific = $this->path . '/' . $migration['name'] . '.' . $driver . '.sql';
            if (is_file($specific)) {
                $files[$migration['name']] = $specific;
            }
        }

        return $files;
    }

    private function loadSql(string $name, string $direction): string
    {
        $driver = $this->driver();
        $genericFile = $this->path . '/' . $name . '.sql';
        $specificFile = $this->path . '/' . $name . '.' . $driver . '.sql';
        $file = is_file($specificFile) ? $specificFile : $genericFile;

        if (!is_file($file)) {
            throw new RuntimeException("Migration file not found: {$name}");
        }

        $contents = file_get_contents($file);
        if ($contents === false) {
            throw new RuntimeException("Unable to read migration: {$file}");
        }

        $upMarker = '-- UP';
        $downMarker = '-- DOWN';
        $upPos = stripos($contents, $upMarker);
        $downPos = stripos($contents, $downMarker);

        if ($upPos === false || $downPos === false || $downPos <= $upPos) {
            throw new RuntimeException("Migration must contain -- UP and -- DOWN sections: {$file}");
        }

        $up = trim(substr($contents, $upPos + strlen($upMarker), $downPos - ($upPos + strlen($upMarker))));
        $down = trim(substr($contents, $downPos + strlen($downMarker)));
        $sql = $direction === 'up' ? $up : $down;
        $sql = $this->compilePlaceholders($sql);

        if ($sql === '') {
            throw new RuntimeException("Migration {$direction} section is empty: {$file}");
        }

        return $sql;
    }

    private function compilePlaceholders(string $sql): string
    {
        $pattern = '/\{\{\s*password\(\s*(\'((?:\\.|[^\'])*)\'|"((?:\\.|[^"])*)")\s*\)\s*\}\}/i';

        $result = preg_replace_callback($pattern, static function (array $match): string {
            $raw = $match[1] !== '' ? $match[1] : $match[2];
            $password = str_starts_with($raw, "'")
                ? stripcslashes(substr($raw, 1, -1))
                : stripcslashes(substr($raw, 1, -1));

            if ($password === '') {
                throw new RuntimeException('Migration password placeholder cannot be empty.');
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            if ($hash === false) {
                throw new RuntimeException('Unable to hash migration password.');
            }

            return "'" . str_replace("'", "''", $hash) . "'";
        }, $sql);

        if ($result === null) {
            throw new RuntimeException('Unable to process migration password placeholders.');
        }

        return $result;
    }

    private function apply(string $name, string $direction, ?int $batch = null): void
    {
        $sql = $this->loadSql($name, $direction);

        Database::transaction(function (PDO $pdo) use ($name, $direction, $batch, $sql): void {
            $pdo->exec($sql);

            if ($direction === 'up') {
                $this->record($name, $batch ?? $this->nextBatch());
            } else {
                $this->forget($name);
            }
        });
    }

    private function runSql(string $sql): void
    {
        Database::transaction(static function (PDO $pdo) use ($sql): void {
            $pdo->exec($sql);
        }, $this->connection);
    }

    private function appliedMigrations(): array
    {
        $this->ensureRepository();
        $statement = self::connection()->query(
            'SELECT migration, batch FROM ' . self::TABLE . ' ORDER BY id ASC'
        );

        $result = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[$row['migration']] = ['batch' => (int) $row['batch']];
        }

        return $result;
    }

    private function nextBatch(): int
    {
        $value = self::connection()->query(
            'SELECT MAX(batch) FROM ' . self::TABLE
        )->fetchColumn();

        return ((int) $value) + 1;
    }

    private function latestBatch(): ?int
    {
        $value = self::connection()->query(
            'SELECT MAX(batch) FROM ' . self::TABLE
        )->fetchColumn();

        return $value === false || $value === null ? null : (int) $value;
    }

    private function record(string $name, int $batch): void
    {
        $statement = self::connection()->prepare(
            'INSERT INTO ' . self::TABLE . ' (migration, batch) VALUES (:migration, :batch)'
        );
        $statement->execute(['migration' => $name, 'batch' => $batch]);
    }

    private function forget(string $name): void
    {
        $statement = self::connection()->prepare(
            'DELETE FROM ' . self::TABLE . ' WHERE migration = :migration'
        );
        $statement->execute(['migration' => $name]);
    }
}

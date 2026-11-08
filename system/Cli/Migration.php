<?php

declare(strict_types=1);

namespace System\Cli;

use DateTimeImmutable;
use DateTimeZone;
use PDO;
use RuntimeException;
use System\Core\Database;

final class Migration
{
    private const TABLE = 'ul_migrations';

    public function __construct(
        private readonly string $path,
        private readonly ?string $connection = null
    ) {}

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

        $statement = $this->connection()->prepare(
            'SELECT migration FROM ' . self::TABLE
            . ' WHERE batch = :batch ORDER BY id DESC'
        );

        $batch = $this->latestBatch();

        if ($batch === null) {
            return [];
        }

        $statement->execute(['batch' => $batch]);

        $names = $statement->fetchAll(PDO::FETCH_COLUMN);
        $files = $this->migrationFilesByName();
        $rolledBack = [];

        foreach ($names as $name) {
            if (!isset($files[$name])) {
                throw new RuntimeException(
                    "Migration file not found for rollback: {$name}"
                );
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

        $statement = $this->connection()->query(
            'SELECT migration FROM ' . self::TABLE . ' ORDER BY id DESC'
        );

        $names = $statement->fetchAll(PDO::FETCH_COLUMN);
        $files = $this->migrationFilesByName();
        $reset = [];

        foreach ($names as $name) {
            if (!isset($files[$name])) {
                throw new RuntimeException(
                    "Migration file not found for reset: {$name}"
                );
            }

            // Run DOWN and remove the record in one transaction.
            $this->apply($name, 'down');

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

        if (
            !is_dir($directory)
            && !mkdir($directory, 0775, true)
            && !is_dir($directory)
        ) {
            throw new RuntimeException(
                'Unable to create migration lock directory.'
            );
        }

        $handle = fopen($directory . '/migrations.lock', 'c');

        if ($handle === false) {
            throw new RuntimeException('Unable to create migration lock.');
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                throw new RuntimeException(
                    'Unable to acquire migration lock.'
                );
            }

            return $callback();
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function ensureRepository(): void
    {
        $sql = match ($this->driver()) {
            'mysql' => 'CREATE TABLE IF NOT EXISTS ' . self::TABLE . ' (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                batch INT NOT NULL
            )',

            'pgsql' => 'CREATE TABLE IF NOT EXISTS ' . self::TABLE . ' (
                id BIGSERIAL PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                batch INTEGER NOT NULL
            )',

            'sqlite' => 'CREATE TABLE IF NOT EXISTS ' . self::TABLE . ' (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration VARCHAR(255) NOT NULL UNIQUE,
                batch INTEGER NOT NULL
            )',

            default => throw new RuntimeException(
                'Unsupported database driver: ' . $this->driver()
            ),
        };

        $this->connection()->exec($sql);
    }

    private function connection(): PDO
    {
        return Database::connection($this->connection);
    }

    private function driver(): string
    {
        $name = $this->connection
            ?? config('database.default', 'mysql');

        return (string) config(
            'database.connections.' . $name . '.driver',
            'mysql'
        );
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

            if (
                str_ends_with($name, '.mysql')
                || str_ends_with($name, '.pgsql')
                || str_ends_with($name, '.sqlite')
            ) {
                continue;
            }

            $result[] = [
                'name' => $name,
                'file' => $file,
            ];
        }

        usort(
            $result,
            static fn(array $a, array $b): int =>
                strcmp($a['name'], $b['name'])
        );

        return $result;
    }

    private function migrationFilesByName(): array
    {
        $files = [];
        $driver = $this->driver();

        foreach ($this->migrationFiles() as $migration) {
            $name = $migration['name'];

            $files[$name] = $migration['file'];

            $specific = $this->path . '/'
                . $name . '.' . $driver . '.sql';

            if (is_file($specific)) {
                $files[$name] = $specific;
            }
        }

        return $files;
    }

    private function loadSql(string $name, string $direction): string
    {
        $driver = $this->driver();

        $genericFile = $this->path . '/' . $name . '.sql';
        $specificFile = $this->path . '/'
            . $name . '.' . $driver . '.sql';

        $file = is_file($specificFile)
            ? $specificFile
            : $genericFile;

        if (!is_file($file)) {
            throw new RuntimeException(
                "Migration file not found: {$name}"
            );
        }

        $contents = file_get_contents($file);

        if ($contents === false) {
            throw new RuntimeException(
                "Unable to read migration: {$file}"
            );
        }

        $upMarker = '-- UP';
        $downMarker = '-- DOWN';

        $upPos = stripos($contents, $upMarker);
        $downPos = stripos($contents, $downMarker);

        if (
            $upPos === false
            || $downPos === false
            || $downPos <= $upPos
        ) {
            throw new RuntimeException(
                "Migration must contain -- UP and -- DOWN sections: {$file}"
            );
        }

        $up = trim(substr(
            $contents,
            $upPos + strlen($upMarker),
            $downPos - ($upPos + strlen($upMarker))
        ));

        $down = trim(substr(
            $contents,
            $downPos + strlen($downMarker)
        ));

        $sql = $direction === 'up' ? $up : $down;
        $sql = $this->compilePlaceholders($sql);

        if ($sql === '') {
            throw new RuntimeException(
                "Migration {$direction} section is empty: {$file}"
            );
        }

        return $sql;
    }

    private function compilePlaceholders(string $sql): string
    {
        $pattern = <<<'REGEX'
~\{\{\s*([a-z_][a-z0-9_]*)\s*\(\s*(?:'((?:''|[^'])*)'|"((?:""|[^"])*)")?\s*\)\s*\}\}~i
REGEX;

        $driver = $this->driver();

        $result = preg_replace_callback(
            $pattern,
            static function (array $match) use ($driver): string {
                $name = strtolower($match[1]);

                $argument = null;

                if (isset($match[2]) && $match[2] !== '') {
                    $argument = str_replace("''", "'", $match[2]);
                } elseif (isset($match[3]) && $match[3] !== '') {
                    $argument = str_replace('""', '"', $match[3]);
                }

                if ($name === 'password') {
                    if ($argument === null || $argument === '') {
                        throw new RuntimeException(
                            'The password placeholder requires a non-empty string.'
                        );
                    }

                    $value = password_hash($argument, PASSWORD_DEFAULT);

                    if ($value === false) {
                        throw new RuntimeException(
                            'Unable to hash migration password.'
                        );
                    }
                } elseif (in_array($name, ['now', 'today', 'time'], true)) {
                    try {
                        $timezone = new DateTimeZone($argument ?? 'UTC');
                        $date = new DateTimeImmutable('now', $timezone);
                    } catch (\Throwable $e) {
                        throw new RuntimeException(
                            'Invalid migration placeholder timezone.',
                            0,
                            $e
                        );
                    }

                    if ($name === 'now') {
                        $value = match ($driver) {
                            'mysql' => $date->format('Y-m-d H:i:s'),
                            'pgsql' => $date->format('Y-m-d H:i:sP'),
                            'sqlite' => $argument === null
                                ? $date->setTimezone(new DateTimeZone('UTC'))
                                    ->format('Y-m-d\TH:i:s\Z')
                                : $date->format('Y-m-d\TH:i:sP'),

                            default => throw new RuntimeException(
                                "Unsupported database driver: {$driver}"
                            ),
                        };
                    } else {
                        $value = $name === 'today'
                            ? $date->format('Y-m-d')
                            : $date->format('H:i:s');
                    }
                } elseif ($name === 'uuid') {
                    if ($argument !== null) {
                        throw new RuntimeException(
                            'The uuid placeholder does not accept arguments.'
                        );
                    }

                    $bytes = random_bytes(16);

                    $bytes[6] = chr(
                        (ord($bytes[6]) & 0x0f) | 0x40
                    );

                    $bytes[8] = chr(
                        (ord($bytes[8]) & 0x3f) | 0x80
                    );

                    $hex = bin2hex($bytes);

                    $value = sprintf(
                        '%s-%s-%s-%s-%s',
                        substr($hex, 0, 8),
                        substr($hex, 8, 4),
                        substr($hex, 12, 4),
                        substr($hex, 16, 4),
                        substr($hex, 20, 12)
                    );
                } else {
                    throw new RuntimeException(
                        "Unsupported migration placeholder: {$name}"
                    );
                }

                return "'" . str_replace("'", "''", $value) . "'";
            },
            $sql
        );

        if ($result === null) {
            throw new RuntimeException(
                'Unable to process migration placeholders.'
            );
        }

        if (preg_match('~\{\{.*?\}\}~s', $result)) {
            throw new RuntimeException(
                'Migration contains an invalid or unresolved placeholder.'
            );
        }

        return $result;
    }

    private function apply(
        string $name,
        string $direction,
        ?int $batch = null
    ): void {
        $sql = $this->loadSql($name, $direction);

        Database::transaction(
            function (PDO $pdo) use ($name, $direction, $batch, $sql): void {
                $pdo->exec($sql);

                if ($direction === 'up') {
                    $this->record(
                        $name,
                        $batch ?? $this->nextBatch()
                    );
                } else {
                    $this->forget($name);
                }
            },
            $this->connection
        );
    }

    private function appliedMigrations(): array
    {
        $this->ensureRepository();

        $statement = $this->connection()->query(
            'SELECT migration, batch FROM ' . self::TABLE . ' ORDER BY id ASC'
        );

        $result = [];

        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[$row['migration']] = [
                'batch' => (int) $row['batch'],
            ];
        }

        return $result;
    }

    private function nextBatch(): int
    {
        $value = $this->connection()->query(
            'SELECT MAX(batch) FROM ' . self::TABLE
        )->fetchColumn();

        return ((int) $value) + 1;
    }

    private function latestBatch(): ?int
    {
        $value = $this->connection()->query(
            'SELECT MAX(batch) FROM ' . self::TABLE
        )->fetchColumn();

        return $value === false || $value === null
            ? null
            : (int) $value;
    }

    private function record(string $name, int $batch): void
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO ' . self::TABLE
            . ' (migration, batch) VALUES (:migration, :batch)'
        );

        $statement->execute([
            'migration' => $name,
            'batch' => $batch,
        ]);
    }

    private function forget(string $name): void
    {
        $statement = $this->connection()->prepare(
            'DELETE FROM ' . self::TABLE . ' WHERE migration = :migration'
        );

        $statement->execute(['migration' => $name]);
    }
}

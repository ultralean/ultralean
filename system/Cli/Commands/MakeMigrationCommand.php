<?php

declare(strict_types=1);

namespace System\Cli\Commands;

use RuntimeException;

final class MakeMigrationCommand
{
    public static function signature(): array
    {
        return ['name' => 'make:migration', 'description' => 'Create a new SQL migration file.'];
    }

    public function handle(array $arguments): int
    {
        $name = trim((string) ($arguments[0] ?? ''));

        if ($name === '') {
            fwrite(STDERR, "Usage: php ul make:migration create_users\n");
            return 1;
        }

        $slug = \str_slug($name);
        $timestamp = date('Ymd_His');
        $filename = $timestamp . '_' . $slug . '.sql';
        $counter = 1;

        while (is_file(app_path('Migrations/' . $filename))) {
            $filename = $timestamp . '_' . $slug . '_' . str_pad((string) $counter, 2, '0', STR_PAD_LEFT) . '.sql';
            $counter++;
        }

        $contents = $this->template($name);
        $path = app_path('Migrations/' . $filename);

        if (file_put_contents($path, $contents . PHP_EOL) === false) {
            throw new RuntimeException("Unable to create migration: {$path}");
        }

        echo "Created: app/Migrations/{$filename}\n";
        return 0;
    }

    private function template(string $name): string
    {
        if (preg_match('/^create_(.+)$/i', \str_slug($name), $match)) {
            $table = $match[1];

            return <<<SQL
-- UP

CREATE TABLE {$table} (
    id INTEGER PRIMARY KEY,
    created_at VARCHAR(30) NOT NULL,
    updated_at VARCHAR(30) NOT NULL
);

-- DOWN

DROP TABLE {$table};
SQL;
        }

        if (preg_match('/^add_(.+)_to_(.+)$/i', \str_slug($name), $match)) {
            $column = $match[1];
            $table = $match[2];

            return <<<SQL
-- UP

ALTER TABLE {$table}
    ADD COLUMN {$column} VARCHAR(190);

-- DOWN

ALTER TABLE {$table}
    DROP COLUMN {$column};
SQL;
        }

        if (preg_match('/^drop_(.+)$/i', \str_slug($name), $match)) {
            $table = $match[1];

            return <<<SQL
-- UP

DROP TABLE {$table};

-- DOWN

-- Recreate the dropped table here.
CREATE TABLE {$table} (
    id INTEGER PRIMARY KEY,
    created_at VARCHAR(30) NOT NULL,
    updated_at VARCHAR(30) NOT NULL
);
SQL;
        }

        return <<<SQL
-- UP

-- Write the forward migration SQL here.
-- Passwords can use the migration-only placeholder:
-- {{ password('plain-text-password') }}
-- It is converted to PHP's PASSWORD_DEFAULT hash while the migration runs.
-- Example:
-- INSERT INTO users (password_hash) VALUES ({{ password('change-me') }});

SELECT 1;

-- DOWN

-- Write the rollback SQL here.
-- Example:
-- DROP TABLE example;

SELECT 1;
SQL;
    }
}

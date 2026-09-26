<?php

declare(strict_types=1);

namespace System\Cli\Commands;

use System\Cli\Migration;

final class MigrateCommand
{
    public static function signature(): array
    {
        return ['name' => 'migrate', 'description' => 'Run all pending migrations. Supports --database=name.'];
    }

    public function handle(array $arguments): int
    {
        $migration = new Migration(app_path('Migrations'), $this->database($arguments));
        $ran = $migration->migrate();

        if ($ran === []) {
            echo "Nothing to migrate.\n";
            return 0;
        }

        foreach ($ran as $name) {
            echo "Migrated: {$name}\n";
        }

        echo "Done. " . count($ran) . " migration(s) applied.\n";
        return 0;
    }
    private function database(array $arguments): ?string
    {
        foreach ($arguments as $argument) {
            if (str_starts_with($argument, '--database=')) {
                $name = trim(substr($argument, 11));
                return $name !== '' ? $name : null;
            }
        }

        return null;
    }

}

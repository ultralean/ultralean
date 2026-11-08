<?php

declare(strict_types=1);

namespace System\Cli\Commands;

use System\Cli\DatabaseSelector;
use System\Cli\Migration;

final class MigrateCommand
{
    public static function signature(): array
    {
        return [
            'name' => 'migrate',
            'description' => 'Run all pending migrations. Supports --database=name.',
        ];
    }

    public function handle(array $arguments): int
    {
        $database = DatabaseSelector::resolve($arguments);

        $migration = new Migration(
            app_path('Migrations'),
            $database
        );

        $ran = $migration->migrate();

        if ($ran === []) {
            echo "Nothing to migrate.\n";
            return 0;
        }

        foreach ($ran as $name) {
            echo "Migrated: {$name}\n";
        }

        echo "\nDone. " . count($ran) . " migration(s) applied.\n";

        return 0;
    }
}

<?php

declare(strict_types=1);

namespace System\Cli\Commands;

use System\Cli\DatabaseSelector;
use System\Cli\Migration;

final class MigrateRollbackCommand
{
    public static function signature(): array
    {
        return [
            'name' => 'migrate:rollback',
            'description' => 'Undo the latest migration batch. Supports --database=name.',
        ];
    }

    public function handle(array $arguments): int
    {
        $database = DatabaseSelector::resolve($arguments);

        $rolledBack = (new Migration(
            app_path('Migrations'),
            $database
        ))->rollback();

        if ($rolledBack === []) {
            echo "Nothing to rollback.\n";
            return 0;
        }

        foreach ($rolledBack as $name) {
            echo "Rolled back: {$name}\n";
        }

        echo "\nDone. "
            . count($rolledBack)
            . " migration(s) rolled back.\n";

        return 0;
    }
}

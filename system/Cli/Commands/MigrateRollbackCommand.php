<?php

declare(strict_types=1);

namespace System\Cli\Commands;

use System\Cli\Migration;

final class MigrateRollbackCommand
{
    public static function signature(): array
    {
        return ['name' => 'migrate:rollback', 'description' => 'Undo the latest migration batch. Supports --database=name.'];
    }

    public function handle(array $arguments): int
    {
        $rolledBack = (new Migration(app_path('Migrations'), $this->database($arguments)))->rollback();

        if ($rolledBack === []) {
            echo "Nothing to rollback.\n";
            return 0;
        }

        foreach ($rolledBack as $name) {
            echo "Rolled back: {$name}\n";
        }

        echo "Done. " . count($rolledBack) . " migration(s) rolled back.\n";
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

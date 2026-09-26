<?php

declare(strict_types=1);

namespace System\Cli\Commands;

use System\Cli\Migration;

final class MigrateFreshCommand
{
    public static function signature(): array
    {
        return ['name' => 'migrate:fresh', 'description' => 'Reset all migrations, then run them again. Supports --database=name.'];
    }

    public function handle(array $arguments): int
    {
        if (!$this->confirmed($arguments)) {
            echo "Fresh migration cancelled.
";
            return 0;
        }

        $migration = new Migration(app_path('Migrations'), $this->database($arguments));
        $migration->reset();
        $ran = $migration->migrate();

        echo "Database refreshed. " . count($ran) . " migration(s) applied.\n";
        return 0;
    }
    private function confirmed(array $arguments): bool
    {
        if (in_array('--force', $arguments, true)) {
            return true;
        }

        if (!defined('STDIN') || !function_exists('readline')) {
            return false;
        }

        $answer = readline('This will undo ALL migrations and run them again. Continue? [y/N] ');
        return in_array(strtolower(trim($answer)), ['y', 'yes'], true);
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

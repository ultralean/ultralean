<?php

declare(strict_types=1);

namespace System\Cli\Commands;

use System\Cli\DatabaseSelector;
use System\Cli\Migration;

final class MigrateFreshCommand
{
    public static function signature(): array
    {
        return [
            'name' => 'migrate:fresh',
            'description' => 'Reset all migrations, then run them again. Supports --database=name.',
        ];
    }

    public function handle(array $arguments): int
    {
        if (!$this->confirmed($arguments)) {
            echo "Fresh migration cancelled.\n";
            return 0;
        }

        $database = DatabaseSelector::resolve($arguments);

        $migration = new Migration(
            app_path('Migrations'),
            $database
        );

        $ran = $migration->fresh();

        foreach ($ran as $name) {
            echo "Migrated: {$name}\n";
        }

        echo "\nDatabase refreshed. "
            . count($ran)
            . " migration(s) applied.\n";

        return 0;
    }

    private function confirmed(array $arguments): bool
    {
        if (in_array('--force', $arguments, true)) {
            return true;
        }

        if (!defined('STDIN')) {
            return false;
        }

        echo "WARNING: This will undo ALL migrations and run them again.\n";
        echo "Continue? [y/N] ";

        $answer = fgets(STDIN);

        return $answer !== false
            && in_array(
                strtolower(trim($answer)),
                ['y', 'yes'],
                true
            );
    }
}

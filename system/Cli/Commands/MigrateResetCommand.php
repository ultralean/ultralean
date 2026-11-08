<?php

declare(strict_types=1);

namespace System\Cli\Commands;

use System\Cli\DatabaseSelector;
use System\Cli\Migration;

final class MigrateResetCommand
{
    public static function signature(): array
    {
        return [
            'name' => 'migrate:reset',
            'description' => 'Undo every applied migration. Supports --database=name.',
        ];
    }

    public function handle(array $arguments): int
    {
        if (!$this->confirmed($arguments)) {
            echo "Reset cancelled.\n";
            return 0;
        }

        $database = DatabaseSelector::resolve($arguments);

        $reset = (new Migration(
            app_path('Migrations'),
            $database
        ))->reset();

        if ($reset === []) {
            echo "Nothing to reset.\n";
            return 0;
        }

        foreach ($reset as $name) {
            echo "Reset: {$name}\n";
        }

        echo "\nDone. " . count($reset) . " migration(s) reset.\n";

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

        echo "WARNING: This will undo ALL applied migrations.\n";
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

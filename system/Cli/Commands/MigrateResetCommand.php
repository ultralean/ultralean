<?php

declare(strict_types=1);

namespace System\Cli\Commands;

use System\Cli\Migration;

final class MigrateResetCommand
{
    public static function signature(): array
    {
        return ['name' => 'migrate:reset', 'description' => 'Undo every applied migration. Supports --database=name.'];
    }

    public function handle(array $arguments): int
    {
        if (!$this->confirmed($arguments)) {
            echo "Reset cancelled.
";
            return 0;
        }

        $reset = (new Migration(app_path('Migrations'), $this->database($arguments)))->reset();

        if ($reset === []) {
            echo "Nothing to reset.\n";
            return 0;
        }

        foreach ($reset as $name) {
            echo "Reset: {$name}\n";
        }

        echo "Done. " . count($reset) . " migration(s) reset.\n";
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

        $answer = readline('This will undo ALL applied migrations. Continue? [y/N] ');
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

<?php

declare(strict_types=1);

namespace System\Cli\Commands;

use System\Cli\Migration;

final class MigrateStatusCommand
{
    public static function signature(): array
    {
        return ['name' => 'migrate:status', 'description' => 'Show migration status. Supports --database=name.'];
    }

    public function handle(array $arguments): int
    {
        $rows = (new Migration(app_path('Migrations'), $this->database($arguments)))->status();

        if ($rows === []) {
            echo "No migration files found.\n";
            return 0;
        }

        printf("%-8s %-8s %-6s %s\n", 'Status', 'Batch', ' ', 'Migration');
        echo str_repeat('-', 70) . "\n";

        foreach ($rows as $row) {
            printf(
                "%-8s %-8s %-6s %s\n",
                $row['status'],
                $row['batch'] ?? '-',
                '',
                $row['name']
            );
        }

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

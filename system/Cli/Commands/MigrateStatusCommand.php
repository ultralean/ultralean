<?php

declare(strict_types=1);

namespace System\Cli\Commands;

use System\Cli\DatabaseSelector;
use System\Cli\Migration;

final class MigrateStatusCommand
{
    public static function signature(): array
    {
        return [
            'name' => 'migrate:status',
            'description' => 'Show migration status. Supports --database=name.',
        ];
    }

    public function handle(array $arguments): int
    {
        $database = DatabaseSelector::resolve($arguments);

        $rows = (new Migration(
            app_path('Migrations'),
            $database
        ))->status();

        echo "Database: {$database}\n\n";

        if ($rows === []) {
            echo "No migration files found.\n";
            return 0;
        }

        printf(
            "%-10s %-10s %s\n",
            'Status',
            'Batch',
            'Migration'
        );

        echo str_repeat('-', 75) . "\n";

        foreach ($rows as $row) {
            printf(
                "%-10s %-10s %s\n",
                $row['status'],
                $row['batch'] ?? '-',
                $row['name']
            );
        }

        return 0;
    }
}

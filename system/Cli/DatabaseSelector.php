<?php

declare(strict_types=1);

namespace System\Cli;

use RuntimeException;
use System\Core\Database;

final class DatabaseSelector
{
    public static function resolve(array $arguments): string
    {
        $connections = config('database.connections', []);

        if (!is_array($connections) || $connections === []) {
            throw new RuntimeException(
                'No database connections are configured.'
            );
        }

        $requested = self::argument($arguments);

        // Explicitly requested connection.
        if ($requested !== null) {
            if (!isset($connections[$requested])) {
                throw new RuntimeException(
                    "Database connection '{$requested}' is not configured."
                );
            }

            self::testConnection($requested);

            return $requested;
        }

        // Discover only connections that can actually connect.
        $available = [];

        foreach ($connections as $name => $settings) {
            if (!is_string($name) || $name === '' || !is_array($settings)) {
                continue;
            }

            try {
                Database::connection($name);
                $available[$name] = $settings;
            } catch (\Throwable) {
                // Skip unavailable connections.
            }
        }

        if ($available === []) {
            throw new RuntimeException(
                'No configured database connections are currently connectable.'
            );
        }

        return self::select($available);
    }

    private static function argument(array $arguments): ?string
    {
        foreach ($arguments as $index => $argument) {
            if ($argument === '--database') {
                $name = trim((string) ($arguments[$index + 1] ?? ''));

                if ($name === '') {
                    throw new RuntimeException(
                        'The --database option requires a connection name.'
                    );
                }

                return $name;
            }

            if (str_starts_with($argument, '--database=')) {
                $name = trim(substr($argument, strlen('--database=')));

                if ($name === '') {
                    throw new RuntimeException(
                        'The --database option requires a connection name.'
                    );
                }

                return $name;
            }
        }

        return null;
    }

    private static function testConnection(string $name): void
    {
        try {
            Database::connection($name);
        } catch (\Throwable $e) {
            throw new RuntimeException(
                "Unable to connect to database connection '{$name}': "
                . $e->getMessage(),
                0,
                $e
            );
        }
    }

    private static function select(array $connections): string
    {
        if (!defined('STDIN') || !defined('STDOUT')) {
            throw new RuntimeException(
                'Interactive database selection requires a CLI terminal. '
                . 'Use --database=name instead.'
            );
        }

        $names = array_keys($connections);

        echo "\n";
        echo "Available database connections\n";
        echo str_repeat('-', 55) . "\n";

        /* foreach ($connections as $name => $settings) {
            $driver = $settings['driver'] ?? 'unknown';

            printf(
                "  %2d) %-20s [%s]\n",
                count(array_slice($names, 0, array_search($name, $names, true) + 1)),
                $name,
                $driver
            );
        } */

            foreach ($names as $index => $name) {
    $settings = $connections[$name];

    printf(
        "  %2d) %-20s [%s]\n",
        $index + 1,
        $name,
        $settings['driver'] ?? 'unknown'
    );
}

        echo str_repeat('-', 55) . "\n";
        echo "  0) Cancel\n\n";

        while (true) {
            echo 'Select a database connection: ';

            $input = fgets(STDIN);

            if ($input === false) {
                throw new RuntimeException(
                    'Unable to read database selection.'
                );
            }

            $choice = trim($input);

            if ($choice === '0') {
                throw new RuntimeException(
                    'Database selection cancelled.'
                );
            }

            if (
                $choice !== ''
                && ctype_digit($choice)
                && (int) $choice >= 1
                && (int) $choice <= count($names)
            ) {
                $selected = $names[(int) $choice - 1];

                // Verify again before proceeding.
                self::testConnection($selected);

                echo "\nSelected database: {$selected}\n\n";

                return $selected;
            }

            echo "Invalid selection. Please enter a number from 0 to "
                . count($names) . ".\n";
        }
    }
}

<?php

declare(strict_types=1);

namespace System\Cli;

use RuntimeException;

final class Cli
{
    public function run(array $argv): int
    {
        $command = $argv[1] ?? 'help';
        $arguments = array_slice($argv, 2);

        if ($command === 'help' || $command === '--help' || $command === '-h') {
            $this->help();
            return 0;
        }

        $class = $this->commandClass($command);

        if (!class_exists($class)) {
            $this->error("Unknown command: {$command}");
            $this->help();
            return 1;
        }

        try {
            $instance = new $class();

            if (!method_exists($instance, 'handle')) {
                throw new RuntimeException("Invalid command class: {$class}");
            }

            return (int) $instance->handle($arguments);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }

    private function commandClass(string $command): string
    {
        $parts = explode(':', trim($command));
        $class = array_reduce(
            $parts,
            static fn(string $carry, string $part): string => $carry . ucfirst($part),
            ''
        );

        return 'System\\Cli\\Commands\\' . $class . 'Command';
    }

    public function commands(): array
    {
        return $this->discoverCommands();
    }

    private function help(): void
    {
        $commands = $this->commands();

        echo "ultralean CLI\n\n";
        echo "Usage:\n";
        echo "  php ul <command> [arguments]\n\n";
        echo "Commands:\n";

        foreach ($commands as $name => $description) {
            printf("  %-24s %s\n", $name, $description);
        }
    }

    private function discoverCommands(): array
    {
        $dir = __DIR__ . '/Commands';
        $commands = [];

        foreach (glob($dir . '/*Command.php') ?: [] as $file) {
            $class = 'System\\Cli\\Commands\\' . basename($file, '.php');

            if (!class_exists($class) || !method_exists($class, 'signature')) {
                continue;
            }

            $signature = $class::signature();
            $commands[$signature['name']] = $signature['description'];
        }

        ksort($commands);
        return $commands;
    }

    private function error(string $message): void
    {
        $line = "Error: {$message}" . PHP_EOL;
        if (PHP_SAPI === 'cli') {
            fwrite(STDERR, $line);
            return;
        }

        echo $line;
    }
}

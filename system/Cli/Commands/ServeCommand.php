<?php

declare(strict_types=1);

namespace System\Cli\Commands;

use RuntimeException;

final class ServeCommand
{
    public static function signature(): array
    {
        return [
            'name' => 'serve',
            'description' => 'Start the local development server.',
        ];
    }

    public function handle(array $arguments): int
    {
        $host = '127.0.0.1';
        $port = 8000;

        foreach ($arguments as $argument) {
            if ($argument === '--help' || $argument === '-h') {
                $this->help();
                return 0;
            }

            if ($argument === '--network') {
                $host = '0.0.0.0';
                continue;
            }

            if (str_starts_with($argument, '--host=')) {
                $host = trim(substr($argument, 7));
                continue;
            }

            if (str_starts_with($argument, '--port=')) {
                $port = (int) substr($argument, 7);
                continue;
            }

            throw new RuntimeException("Unknown serve option: {$argument}");
        }

        if ($host === '') {
            throw new RuntimeException('The host cannot be empty.');
        }

        if ($port < 1 || $port > 65535) {
            throw new RuntimeException('The port must be between 1 and 65535.');
        }

        $publicPath = public_path();
        $router = public_path('index.php');

        if (!is_dir($publicPath) || !is_file($router)) {
            throw new RuntimeException('The public directory or public/index.php could not be found.');
        }

        $address = $host . ':' . $port;

        echo PHP_EOL;
        echo "ultralean development server" . PHP_EOL;

        if ($host === '127.0.0.1') {
            echo "Local:   http://127.0.0.1:{$port}/" . PHP_EOL;
        } elseif ($host === '0.0.0.0') {
            echo "Local:   http://127.0.0.1:{$port}/" . PHP_EOL;
        } else {
            echo "URL:     http://{$host}:{$port}/" . PHP_EOL;
        }

        if ($host === '0.0.0.0') {
            foreach ($this->localAddresses() as $ip) {
                echo "Network: http://{$ip}:{$port}/" . PHP_EOL;
            }

            echo "Network mode is enabled. Other devices on the same LAN can use a Network URL." . PHP_EOL;
            echo "If Windows Firewall asks for permission, allow PHP for your private network." . PHP_EOL;
            echo "This does not automatically expose the site to the public Internet." . PHP_EOL;
        } else {
            echo "Host:    {$host}" . PHP_EOL;
            echo "Use --network to allow devices on your local network to connect." . PHP_EOL;
        }

        echo "Press Ctrl+C to stop the server." . PHP_EOL . PHP_EOL;

        $command = escapeshellarg(PHP_BINARY)
            . ' -S ' . escapeshellarg($address)
            . ' -t ' . escapeshellarg($publicPath)
            . ' ' . escapeshellarg($router);

        passthru($command, $exitCode);

        return (int) $exitCode;
    }

    private function localAddresses(): array
    {
        $hostname = gethostname();

        if ($hostname === false || $hostname === '') {
            return [];
        }

        $addresses = gethostbynamel($hostname) ?: [];

        return array_values(array_unique(array_filter(
            $addresses,
            static fn(string $ip): bool => filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
                && !str_starts_with($ip, '127.')
        )));
    }

    private function help(): void
    {
        echo "Usage: php ul serve [options]" . PHP_EOL . PHP_EOL;
        echo "Start the ultralean local development server." . PHP_EOL . PHP_EOL;
        echo "Options:" . PHP_EOL;
        echo "  --port=8000       Listen on the specified port." . PHP_EOL;
        echo "  --host=127.0.0.1  Listen on the specified host." . PHP_EOL;
        echo "  --network         Listen on 0.0.0.0 for local-network testing." . PHP_EOL;
        echo "  --help            Show this help." . PHP_EOL . PHP_EOL;
        echo "Examples:" . PHP_EOL;
        echo "  php ul serve" . PHP_EOL;
        echo "  php ul serve --port=8080" . PHP_EOL;
        echo "  php ul serve --network" . PHP_EOL;
        echo "  php ul serve --network --port=8080" . PHP_EOL;
    }
}

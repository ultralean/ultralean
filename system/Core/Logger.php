<?php

declare(strict_types=1);

namespace System\Core;

final class Logger
{
    private static ?string $file = null;
    private static ?string $date = null;
    private static int $writeCount = 0;

    private function __construct() {}

    public static function log(string $level, string $message, array $context = []): void
    {
        self::write(strtolower($level), $message, $context);
    }

    public static function emergency(string $message, array $context = []): void { self::write('emergency', $message, $context); }
    public static function alert(string $message, array $context = []): void { self::write('alert', $message, $context); }
    public static function critical(string $message, array $context = []): void { self::write('critical', $message, $context); }
    public static function error(string $message, array $context = []): void { self::write('error', $message, $context); }
    public static function warning(string $message, array $context = []): void { self::write('warning', $message, $context); }
    public static function notice(string $message, array $context = []): void { self::write('notice', $message, $context); }
    public static function info(string $message, array $context = []): void { self::write('info', $message, $context); }
    public static function debug(string $message, array $context = []): void { self::write('debug', $message, $context); }

    private static function write(string $level, string $message, array $context): void
    {
        $date = date('Y-m-d');

        if (self::$file === null || self::$date !== $date) {
            self::$date = $date;
            self::$file = storage_path("logs/app-{$date}.log");
            $dir = dirname(self::$file);

            if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
                return;
            }
        }

        $line = '[' . date('Y-m-d H:i:s') . '] ' . strtoupper($level) . ': ' . $message;

        if ($context !== []) {
            $json = json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
            if ($json !== false) {
                $line .= ' ' . $json;
            }
        }

        if (@file_put_contents(self::$file, $line . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
            return;
        }

        self::$writeCount++;
        $every = max(1, (int) config('app.errors.logging.cleanup_every', 100));
        if (self::$writeCount % $every === 0) {
            self::cleanup();
        }
    }

    private static function cleanup(): void
    {
        $days = max(0, (int) config('app.errors.logging.retention_days', 0));
        if ($days === 0) {
            return;
        }

        $directory = storage_path('logs');
        if (!is_dir($directory)) {
            return;
        }

        $cutoff = time() - ($days * 86400);
        foreach (glob($directory . '/app-*.log') ?: [] as $file) {
            if ($file === self::$file || !is_file($file)) {
                continue;
            }

            $name = basename($file);
            if (preg_match('/^app-(\d{4}-\d{2}-\d{2})\.log$/', $name, $match) !== 1) {
                continue;
            }

            $timestamp = strtotime($match[1] . ' 00:00:00');
            if ($timestamp !== false && $timestamp < $cutoff) {
                @unlink($file);
            }
        }
    }
}

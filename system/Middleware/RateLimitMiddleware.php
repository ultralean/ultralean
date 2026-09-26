<?php

declare(strict_types=1);

namespace System\Middleware;

use RuntimeException;
use System\Core\Request;
use System\Core\Response;

final class RateLimitMiddleware
{
    private static int $operations = 0;

    public function handle(Request $request, callable $next): Response
    {
        $config = config('app.rate_limit', []);
        if (!is_array($config) || empty($config['enabled'])) {
            return $next();
        }

        $path = $request->path();
        $rule = $config['routes'][$path] ?? [];
        if (!is_array($rule)) {
            $rule = [];
        }

        $directory = (string) ($config['storage'] ?? storage_path('cache/rate_limit'));
        if (!$this->ensureDirectory($directory)) {
            return $next();
        }

        $accountField = (string) ($rule['account_field'] ?? '');
        if ($accountField !== '') {
            $account = trim((string) $request->input($accountField, ''));
            if ($account !== '') {
                $this->consume(
                    $directory,
                    'account|' . $path . '|' . self::normalizeAccount($account),
                    max(1, (int) ($rule['account_limit'] ?? $rule['limit'] ?? 5)),
                    max(1, (int) ($rule['account_window'] ?? $rule['window'] ?? 60))
                );
            }

            $limit = max(1, (int) ($rule['ip_limit'] ?? $config['limit'] ?? 60));
            $window = max(1, (int) ($rule['ip_window'] ?? $config['window'] ?? 60));
            $this->consume($directory, 'ip|' . $path . '|' . $request->ip(), $limit, $window);
            return $next();
        }

        $limit = max(1, (int) ($rule['limit'] ?? $config['limit'] ?? 60));
        $window = max(1, (int) ($rule['window'] ?? $config['window'] ?? 60));
        $this->consume($directory, 'request|' . $request->method() . '|' . $path . '|' . $request->ip(), $limit, $window);

        return $next();
    }

    private function consume(string $directory, string $identity, int $limit, int $window): void
    {
        self::$operations++;
        $this->maybeCleanup($directory);

        $key = hash('sha256', $identity);
        $file = $directory . '/' . $key . '.tmp';
        $handle = @fopen($file, 'c+');
        if ($handle === false) {
            return;
        }

        $allowed = true;
        $retryAfter = 0;

        try {
            if (!flock($handle, LOCK_EX)) {
                return;
            }

            $contents = stream_get_contents($handle);
            [$count, $expires] = self::parse($contents ?: '');
            $now = time();

            if ($expires <= $now) {
                $count = 0;
                $expires = $now + $window;
            }

            $count++;
            if ($count > $limit) {
                $allowed = false;
                $retryAfter = max(1, $expires - $now);
            } else {
                ftruncate($handle, 0);
                rewind($handle);
                fwrite($handle, $count . '|' . $expires);
                fflush($handle);
            }

            flock($handle, LOCK_UN);
        } finally {
            fclose($handle);
        }

        if (!$allowed) {
            if (PHP_SAPI !== 'cli') {
                header('Retry-After: ' . $retryAfter);
            }
            throw new RuntimeException('Too many requests. Please wait before trying again.', 429);
        }
    }

    private function maybeCleanup(string $directory): void
    {
        $every = max(1, (int) config('app.rate_limit.cleanup_every', 100));
        if (self::$operations % $every !== 0) {
            return;
        }

        $age = max(0, (int) config('app.rate_limit.cleanup_after', 86400));
        if ($age === 0 || !is_dir($directory)) {
            return;
        }

        $cutoff = time() - $age;
        foreach (glob($directory . '/*.tmp') ?: [] as $file) {
            if (!is_file($file)) {
                continue;
            }

            $contents = @file_get_contents($file);
            [, $expires] = self::parse($contents ?: '');
            if ($expires > 0 && $expires < $cutoff) {
                @unlink($file);
            }
        }
    }

    private static function normalizeAccount(string $account): string
    {
        return strtolower(trim($account));
    }

    private static function parse(string $value): array
    {
        $parts = explode('|', trim($value));
        if (count($parts) !== 2) {
            return [0, 0];
        }

        return [(int) $parts[0], (int) $parts[1]];
    }

    private function ensureDirectory(string $directory): bool
    {
        if (is_dir($directory)) {
            return true;
        }

        return mkdir($directory, 0775, true) || is_dir($directory);
    }
}

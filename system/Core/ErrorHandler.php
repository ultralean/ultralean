<?php

declare(strict_types=1);

namespace System\Core;

use ErrorException;
use Throwable;

final class ErrorHandler
{
    private static bool $registered = false;
    private static bool $handling = false;

    private function __construct() {}

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        self::$registered = true;
        set_exception_handler([self::class, 'exception']);
        set_error_handler([self::class, 'error']);
        register_shutdown_function([self::class, 'shutdown']);
    }

    public static function exception(Throwable $e): void
    {
        if (self::$handling) {
            self::fallback($e);
            return;
        }

        self::$handling = true;
        self::cleanBuffers();

        $status = self::status($e);
        if (PHP_SAPI !== 'cli') {
            http_response_code($status);
            if ($status === 429) {
                header('Retry-After: 60');
            }
        }

        self::log($e, $status);

        if (self::wantsJson()) {
            self::renderJson($e, $status);
            return;
        }

        if (self::debug()) {
            self::renderException($e, $status);
        } else {
            self::renderFriendly($status);
        }
    }

    public static function error(int $level, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $level)) {
            return false;
        }

        throw new ErrorException($message, 0, $level, $file, $line);
    }

    public static function shutdown(): void
    {
        $error = error_get_last();
        if ($error === null) {
            return;
        }

        $fatal = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR;
        if (($error['type'] & $fatal) === 0) {
            return;
        }

        self::exception(new ErrorException(
            $error['message'],
            0,
            $error['type'],
            $error['file'],
            $error['line']
        ));
    }

    private static function debug(): bool
    {
        return (bool) config('app.debug', false);
    }

    private static function status(Throwable $e): int
    {
        $code = (int) $e->getCode();
        return $code >= 400 && $code <= 599 ? $code : 500;
    }

    private static function statusText(int $status): string
    {
        return match ($status) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Page Not Found',
            405 => 'Method Not Allowed',
            408 => 'Request Timeout',
            409 => 'Conflict',
            419 => 'Page Expired',
            422 => 'Validation Error',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            default => 'Request Error',
        };
    }

    private static function friendlyMessage(int $status): string
    {
        return match ($status) {
            400 => 'The request could not be understood. Please check the information and try again.',
            401 => 'You need to sign in to continue.',
            403 => 'You do not have permission to access this page.',
            404 => 'The page you requested could not be found.',
            405 => 'This action is not available for this request method.',
            408 => 'The request took too long. Please try again.',
            409 => 'The request could not be completed because it conflicts with existing data.',
            419 => 'Your session or security token has expired. Please try again.',
            422 => 'Some of the information provided is invalid. Please check the form and try again.',
            429 => 'Too many requests were made. Please wait a moment and try again.',
            500 => 'The application could not complete the request. Please try again later.',
            501 => 'This feature is not available yet.',
            502 => 'The service received an invalid response from an upstream service.',
            503 => 'The service is temporarily unavailable. Please try again later.',
            504 => 'An upstream service took too long to respond. Please try again later.',
            default => 'Something went wrong while processing your request. Please try again later.',
        };
    }

    private static function log(Throwable $e, int $status): void
    {
        $logging = config('app.errors.logging', []);
        if (!is_array($logging) || empty($logging['enabled'])) {
            return;
        }

        $levels = $logging['levels'] ?? [];
        if (!is_array($levels) || $levels === []) {
            return;
        }

        $level = self::logLevel($status, $e);
        if (!self::shouldLog($level, $levels)) {
            return;
        }

        try {
            Logger::log($level, $e->getMessage(), [
                'status' => $status,
                'exception' => $e::class,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        } catch (Throwable) {
            // Never let logging hide the original error.
        }
    }

    private static function shouldLog(string $actual, array $configured): bool
    {
        $severity = [
            'emergency' => 0,
            'alert' => 1,
            'critical' => 2,
            'error' => 3,
            'warning' => 4,
            'notice' => 5,
            'info' => 6,
            'debug' => 7,
        ];

        $actualRank = $severity[$actual] ?? 7;
        foreach ($configured as $level) {
            $rank = $severity[strtolower((string) $level)] ?? null;
            if ($rank !== null && $actualRank <= $rank) {
                return true;
            }
        }

        return false;
    }

    private static function logLevel(int $status, Throwable $e): string
    {
        if ($e instanceof ErrorException) {
            return 'error';
        }

        return match (true) {
            $status >= 500 => 'error',
            $status === 429 => 'warning',
            $status >= 400 => 'notice',
            default => 'debug',
        };
    }

    private static function wantsJson(): bool
    {
        if (PHP_SAPI === 'cli') {
            return false;
        }

        $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
        $path = (string) ($_SERVER['REQUEST_URI'] ?? '');

        return str_contains($accept, 'application/json')
            || str_starts_with(parse_url($path, PHP_URL_PATH) ?: '/', '/api/');
    }

    private static function renderJson(Throwable $e, int $status): void
    {
        $payload = [
            'error' => [
                'status' => $status,
                'type' => self::statusText($status),
                'message' => self::debug() ? $e->getMessage() : self::friendlyMessage($status),
            ],
        ];

        if (self::debug()) {
            $payload['error']['exception'] = $e::class;
            $payload['error']['file'] = $e->getFile();
            $payload['error']['line'] = $e->getLine();
            $payload['error']['trace'] = $e->getTraceAsString();
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

    private static function cleanBuffers(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    private static function renderFriendly(int $status): void
    {
        if (PHP_SAPI === 'cli') {
            echo $status . ' ' . self::statusText($status) . ': ' . self::friendlyMessage($status) . PHP_EOL;
            return;
        }

        $title = e($status . ' ' . self::statusText($status));
        $message = e(self::friendlyMessage($status));
        $app = e((string) config('app.name', 'ultralean'));

        echo '<!doctype html><html lang="en"><head><meta charset="utf-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<title>' . $title . ' · ' . $app . '</title>';
        echo '<style>body{margin:0;background:#f8fafc;color:#0f172a;font:16px/1.6 system-ui,sans-serif}main{max-width:680px;margin:12vh auto;padding:32px}h1{font-size:42px;margin:0 0 8px}p{color:#475569}a{color:#2563eb;text-decoration:none}</style>';
        echo '</head><body><main><h1>' . $title . '</h1><p>' . $message . '</p><p><a href="/">Return to home</a></p></main></body></html>';
    }

    private static function renderException(Throwable $e, int $status): void
    {
        $file = $e->getFile();
        $line = $e->getLine();
        $message = e($e->getMessage());
        $fileHtml = e($file);
        $trace = e($e->getTraceAsString());
        $lines = self::snippet($file, $line);

        echo '<div style="background:#0f172a;color:#e2e8f0;padding:20px;font:14px/1.5 Consolas,monospace">';
        echo '<h2 style="color:#ff6b6b;margin-top:0">ultralean Exception</h2>';
        echo '<p><b>Status:</b> ' . $status . ' ' . e(self::statusText($status)) . '</p>';
        echo '<p><b>Message:</b> ' . $message . '</p>';
        echo '<p><b>File:</b> ' . $fileHtml . '</p>';
        echo '<p><b>Line:</b> ' . $line . '</p>';

        if ($lines !== []) {
            echo '<h3>Code Preview</h3><pre style="background:#111827;padding:15px;overflow:auto">';
            foreach ($lines as $number => $code) {
                $background = $number === $line ? 'background:#7f1d1d;' : '';
                echo '<div style="' . $background . '">' . str_pad((string) $number, 5, ' ', STR_PAD_LEFT) . ' | ' . e($code) . '</div>';
            }
            echo '</pre>';
        }

        echo '<h3>Stack Trace</h3><pre style="overflow:auto">' . $trace . '</pre></div>';
    }

    private static function snippet(string $file, int $line, int $padding = 6): array
    {
        if (!is_file($file) || $line < 1) {
            return [];
        }

        $start = max(1, $line - $padding);
        $end = $line + $padding;
        $result = [];
        $fileObject = new \SplFileObject($file, 'r');
        $fileObject->seek($start - 1);

        for ($number = $start; $number <= $end && !$fileObject->eof(); $number++) {
            $result[$number] = rtrim($fileObject->current(), "\r\n");
            $fileObject->next();
        }

        return $result;
    }

    private static function fallback(Throwable $e): never
    {
        if (PHP_SAPI !== 'cli') {
            http_response_code(500);
            if (self::wantsJson()) {
                header('Content-Type: application/json; charset=UTF-8');
                echo '{"error":{"status":500,"type":"Internal Server Error","message":"The application could not complete the request."}}';
            } else {
                echo '500 Internal Server Error';
            }
        } else {
            echo "500 Internal Server Error\n";
        }
        exit(1);
    }
}

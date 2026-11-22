
---
layout: docs
key: logging
title: Logging
---

# Logging

ultralean includes a lightweight file-based Logger class for recording application events.

Logs are stored under:

```text
storage/logs/
```

Each day receives a separate log file.

For example:

```text
storage/logs/app-2026-11-22.log
```

## 1. Writing a log

Use the static Logger methods:

```php
use System\Core\Logger;

Logger::info('User logged in.');
```

## 2. Available log levels

```php
Logger::emergency('System is unusable.');
Logger::alert('Immediate action required.');
Logger::critical('Critical application failure.');
Logger::error('An application error occurred.');
Logger::warning('Something unexpected happened.');
Logger::notice('Something noteworthy happened.');
Logger::info('Application event.');
Logger::debug('Debugging information.');
```

You can also use the generic method:

```php
Logger::log('info', 'User logged in.');
```

The generic method converts the level to lowercase.

## 3. Adding context

Pass additional information as an array:

```php
Logger::info(
    'User logged in.',
    [
        'user_id' => 25,
        'ip' => request()->ip(),
    ]
);
```

Context is encoded as JSON and appended to the log entry.

Example:

```text
[2026-11-22 12:30:00] INFO: User logged in. {"user_id":25,"ip":"127.0.0.1"}
```

## 4. Logging exceptions

```php
try {
    // Application operation.
} catch (\Throwable $exception) {
    Logger::error(
        'Operation failed.',
        [
            'exception' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]
    );

    throw $exception;
}
```

Avoid logging passwords, session identifiers, authentication tokens or other sensitive information.

## 5. Log retention

Logging cleanup can be configured in:

```text
app/Config/app.php
```

Example:

```php
'errors' => [
    'logging' => [
        'enabled' => true,
        'levels' => ['error', 'warning'],
        'cleanup_every' => 100,
        'retention_days' => 30,
    ],
],
```

The Logger cleanup mechanism uses:

- `cleanup_every` — how many successful log writes occur before cleanup is considered.
- `retention_days` — how many days of old log files to retain.

A retention value of `0` disables automatic cleanup.

Cleanup only runs when the configured number of successful log writes has been reached.

## 6. Storage permissions

Make sure PHP can write to:

```text
storage/logs/
```

The storage directory should not be publicly accessible.

## Important notes

- Logging is file-based.
- Log files are separated by date.
- Context is stored as JSON.
- Failed writes are silently ignored.
- Retention cleanup is opportunistic, not a scheduled background task.
- The Logger itself does not enforce the configured logging levels or enabled setting; those settings must be applied by the calling error/logging integration.

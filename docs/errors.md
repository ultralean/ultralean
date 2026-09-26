---
layout: docs
key: errors
title: Errors and logging
---

# Errors and logging

## Error handler

`System\Core\ErrorHandler` handles:

- uncaught exceptions
- relevant PHP errors
- shutdown/fatal errors
- safe HTTP error rendering
- JSON API error rendering
- optional logging

## HTTP statuses

Common errors have individual status codes and friendly production messages, including:

```text
400 Bad Request
401 Unauthorized
403 Forbidden
404 Not Found
405 Method Not Allowed
408 Request Timeout
409 Conflict
419 Page Expired
422 Unprocessable Content
429 Too Many Requests
500 Internal Server Error
501 Not Implemented
502 Bad Gateway
503 Service Unavailable
504 Gateway Timeout
```

A database configuration problem is not automatically treated as an indistinguishable generic 500 by the standalone migration utility; migration command failures are displayed as actionable command output.

## Development mode

With:

```php
'env' => 'development',
'debug' => true,
```

exception pages can show:

- status
- message
- file
- line
- source preview
- stack trace

This is intended for local development only.

## Production mode

With debug disabled, the response exposes only a safe, friendly message. Source paths, stack traces and internal exception details are not displayed.

## JSON errors

API requests receive JSON error responses rather than an HTML error page.

## Logging is opt-in

Nothing is logged by default.

Enable it in `app/Config/app.php`:

```php
'errors' => [
    'logging' => [
        'enabled' => true,
        'levels' => ['error'],
        'retention_days' => 10,
        'cleanup_every' => 100,
    ],
],
```

## Severity levels

```text
emergency
alert
critical
error
warning
notice
info
debug
```

The configured values are minimum thresholds.

`['error']` means record emergency, alert, critical and error.

`['warning']` means record emergency through warning.

`['debug']` means record all levels.

## Log files

Logs are stored under:

```text
app/Storage/logs/
```

with daily files.

Retention is controlled by `retention_days`.

`0` means no automatic deletion.

Cleanup is checked only after the configured number of writes, avoiding filesystem scans on every request.

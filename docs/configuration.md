---
layout: docs
key: configuration
title: Configuration
---

# Configuration

Configuration is intentionally small.

## `app/Config/app.php`

```php
return [
    'name' => 'ultralean',
    'env' => 'development',
    'debug' => true,
    'timezone' => 'Asia/Karachi',
    'upload_path' => public_path('uploads'),
    'upload_url' => '/uploads',
    // errors and rate_limit...
];
```

### `name`

Application name used by built-in error pages and sample UI.

### `env`

Supported values:

```text
development
staging
production
```

### `debug`

`true` provides detailed development diagnostics. Use `false` in production.

### `timezone`

Application-facing timezone. Examples:

```text
UTC
Asia/Karachi
Europe/London
America/New_York
```

This does not change the UTC storage rule for model timestamps.

## Error logging configuration

```php
'errors' => [
    'logging' => [
        'enabled' => false,
        'levels' => [],
        'retention_days' => 10,
        'cleanup_every' => 100,
    ],
],
```

`enabled` controls whether error logging is active at all.

`levels` is a minimum severity selection. Supported levels:

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

Examples:

```php
[]
```

logs nothing.

```php
['error']
```

logs `emergency`, `alert`, `critical` and `error`.

```php
['warning']
```

logs everything from `emergency` through `warning`.

```php
['debug']
```

logs every level.

`retention_days`:

```text
0  = unlimited retention
10 = approximately ten days
30 = approximately thirty days
```

`cleanup_every` determines how many log writes occur before old-file cleanup is checked.

## Rate limiting configuration

```php
'rate_limit' => [
    'enabled' => true,
    'limit' => 60,
    'window' => 60,
    'storage' => storage_path('cache/rate_limit'),
    'cleanup_after' => 86400,
    'cleanup_every' => 100,
],
```

`limit` is the maximum operations allowed in the window.

`window` is seconds.

Therefore:

```text
limit=60, window=60
```

means 60 operations per 60 seconds for that bucket.

Login can define separate account and IP limits:

```php
'/login' => [
    'account_field' => 'username',
    'account_limit' => 5,
    'account_window' => 300,
    'ip_limit' => 300,
    'ip_window' => 60,
],
```

## `app/Config/database.php`

Database configuration is kept separate because applications commonly have multiple connections and database-specific options.

Supported drivers:

```text
mysql
pgsql
sqlite
```

Named connections can be selected by passing the name to the database helper or CLI migration command.

## `app/Config/migrate.php`

This file is used only by the standalone browser migration utility. It is intentionally separate because `public/migrate.php` can operate without booting the normal application.

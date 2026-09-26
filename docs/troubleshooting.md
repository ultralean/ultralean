---
layout: docs
key: troubleshooting
title: Troubleshooting
---

# Troubleshooting

## The site returns 404 for every route

Check that the web server document root is `public/` and that rewrite support is enabled.

## Repeated slashes redirect unexpectedly

This is intentional. Canonical URL cleanup is always enabled.

```text
/admin///users
```

redirects to:

```text
/admin/users
```

Query strings are preserved.

## Database connection error

Check:

1. the selected `default` connection in `app/Config/database.php`
2. host
3. port
4. database name
5. username
6. password
7. PDO driver installation
8. database server availability

For CLI migrations:

```bash
php ul migrate
```

The CLI prints the database error.

For browser migrations, the error is displayed inside `public/migrate.php` so you can correct the configuration without losing the migration interface.

## `could not find driver`

PHP has PDO but the selected PDO driver is missing.

Check:

```bash
php -m
```

For SQLite you need `pdo_sqlite`.

For MySQL you need `pdo_mysql`.

For PostgreSQL you need `pdo_pgsql`.

## Logs are empty

Logging is disabled by default. Check:

```php
'errors' => [
    'logging' => [
        'enabled' => true,
        'levels' => ['error'],
    ],
],
```

## Too many login attempts

The example login has account and IP limits. Wait for the configured window to expire or adjust the configuration during development.

## Session/authentication problems

Check that PHP sessions can be started and that the application is served over a stable host. After login, ultralean regenerates the session to reduce session fixation risk.

## API returns HTML

Use:

```http
Accept: application/json
```

or place the endpoint under `/api/`.

## Production site refuses to load

Check whether:

```text
public/migrate.php
```

still exists.

In production this file is intentionally treated as a deployment hazard. Delete or move it after migrations.

## Static files are routed through PHP

Check the web server configuration and `public/.htaccess`. Existing files should be served directly.

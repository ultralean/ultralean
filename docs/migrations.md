---
layout: docs
key: migrations
title: Migrations
---

# Migrations

Migrations are plain SQL files stored in:

```text
app/Migrations/
```

The migration engine is in the optional CLI subsystem:

```text
system/Cli/Migration.php
```

## Create a migration

```bash
php ul make:migration create_users
```

The command creates a timestamped migration.

## File format

```sql
-- UP

CREATE TABLE users (
    id INTEGER PRIMARY KEY,
    username VARCHAR(100) NOT NULL
);

-- DOWN

DROP TABLE users;
```

## Driver-specific SQL

Universal SQL is often not truly universal. ultralean supports:

```text
name.sql
name.mysql.sql
name.pgsql.sql
name.sqlite.sql
```

A driver-specific file is selected for that database driver when available.

## Run migrations

```bash
php ul migrate
```

Specific connection:

```bash
php ul migrate --database=mysql
```

## Status

```bash
php ul migrate:status
```

## Rollback

```bash
php ul migrate:rollback
```

This rolls back the latest migration batch.

## Reset

```bash
php ul migrate:reset --force
```

This rolls back all applied migrations.

## Fresh

```bash
php ul migrate:fresh --force
```

This resets all migrations and runs them again.

## Migration batches and locking

Migrations are recorded in `ul_migrations`. Migration execution uses a lock so two migration processes do not casually run the same batch simultaneously.

## Password placeholders

For demo/seed credentials, readable migration source is supported:

```sql
INSERT INTO users (username, password_hash)
VALUES ('admin', {{ password('admin') }});
```

The placeholder is converted to a `PASSWORD_DEFAULT` hash before the SQL reaches PDO. Plain text is never stored in the database.

This is intended to make example migrations understandable. For production seed data, choose credentials and deployment procedures appropriate to your application.

## Browser migration utility

`public/migrate.php` uses the same CLI commands and migration engine. It is useful on shared hosting where SSH is unavailable.

Database/configuration errors are shown inside the page so the utility remains usable while configuration is being corrected.

### Production rule

If `env` is `production` and `public/migrate.php` still exists, the normal site refuses to load. Delete or move the file after migrations.

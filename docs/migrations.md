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

The migration engine is part of the optional CLI subsystem:

```text
system/Cli/Migration.php
```

## Create a migration

```bash
php ul make:migration create_users
```

The command creates a timestamped migration file.

## File format

Each migration contains an `-- UP` section and a `-- DOWN` section.

```sql
-- UP

CREATE TABLE users (
    id INTEGER PRIMARY KEY,
    username VARCHAR(100) NOT NULL
);

-- DOWN

DROP TABLE users;
```

The `-- UP` section applies the migration. The `-- DOWN` section reverses it.

## Driver-specific SQL

Universal SQL is not always portable across database engines. ultralean supports MySQL, PostgreSQL, and SQLite through PDO.

Use these filenames when database-specific SQL is required:

```text
name.sql
name.mysql.sql
name.pgsql.sql
name.sqlite.sql
```

The migration engine selects the driver-specific file when one exists for the active database connection. Otherwise, it uses the generic `.sql` file.

## Run migrations

```bash
php ul migrate
```

To use a specific configured database connection:

```bash
php ul migrate --database=mysql
```

The selected connection determines the database engine used by the migration engine and its placeholders.

## Check migration status

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

This rolls back all applied migrations. Use it carefully, as rollback operations can remove tables and data.

## Fresh

```bash
php ul migrate:fresh --force
```

This resets all applied migrations and runs them again.

## Migration batches and locking

Applied migrations are recorded in the `ul_migrations` table. Migrations are grouped into batches, and a lock helps prevent multiple migration processes from executing concurrently.

## Migration placeholders

ultralean supports migration-only placeholders for commonly needed values. Placeholders are compiled before their SQL is executed through PDO.

Placeholders must be written as SQL values without surrounding SQL quotes.

### Supported placeholders

| Placeholder | Description | Example output |
|---|---|---|
| `{{ password('value') }}` | Hashes a non-empty password using PHP's `PASSWORD_DEFAULT` algorithm. | A password hash |
| `{{ now() }}` | Current date and time in UTC by default. | `2026-11-01 17:30:00` (MySQL) |
| `{{ now('Asia/Karachi') }}` | Current date and time in the specified timezone. | `2026-11-01 22:30:00` (MySQL) |
| `{{ today() }}` | Current date in UTC by default. | `2026-11-01` |
| `{{ today('Asia/Karachi') }}` | Current date in the specified timezone. | `2026-11-01` |
| `{{ time() }}` | Current time in UTC by default. | `17:30:00` |
| `{{ time('Asia/Karachi') }}` | Current time in the specified timezone. | `22:30:00` |
| `{{ uuid() }}` | Generates a UUID version 4. | `550e8400-e29b-41d4-a716-446655440000` |

The date and time examples are illustrative; actual values depend on when the migration executes.

### Password placeholder

Use `password()` when inserting initial accounts or demo credentials:

```sql
INSERT INTO users (username, password_hash)
VALUES ('admin', {{ password('admin') }});
```

The placeholder generates a password hash before executing the SQL. The plain-text password is not stored in the database.

Password arguments support single or double quotes. To include the same quote character inside the argument, double it:

```sql
{{ password('it''s a password') }}
{{ password("say ""hello""") }}
```

Backslashes are treated as literal characters rather than PHP escape sequences. Do not rely on PHP string interpolation or PHP escape processing in placeholder arguments.

Use appropriate production credentials and deployment procedures for production seed data.

### Date and time placeholders

Date and time placeholders use UTC by default, regardless of the application's configured default timezone. This provides a consistent storage convention across environments.

An explicit timezone can be supplied using a valid PHP timezone identifier:

```sql
INSERT INTO events (name, created_at)
VALUES ('Example event', {{ now() }});
```

To calculate the value using a specific timezone:

```sql
INSERT INTO events (name, created_at)
VALUES ('Example event', {{ now('Asia/Karachi') }});
```

Use `today()` for date-only values and `time()` for time-only values.

Date and time placeholders generate values when a migration executes; they are not database functions that update automatically.

### Database-specific date and time formats

The `now()` placeholder generates a format appropriate to the selected database driver:

| Driver | Default UTC output | Intended storage convention |
|---|---|---|
| MySQL | `YYYY-MM-DD HH:MM:SS` | UTC in a timezone-less column |
| PostgreSQL | `YYYY-MM-DD HH:MM:SS+00:00` | Timestamp with time zone |
| SQLite | `YYYY-MM-DDTHH:MM:SSZ` | ISO 8601 UTC text |

When an explicit timezone is supplied, the generated value represents the current time in that timezone. The timezone suffix or offset must be interpreted correctly by the target column.

For PostgreSQL `TIMESTAMP WITHOUT TIME ZONE` and MySQL `DATETIME`, use a consistent UTC storage convention. These column types do not retain timezone information themselves.

The `today()`, `time()`, `password()`, and `uuid()` placeholders produce values that can be used across all three supported database engines.

### UUID placeholder

Use `uuid()` when a migration needs to insert a UUID identifier:

```sql
INSERT INTO application_settings (public_id, setting_name)
VALUES ({{ uuid() }}, 'maintenance_mode');
```

The generated UUID is a string. PostgreSQL's native `UUID` column can accept it, while MySQL and SQLite can store it in suitable text columns.

A UUID is an identifier, not a secret authentication token.

### Placeholder limitations

- Only supported placeholder names and argument formats are accepted.
- `now()`, `today()`, and `time()` accept no argument or one quoted timezone identifier.
- `uuid()` accepts no arguments.
- `password()` requires a non-empty quoted string.
- Placeholders should be used as SQL values, not inside SQL string literals or comments.
- Placeholder compilation is not a general-purpose SQL parser or PHP template engine.

## Browser migration utility

`public/migrate.php` uses the same migration commands and engine. It is useful on shared hosting where SSH access is unavailable.

Database and configuration errors are displayed on the page to help diagnose setup problems.

### Production rule

If the application environment is `production` and `public/migrate.php` still exists, the normal site refuses to load. Delete or move the file after completing migrations.

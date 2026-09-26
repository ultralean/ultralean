---
layout: docs
key: cli
title: CLI
---

# CLI

The executable is:

```text
ul
```

Run:

```bash
php ul
```

## Built-in commands

```bash
php ul
php ul migrate
php ul migrate:status
php ul migrate:rollback
php ul migrate:reset --force
php ul migrate:fresh --force
php ul make:migration create_users
php ul serve
```

Migrations can target a named connection:

```bash
php ul migrate --database=mysql
```

## Development server

The `serve` command starts PHP's built-in development server using the application's `public/` directory. It also uses `public/index.php` as the server router so application URLs reach ultralean while existing static files are served directly.

Start locally:

```bash
php ul serve
```

By default it listens on `127.0.0.1:8000`. Stop it with `Ctrl+C`.

### Local-network testing

To test the application from another device on the same LAN:

```bash
php ul serve --network
```

This binds the development server to `0.0.0.0`. The command prints the detected local Network URL(s), such as:

```text
Network: http://192.168.1.20:8000/
```

Open that address from another device on the same network. If Windows Firewall prompts you, allow PHP for the Private network.

`--network` is for local-network testing; it does not configure router port forwarding or publish the application to the public Internet.

### Port and host options

```bash
php ul serve --port=8080
php ul serve --network --port=8080
php ul serve --host=127.0.0.1 --port=8080
```

Show the command options with:

```bash
php ul serve --help
```

## Command discovery

Commands live in:

```text
system/Cli/Commands/
```

A command class exposes a static signature and a `handle()` method. The CLI scans command files automatically, so a new command does not need to be registered in a central list.

Conceptually:

```php
final class ExampleCommand
{
    public static function signature(): array
    {
        return [
            'name' => 'example',
            'description' => 'Run an example command.',
        ];
    }

    public function handle(array $arguments): int
    {
        // ...
        return 0;
    }
}
```

## Optional subsystem

`system/Cli/` is intentionally outside the minimal web runtime. If an application never uses migrations or CLI commands, normal web requests do not need to load that subsystem.

## Browser equivalent

`public/migrate.php` uses the same command classes for environments without SSH.

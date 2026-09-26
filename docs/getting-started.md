---
layout: docs
key: getting-started
title: Getting started
---

# Getting started

## 1. Download or clone

```bash
git clone https://github.com/ultralean/ultralean.git
cd ultralean
```

If you use a different repository name, adjust the URL accordingly.

## 2. PHP

Check PHP:

```bash
php -v
```

Use PHP 8.0 or newer.

Check PDO drivers:

```bash
php -m
```

You need the driver matching your database.

## 3. Web root

Point Apache/Nginx/IIS configuration at:

```text
ultralean/public/
```

Do not make the project root the public document root. Keeping `app/`, `system/` and configuration outside the public directory is an important security boundary.

For Apache, the included `public/.htaccess` sends non-file requests to `public/index.php` while existing static files are served directly.

## 4. Application configuration

Edit:

```text
app/Config/app.php
```

At minimum check:

```php
'name' => 'My Application',
'env' => 'development',
'debug' => true,
'timezone' => 'Asia/Karachi',
```

Use `production` and normally `debug => false` when deploying.

## 5. Database configuration

Edit:

```text
app/Config/database.php
```

Set `default` to the connection name you want to use.

For SQLite:

```php
'default' => 'sqlite',
```

For MySQL:

```php
'default' => 'mysql',
```

For PostgreSQL:

```php
'default' => 'pgsql',
```

## 6. Run migrations

```bash
php ul migrate
```

Check status:

```bash
php ul migrate:status
```

## 7. Start locally

ultralean includes a small development-server command, so you do not need to remember the PHP built-in server syntax:

```bash
php ul serve
```

Open `http://127.0.0.1:8000/`. The command uses `public/` as the web root and `public/index.php` as the built-in server router, while existing static files are served directly.

### Test from another device on your local network

Use network mode:

```bash
php ul serve --network
```

The server listens on `0.0.0.0` and prints the detected Network URL(s), for example:

```text
Network: http://192.168.1.20:8000/
```

Open that Network URL from another phone, tablet or computer on the same LAN. On Windows, allow PHP through Windows Firewall for your **Private network** if Windows asks.

Network mode is intended for local/LAN testing. It does not automatically publish the application to the public Internet or configure router port forwarding.

You can also choose a port:

```bash
php ul serve --port=8080
php ul serve --network --port=8080
```

For custom binding, use `--host=`:

```bash
php ul serve --host=127.0.0.1 --port=8080
```

## 8. Example login

The included demonstration account is:

```text
admin / admin
```

Change it immediately in the example application before using the project as the base for a real application.

## Shared hosting

If SSH is unavailable, use:

```text
public/migrate.php
```

The page uses the same migration command classes as the CLI. It reports configuration/database errors in the page rather than collapsing the entire migration utility into a generic 500 response.

After production migrations, delete or move `public/migrate.php`.

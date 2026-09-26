# ultralean

**A small, plain-PHP application foundation for PHP 8+.**

ultralean is designed for developers who want a clean starting point for building **web applications and JSON APIs** without committing to a large framework or a large runtime stack.

It keeps PHP, SQL, PDO, HTML and HTTP close to the surface. Components are loaded when they are needed, and application-specific features remain in `app/` instead of being forced into the system core.

## Why ultralean?

- Plain PHP 8+ and PDO
- Works for HTML web applications and JSON APIs
- Lazy database connections
- Lazy session/authentication usage
- Simple string-based routes
- Route groups, named routes and URL generation
- Automatic canonical URL cleanup
- Lightweight model/query helpers without an ORM
- Raw PDO remains available
- MySQL, PostgreSQL and SQLite
- SQL migrations with CLI and browser-based migration utility
- CSRF protection
- File-backed rate limiting
- Configurable error handling and optional logging
- UTC model timestamps with explicit application-timezone conversion
- Simple PHP views with layouts and sections
- No dependency container, ORM, template compiler or reflection-heavy machinery
- CLI subsystem is isolated from normal web requests

## Requirements

- PHP 8.0 or newer
- A web server capable of rewriting requests to `public/index.php` (Apache is supported by the included `.htaccess`)
- PDO
- PDO driver for the database you choose:
  - `pdo_sqlite`
  - `pdo_mysql`
  - `pdo_pgsql`
- Composer is optional; ultralean itself does not require Composer packages.

## Quick start

1. Clone this repository `https://github.com/ultralean/ultralean.git` or download it `https://github.com/ultralean/ultralean/archive/refs/heads/master.zip` and extract it.
2. Copy the project to your web server.
3. Point the web server document root to `public/`.
4. Edit `app/Config/app.php`.
5. Edit `app/Config/database.php`.
6. Run the first migration:

```bash
php ul migrate
```

7. Open the application in a browser.
8. The included example application provides an admin login:

```text
Username: admin
Password: admin
```

Change the password immediately for anything beyond local demonstration.

### Local development or hosting with SSH

If you are working in a local development environment or on a hosting provider that gives you SSH access, run the following command from the root directory of ultralean to view all available CLI commands:

```bash
php ul
```

This displays the list of all available commands for tasks such as database migrations and serve command.

### Shared hosting without SSH

The project includes `public/migrate.php` as a browser-based alternative to the CLI migration commands. It uses the same CLI command and migration implementation.

In production, ultralean deliberately blocks the normal site while `public/migrate.php` remains present. Run the required migrations, then delete or move `public/migrate.php` to somewhere else outside of public directory.

## Web + API in one application

Web routes can return HTML:

```php
Router::get('/users', 'UserController@index');
```

API routes can return JSON:

```php
Router::get('/api/users', 'Api\UserController@index');
```

And controllers can return JSON directly:

```php
return Response::json([
    'ok' => true,
    'users' => $users,
]);
```

The included example exposes:

```text
GET /api/health
```

API errors are returned as JSON when the request accepts JSON or uses an `/api/` path.

## Documentation

The documentation is published at:

**https://ultralean.github.io/ultralean/**

Start with:

- [Documentation home](https://ultralean.github.io/ultralean/)
- [Step-by-step tutorial](https://ultralean.github.io/ultralean/tutorial/)
- [Getting started](https://ultralean.github.io/ultralean/getting-started/)
- [Architecture](https://ultralean.github.io/ultralean/architecture/)
- [Routing](https://ultralean.github.io/ultralean/routing/)
- [Database and models](https://ultralean.github.io/ultralean/database/)
- [Migrations](https://ultralean.github.io/ultralean/migrations/)
- [Security](https://ultralean.github.io/ultralean/security/)
- [Deployment](https://ultralean.github.io/ultralean/deployment/)

The account landing page is:

**https://ultralean.github.io/**

## License

ultralean is free and unencumbered software released into the public domain under The Unlicense.

You are free to use, copy, modify, publish, distribute, compile, sell, and otherwise use ultralean for any purpose, including commercial and non-commercial purposes, without requiring permission or attribution.

See the LICENSE file for the complete license text.

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

1. Copy the project to your web server.
2. Point the web server document root to `public/`.
3. Edit `app/Config/app.php`.
4. Edit `app/Config/database.php`.
5. Run the first migration:

```bash
php ul migrate
```

6. Open the application in a browser.
7. The included example application provides an admin login:

```text
Username: admin
Password: admin
```

Change the password immediately for anything beyond local demonstration.

### Shared hosting without SSH

The project includes `public/migrate.php` as a browser-based alternative to the CLI migration commands. It uses the same CLI command and migration implementation.

In production, ultralean deliberately blocks the normal site while `public/migrate.php` remains present. Run the required migrations, then delete or move `public/migrate.php`.

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

## GitHub

Project: https://github.com/ultralean/ultralean

The repository is named `ultralean` in this documentation. The shorter `ul` name also works if you prefer it; the executable remains `php ul` either way.

## License

Add the license you want to use before publishing the repository. MIT is a common choice for a small reusable PHP project, but the repository owner should choose the license that matches the project's intended terms.

---
title: Documentation home
key: index
layout: docs
---

# ultralean documentation

**ultralean** is a small, plain-PHP application foundation for PHP 8+.

It gives you a practical structure for building web applications and JSON APIs while keeping PHP, SQL, PDO, HTTP and HTML close to the surface.

## Choose your path

### Learn by building

The **[step-by-step tutorial]({{ '/tutorial/' | relative_url }})** is a separate practical learning path. It builds a small application from the project setup through public pages, database, admin login, CRUD, security, API and deployment.

### Use the reference documentation

Use the reference documentation in the left sidebar. It covers installation, architecture, configuration, routing, HTTP, database access, migrations, APIs, security, errors, rate limiting, date/time, views, CLI and deployment.

## Documentation map

- [Getting started]({{ '/getting-started/' | relative_url }}) — install and run the included example.
- [Architecture]({{ '/architecture/' | relative_url }}) — understand the project directories and lazy-loading design.
- [Configuration]({{ '/configuration/' | relative_url }}) — configure the application and database.
- [Routing]({{ '/routing/' | relative_url }}) — routes, groups, names, middleware and canonical URLs.
- [HTTP requests & responses]({{ '/http/' | relative_url }}) — request input, JSON, headers and responses.
- [Database & models]({{ '/database/' | relative_url }}) — PDO, named connections and the thin model layer.
- [Migrations]({{ '/migrations/' | relative_url }}) — create, run, rollback and inspect migrations.
- [Web APIs]({{ '/api/' | relative_url }}) — build JSON endpoints alongside normal web pages.
- [Authentication & security]({{ '/security/' | relative_url }}) — sessions, CSRF, passwords and security practices.
- [Errors & logging]({{ '/errors/' | relative_url }}) — production-safe errors and optional logs.
- [Rate limiting & cache]({{ '/rate-limiting/' | relative_url }}) — file-backed limits and cleanup behavior.
- [Date & time]({{ '/date-time/' | relative_url }}) — UTC persistence and explicit timezone conversion.
- [Views]({{ '/views/' | relative_url }}) — layouts, sections, partials and plain PHP templates.
- [CLI]({{ '/cli/' | relative_url }}) — `php ul` commands and command discovery.
- [Deployment]({{ '/deployment/' | relative_url }}) — Apache/shared hosting and production setup.
- [Troubleshooting]({{ '/troubleshooting/' | relative_url }}) — common configuration and runtime problems.

## Design goals

- Plain PHP 8+ and PDO.
- Lazy loading by default.
- No ORM, DI container or template compiler.
- SQL remains visible and raw PDO remains available.
- MySQL, PostgreSQL and SQLite support.
- Web applications and JSON APIs in one application.
- Application-specific behavior belongs in `app/`.
- Optional CLI/migration code stays outside the normal web bootstrap.

## Example application

The repository includes a working sample with public pages, an admin login, session authentication, CSRF protection, rate limiting, account/password management, a JSON endpoint, migrations and CLI/browser migration utilities.

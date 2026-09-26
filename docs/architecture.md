---
layout: docs
key: architecture
title: Architecture
---

# Architecture

## Project structure

```text
ultralean/
├── app/
│   ├── Auth/
│   ├── Config/
│   ├── Controllers/
│   ├── Middleware/
│   ├── Models/
│   ├── Migrations/
│   ├── Routes/
│   ├── Views/
│   └── Storage/
├── public/
│   ├── index.php
│   ├── migrate.php
│   └── assets/
├── system/
│   ├── Core/
│   ├── Helpers/
│   ├── Middleware/
│   └── Cli/
├── ul
├── composer.json
└── README.md
```

## `public/`

This is the web-server document root.

`index.php` is the normal application entry point.

`migrate.php` is an optional standalone migration interface for environments where CLI access is unavailable.

Static assets are served directly by the web server. The application router does not inspect every CSS, JavaScript, image or other static file.

## `system/Core/`

Contains reusable runtime pieces such as:

- application bootstrap/runtime
- router
- request/response objects
- controller base class
- database wrapper
- model/query layer
- sessions
- CSRF
- validation
- views
- error handling
- logging
- clock/date-time handling

## `system/Helpers/`

Contains small global helpers such as path, configuration, escaping and database convenience functions.

## `system/Middleware/`

Contains reusable middleware such as CSRF and rate limiting.

## `system/Cli/`

Optional CLI and migration functionality. Normal web requests do not load this subsystem.

Commands are discovered from `system/Cli/Commands/*Command.php`. Adding a command does not require editing a central command registry.

## `app/`

This is where application-specific behavior belongs.

Authentication is deliberately here rather than in Core. A project can replace the example authentication implementation without changing the reusable runtime.

## Lazy behavior

The architecture avoids initializing expensive or unnecessary pieces until they are used.

Examples:

- Database connections are opened on first database use.
- Sessions start when session operations require them.
- Authentication resolves the current user lazily.
- Controllers are instantiated after a route matches.
- Middleware is instantiated for routes that actually use it.
- Views are included only when rendered.
- CLI classes are outside the normal web bootstrap.

## Application lifecycle

Normal web request:

```text
web server
   ↓
public/index.php
   ↓
system/bootstrap.php
   ↓
App::run()
   ↓
register routes
   ↓
canonical URL check
   ↓
Router dispatch
   ↓
matched middleware
   ↓
controller
   ↓
view / JSON / redirect
   ↓
Response
```

Static request:

```text
web server
   ↓
existing file in public/
   ↓
served directly
```

CLI request:

```text
php ul
   ↓
system/Cli/bootstrap.php
   ↓
System\Cli\Cli
   ↓
command discovery
   ↓
command class
```

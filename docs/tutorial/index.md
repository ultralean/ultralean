---
title: Step-by-step tutorial
key: tutorial-index
layout: tutorial
---

# Build a complete ultralean application

This tutorial is the **practical learning path** for ultralean.

Instead of explaining every feature in isolation, you will build a small application from an empty ultralean project and add the important pieces one at a time.

## What we are building

The finished application is a small content manager:

```text
Public site
├── Home
├── About
├── Posts
└── Post details

Admin
├── Login
├── Dashboard
├── Post list
├── Create post
├── Edit post
├── Delete post
└── Account / password

API
└── GET /api/health
```

The tutorial deliberately touches almost every important ultralean feature:

- project structure
- configuration
- routes
- route names
- controllers
- views and layouts
- partials
- migrations
- SQLite/MySQL/PostgreSQL configuration
- models and CRUD
- sessions
- authentication
- password hashing
- CSRF
- validation
- rate limiting
- middleware
- JSON APIs
- error handling
- logging
- UTC date/time
- CLI migrations
- browser migrations
- deployment

## Before starting

You need:

- PHP 8.0+
- a web server such as Apache
- the required PDO database driver
- a code editor such as VS Code
- a terminal

For the easiest first run, use SQLite if your PHP installation has `pdo_sqlite` enabled.

## Tutorial sequence

1. [Create the project]({{ '/tutorial/01-project/' | relative_url }})
2. [Build pages, routes and views]({{ '/tutorial/02-pages/' | relative_url }})
3. [Create the database and migrations]({{ '/tutorial/03-database/' | relative_url }})
4. [Add models and CRUD]({{ '/tutorial/04-models/' | relative_url }})
5. [Build the admin login]({{ '/tutorial/05-admin-login/' | relative_url }})
6. [Protect the admin area]({{ '/tutorial/06-admin-security/' | relative_url }})
7. [Add a JSON API]({{ '/tutorial/07-api/' | relative_url }})
8. [Use runtime features correctly]({{ '/tutorial/08-runtime/' | relative_url }})
9. [Deploy the application]({{ '/tutorial/09-deploy/' | relative_url }})
10. [Finish with the checklist]({{ '/tutorial/10-checklist/' | relative_url }})

By the end, you should be able to start a new ultralean application without copying the framework's example application blindly.

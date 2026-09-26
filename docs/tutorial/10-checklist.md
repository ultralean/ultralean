---
title: Tutorial 10 — Final checklist
key: tutorial-10
layout: tutorial
---

# 10. Final checklist

Congratulations. You now have a small application using the main parts of ultralean.

## Application structure

- [ ] `public/` is the web root.
- [ ] Application code is under `app/`.
- [ ] Framework code is under `system/`.
- [ ] Configuration is under `app/Config/`.
- [ ] Routes are under `app/Routes/`.
- [ ] Migrations are under `app/Migrations/`.
- [ ] Storage is under `app/Storage/`.

## Web application

- [ ] Home page works.
- [ ] About page works.
- [ ] Post list works.
- [ ] Post detail works.
- [ ] Named routes are used for generated URLs.
- [ ] Views use a shared layout.

## Database

- [ ] Migrations can be applied.
- [ ] Migration status is understood.
- [ ] Rollback is understood.
- [ ] Destructive commands are used only intentionally.
- [ ] Database credentials are not committed.

## Admin

- [ ] Login works.
- [ ] Passwords are hashed.
- [ ] Logout works.
- [ ] Admin routes require authentication.
- [ ] State-changing forms use CSRF protection.
- [ ] Input is validated server-side.
- [ ] Login attempts are rate-limited.
- [ ] Password changes are protected.

## API

- [ ] `/api/health` returns JSON.
- [ ] JSON input is validated.
- [ ] HTTP status codes are meaningful.
- [ ] API authentication is added if the application requires it.

## Operations

- [ ] Debug is disabled in production.
- [ ] Logging is enabled only when needed.
- [ ] Logs have an appropriate retention policy.
- [ ] Stored timestamps use UTC.
- [ ] Application timezone is configured.
- [ ] Temporary framework data has cleanup rules.

## Deployment

- [ ] Web root points to `public/`.
- [ ] Required PHP PDO driver is installed.
- [ ] Storage directories are writable where required.
- [ ] HTTPS is enabled by the hosting environment.
- [ ] Temporary migration web access is removed after use.
- [ ] A database backup strategy exists.

## Where to go next

You now have two useful ways to continue:

- Use the [reference documentation]({{ '/' | relative_url }}) when you need to look up a feature.
- Start another application and use this tutorial as a checklist rather than copying the sample application.

The framework is intentionally small. Keep application-specific decisions in `app/` and modify Core only when you are deliberately changing the framework itself.

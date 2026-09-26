---
title: Tutorial 9 — Deployment
key: tutorial-09
layout: tutorial
---

# 9. Deploy the application

The framework is designed to work on ordinary PHP hosting as well as local development environments.

## 9.1 Set the document root

The web server should point to:

```text
/path/to/your-app/public
```

Do not expose the project root as the public document root.

## 9.2 Configure production

Set the application environment to production and disable debug output.

Check:

- database credentials
- application URL
- timezone
- upload/storage permissions
- logging configuration
- session configuration
- rate-limit configuration

## 9.3 Run migrations

If SSH is available:

```bash
php ul migrate
```

If SSH is unavailable, use `public/migrate.php` temporarily. After running the required migrations, remove or move that migration endpoint as recommended by the deployment documentation.

## 9.4 Protect secrets

Do not commit production database passwords, SMTP passwords or other secrets to Git.

Keep configuration outside version control when your hosting arrangement permits it.

## 9.5 Apache

The supplied `.htaccess` serves existing files directly and sends application URLs to `public/index.php`.

This is important because the router should not waste work handling CSS, JavaScript, images or other static files.

## 9.6 PHP extensions

Confirm that the production PHP build has the PDO driver you actually configured:

```text
pdo_mysql
pdo_pgsql
pdo_sqlite
```

You only need the driver for the database you use.

## 9.7 Final production test

Test:

1. public home page
2. public about page
3. post list
4. post detail
5. login
6. invalid login
7. admin dashboard
8. create/edit/delete post
9. logout
10. `/api/health`
11. missing page / 404
12. invalid method / 405
13. CSRF failure
14. migration status
15. log creation if enabled

## Next

Continue to [Tutorial 10 — Final checklist]({{ '/tutorial/10-checklist/' | relative_url }}).

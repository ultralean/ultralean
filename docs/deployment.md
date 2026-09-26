---
layout: docs
key: deployment
title: Deployment
---

# Deployment

## Document root

Set the web server document root to:

```text
/path/to/ultralean/public
```

Never expose the repository root as the public web root.

## Production settings

Use:

```php
'env' => 'production',
'debug' => false,
```

Review:

- database credentials
- session configuration
- upload permissions
- file permissions
- logging policy
- rate limits
- API authentication
- HTTPS

## Migration utility

Before production traffic:

1. run migrations
2. verify the application
3. delete or move `public/migrate.php`

When `env` is `production`, ultralean blocks the normal application if that file is still present.

## Storage permissions

PHP must be able to write to the required storage directories:

```text
app/Storage/logs/
app/Storage/cache/
app/Storage/uploads/
```

Do not make the entire project writable by the web user if only these directories need write access.

## Apache

The included `public/.htaccess` serves existing files directly and rewrites application URLs to `index.php`.

Enable `mod_rewrite` and allow `.htaccess` overrides for the public directory.

## Nginx

Configure the server so that existing static files are served directly and non-file requests fall back to:

```text
/public/index.php
```

Do not route arbitrary requests to PHP files outside `public/`.

## HTTPS

Use HTTPS for production applications, especially applications containing authentication or personal data.

## GitHub Pages

GitHub Pages is for the **documentation**, not for running the PHP application. GitHub Pages serves static content and does not execute PHP server-side. citeturn0search2turn0search8

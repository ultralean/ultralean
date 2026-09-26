---
title: Tutorial 8 — Runtime features
key: tutorial-08
layout: tutorial
---

# 8. Use runtime features correctly

At this point the application works. Now we make it easier to operate and understand.

## 8.1 Error handling

Development errors should show enough information to fix the problem. Production errors should not expose source paths, SQL details or stack traces.

The error handler provides separate behavior for debug and production modes.

## 8.2 Logging

Logging is disabled by default. Enable it when the application needs operational logs:

```php
'errors' => [
    'logging' => [
        'enabled' => true,
        'levels' => ['error'],
    ],
],
```

Logs are written under:

```text
app/Storage/logs/
```

Retention and cleanup are configurable so old logs do not grow forever.

## 8.3 UTC timestamps

Store application timestamps in UTC. The `Clock` class lets the application explicitly convert them to the configured application timezone.

For example:

```php
echo $post->created_at->inApplicationTimezone()->format();
```

For large lists, sending UTC values to JavaScript and formatting them in the browser can avoid unnecessary server-side conversion work.

## 8.4 Cache and temporary files

Do not blindly delete everything under `app/Storage/cache/`. Application developers may put intentionally long-lived cache there.

Framework-owned temporary stores, such as rate-limit data, should own their own TTL and cleanup rules.

## 8.5 Canonical URLs

Repeated slashes in GET/HEAD URLs are redirected to a clean URL:

```text
/admin///users////123
```

becomes:

```text
/admin/users/123
```

Query strings are preserved. State-changing requests are not redirected for canonicalization because redirecting them could interfere with request bodies.

## 8.6 Use raw PDO when appropriate

You do not need to fight the model layer for complex SQL. The database helpers expose PDO directly when a specialized query is clearer as SQL.

## Next

Continue to [Tutorial 9 — Deploy the application]({{ '/tutorial/09-deploy/' | relative_url }}).

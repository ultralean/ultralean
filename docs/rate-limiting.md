---
layout: docs
key: rate-limiting
title: Rate limiting and cache
---

# Rate limiting and cache

## Rate limiting

The built-in limiter is file-backed and intended for lightweight protection without requiring Redis or another external service.

Configuration:

```php
'rate_limit' => [
    'enabled' => true,
    'limit' => 60,
    'window' => 60,
    'storage' => storage_path('cache/rate_limit'),
    'cleanup_after' => 86400,
    'cleanup_every' => 100,
],
```

### `limit`

Maximum number of operations permitted for one bucket.

### `window`

Window length in seconds.

For example:

```text
limit = 60
window = 60
```

means 60 requests in 60 seconds.

### Login protection

The example login uses two buckets:

```text
account bucket: username
IP bucket:      source address
```

The account bucket is strict, while the IP bucket is broader. This matters for corporate networks where many people can legitimately share one public IP address.

This does not claim to be an impossible-to-bypass anti-abuse system. A distributed attacker can use multiple source addresses. Stronger applications can add external abuse controls, device reputation or a distributed rate-limit store.

## Cleanup

Rate-limit files are temporary. `cleanup_after` determines their age threshold.

```text
43200  = 12 hours
86400  = 24 hours
172800 = 48 hours
```

`cleanup_every` prevents a filesystem scan on every operation.

For example:

```php
'cleanup_after' => 86400,
'cleanup_every' => 100,
```

means cleanup is checked after approximately every 100 rate-limit operations.

## General cache directory

Do not assume every file under `app/Storage/cache/` can be deleted safely. Applications can put their own cache there.

Framework-owned temporary stores should define their own TTL and cleanup behavior. The built-in rate-limit store is one example.

## Why file-backed?

It requires no extra service and works on ordinary PHP hosting.

For a high-traffic multi-server deployment, a shared/distributed store such as Redis may be more appropriate. ultralean does not force it into Core.

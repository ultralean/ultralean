
## RateLimit middleware

ultralean includes a file-based `RateLimitMiddleware` for limiting repeated requests.

It uses filesystem storage and file locking to coordinate requests that share the same rate-limit identity.

### 1. Enabling rate limiting

Configure rate limiting in:

```text
app/Config/app.php
```

Example:

```php
'rate_limit' => [
    'enabled' => true,
    'limit' => 60,
    'window' => 60,
    'storage' => storage_path('cache/rate_limit'),
    'cleanup_every' => 100,
    'cleanup_after' => 86400,
],
```

This example allows up to 60 requests per IP address per route within a 60-second window.

### 2. Applying middleware

Register the middleware on individual routes:

```php
Router::post(
    '/login',
    'AuthController@login',
    ['RateLimit']
);
```

Or apply it to a group:

```php
Router::group('', ['RateLimit'], function () {
    Router::post('/login', 'AuthController@login');
    Router::post('/register', 'AuthController@register');
});
```

### 3. Per-route configuration

Routes can have their own limits:

```php
'rate_limit' => [
    'enabled' => true,

    'limit' => 60,
    'window' => 60,

    'routes' => [
        '/login' => [
            'limit' => 5,
            'window' => 60,
        ],

        '/register' => [
            'limit' => 3,
            'window' => 300,
        ],
    ],
],
```

Route configuration overrides the global defaults.

### 4. Account-based limiting

For endpoints such as login, it can be useful to limit attempts by both account identifier and IP address.

Example:

```php
'rate_limit' => [
    'enabled' => true,

    'limit' => 60,
    'window' => 60,

    'routes' => [
        '/login' => [
            'account_field' => 'email',

            'account_limit' => 5,
            'account_window' => 300,

            'ip_limit' => 20,
            'ip_window' => 300,
        ],
    ],
],
```

The middleware uses the configured request field to identify the account.

Account identifiers are normalized to lowercase and trimmed before being hashed for storage.

When `account_field` is configured, both account and IP limits are applied when the account field contains a value.

The IP limit is still applied when the account field is empty.

### 5. Rate-limit responses

When a limit is exceeded, the middleware throws an exception with HTTP status 429.

For non-CLI requests, it also sets:

```http
Retry-After: 60
```

The actual value depends on the remaining rate-limit window.

### 6. Storage and cleanup

Rate-limit records are stored as individual files:

```text
storage/cache/rate_limit/
```

Each identity receives a SHA-256-based filename.

The middleware uses file locking to coordinate concurrent updates.

Cleanup is checked periodically using:

- `cleanup_every` — how often cleanup is considered.
- `cleanup_after` — the age after which expired files can be removed.

### Security considerations

- Apply rate limiting to login, registration, password reset and other sensitive endpoints.
- Use account-based limits where appropriate.
- Keep rate-limit storage outside the public web root.
- Make sure the storage directory is writable by PHP.
- Do not rely exclusively on IP-based limiting.
- Consider a shared rate-limit backend when deploying multiple servers.
- Rate limiting does not replace account lockout policies or abuse monitoring.

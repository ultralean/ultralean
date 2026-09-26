---
layout: docs
key: security
title: Authentication and security
---

# Authentication and security

## Authentication is application code

Authentication is under:

```text
app/Auth/Auth.php
```

This is deliberate. Different applications have different authentication requirements, so a reusable runtime should not force one authentication architecture.

The example exposes:

```php
auth()->check();
auth()->id();
auth()->user();
auth()->login($user);
auth()->logout();
```

## Sessions

Session support is a reusable Core component:

```php
$session = app('session');
```

Operations include:

```php
$session->get('key');
$session->set('key', $value);
$session->has('key');
$session->forget('key');
$session->flash('message', 'Saved');
$session->pullFlash('message');
$session->regenerate();
$session->destroy();
```

Authentication regenerates the session during login to reduce session fixation risk.

## CSRF

Use the CSRF middleware for browser state-changing requests:

```php
Router::post('/profile', 'ProfileController@update', ['Csrf']);
```

In a form:

```php
<?= csrf_field() ?>
```

The token is stored in the session and verified on submission.

For stateless APIs authenticated by tokens, use an API-specific authentication design rather than blindly applying browser CSRF rules.

## Passwords

Use PHP's native password API:

```php
password_hash($password, PASSWORD_DEFAULT);
password_verify($password, $hash);
```

Migration password placeholders use the same `PASSWORD_DEFAULT` mechanism.

## SQL injection

Use prepared statements:

```php
$stmt = db()->prepare(
    'SELECT * FROM users WHERE email = :email'
);
$stmt->execute(['email' => $email]);
```

Do not concatenate untrusted values into SQL.

## Production configuration

Use:

```php
'env' => 'production',
'debug' => false,
```

Do not leave the browser migration utility exposed in production.

## Uploads

Treat uploaded files as untrusted. Validate:

- size
- MIME type/content
- extension
- filename
- storage location
- authorization

Prefer storing executable-sensitive uploads outside the public directory when the application does not need direct public access.

## Headers

The public Apache configuration includes `X-Content-Type-Options: nosniff`. Add additional headers such as CSP, HSTS and framing policy according to the application's deployment requirements.

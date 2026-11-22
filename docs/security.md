
---
layout: docs
key: security
title: Authentication and security
---

# Authentication and security

ultralean provides reusable security components while leaving application-specific authentication and authorization decisions to the developer.

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

Session support is provided by a reusable Core component:

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

See [Sessions]({{ '/session/' | relative_url }}) for more information.

## CSRF protection

Browser state-changing requests should use CSRF middleware:

```php
Router::post(
    '/profile',
    'ProfileController@update',
    ['Csrf']
);
```

Include a CSRF field in every applicable form:

```php
<form method="POST" action="/profile">
    <?= csrf_field('profile') ?>

    <button type="submit">Save</button>
</form>
```

The CSRF token is stored in the session and verified when the request is submitted.

For stateless APIs authenticated by tokens, use an API-specific authentication design rather than blindly applying browser CSRF rules.

See [Forms]({{ '/forms/' | relative_url }}) for form protection and old-input management.

## Passwords

Use PHP's native password API:

```php
$hash = password_hash(
    $password,
    PASSWORD_DEFAULT
);

if (password_verify($password, $hash)) {
    // Password is valid.
}
```

Never store plaintext passwords or restore passwords through old-input functionality.

## SQL injection

Always use prepared statements:

```php
$stmt = db()->prepare(
    'SELECT * FROM users WHERE email = :email'
);

$stmt->execute([
    'email' => $email,
]);
```

Do not concatenate untrusted values into SQL statements.

## Input validation

Validate user input before using it:

```php
$validator = validate($request->all(), [
    'name' => 'required|min:3|max:100',
    'email' => 'required|email',
]);

if ($validator->fails()) {
    return back()->withData();
}
```

Validation does not replace output escaping or database parameterization.

See [Validation]({{ '/validator/' | relative_url }}) for supported rules.

## Old form input

Old-input restoration uses separate random form identifiers, rather than relying on one global flash-data entry.

This helps isolate multiple forms and browser tabs.

Sensitive fields, such as passwords and tokens, are excluded from preserved input.

Old form data also expires automatically after its configured lifetime.

Remember that old-input filtering is not a replacement for server-side authorization or validation.

## Uploads

Treat uploaded files as untrusted.

Validate:

- File size
- MIME type and actual content
- Extension
- Filename
- Storage location
- Authorization

Prefer storing executable-sensitive uploads outside the public directory when the application does not need direct public access.

## Production configuration

Use:

```php
'env' => 'production',
'debug' => false,
```

Do not expose the browser migration utility in production.

## Security headers

The public Apache configuration includes:

```http
X-Content-Type-Options: nosniff
```

Consider additional headers such as:

- Content-Security-Policy (CSP)
- Strict-Transport-Security (HSTS)
- X-Frame-Options or CSP frame-ancestors
- Referrer-Policy

Configure them according to your application's deployment requirements.

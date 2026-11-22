
---
layout: docs
key: session
title: Sessions
---

# Sessions

ultralean provides a lightweight Session class for storing data between HTTP requests.

Sessions are commonly used for authentication, temporary messages and user-specific application state.

## 1. Accessing the session

Use the global helper:

```php
$session = session();
```

You can also retrieve it through the application:

```php
$session = app('session');
```

## 2. Storing data

```php
session()->set('username', 'Imran');
```

## 3. Retrieving data

```php
$username = session()->get('username');
```

Provide a default:

```php
$username = session()->get(
    'username',
    'Guest'
);
```

## 4. Checking data

```php
if (session()->has('username')) {
    // Value exists.
}
```

## 5. Removing data

Remove a specific entry:

```php
session()->forget('username');
```

## 6. Flash data

Flash data is intended for temporary messages that should be available on a subsequent request.

Store a message:

```php
session()->flash(
    'success',
    'Profile updated successfully.'
);
```

Retrieve it:

```php
$message = session()->pullFlash('success');
```

Flash data is useful for displaying success and error notifications after redirects.

Example:

```php
session()->flash('success', 'Saved successfully.');

return $this->redirect('/profile');
```

In the destination view:

```php
<?php if ($message = session()->pullFlash('success')): ?>
    <div class="alert alert-success">
        <?= e($message) ?>
    </div>
<?php endif; ?>
```

## 7. Session regeneration

Regenerate the session:

```php
session()->regenerate();
```

Session regeneration is important during authentication because it helps reduce session fixation risks.

ultralean's authentication workflow regenerates the session during login.

## 8. Destroying the session

```php
session()->destroy();
```

Use this when logging out or clearing the current session.

## 9. Sessions and forms

The session is also used internally by ultralean's form-state functionality.

FormState stores preserved form input in the session with:

- Independent form identifiers.
- A 600-second expiration.
- A maximum of 10 records.
- A maximum data size of 64 KB per record.
- One-time consumption.

Developers should normally use:

```php
return back()->withData();
```

instead of manually storing form input in session flash data.

See [Forms]({{ '/forms/' | relative_url }}) for the complete explanation.

## 10. Security considerations

- Never store plaintext passwords in sessions.
- Regenerate sessions during authentication.
- Destroy sessions during logout.
- Use HTTPS in production.
- Use secure, HTTP-only and appropriate SameSite session cookies.
- Do not expose session identifiers to URLs.
- Avoid storing unnecessarily large objects or sensitive data in sessions.
- Apply authorization checks independently of session authentication.

## Available methods

| Method | Purpose |
|---|---|
| `get()` | Retrieve a value |
| `set()` | Store a value |
| `has()` | Check whether a value exists |
| `forget()` | Remove a value |
| `flash()` | Store temporary data |
| `pullFlash()` | Retrieve temporary data |
| `regenerate()` | Regenerate the session ID |
| `destroy()` | Destroy the session |

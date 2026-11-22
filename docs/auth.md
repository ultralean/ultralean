
---
layout: docs
key: auth
title: Authentication
---

# Authentication

ultralean provides a lightweight `Auth` class for managing authenticated users through PHP sessions.

Authentication is configurable, allowing applications to use their own account models, eligibility rules and session keys.

## 1. Accessing authentication

Use the global `auth()` helper:

```php
$auth = auth();
```

If your application defines multiple authentication guards, specify one:

```php
$userAuth = auth('user');
$adminAuth = auth('admin');
```

The default guard is configured in:

```text
app/Config/auth.php
```

## 2. Checking authentication

Check whether a user is authenticated:

```php
if (auth()->check()) {
    // User is authenticated.
}
```

`check()` also verifies that the account still exists and is eligible to authenticate.

If the account is no longer eligible, the stored authentication session is cleared.

## 3. Retrieving the authenticated user

```php
$user = auth()->user();
```

The returned value can be an array, an object or `null`.

For example:

```php
$user = auth()->user();

if ($user !== null) {
    echo e($user['name']);
}
```

The exact access syntax depends on the account representation returned by your configured account finder.

## 4. Retrieving the authenticated ID

```php
$userId = auth()->id();
```

Returns an integer, string or `null`.

This is useful when you only need the authenticated account ID:

```php
$userId = auth()->id();

if ($userId !== null) {
    // Load related records.
}
```

## 5. Logging in

Use the account ID:

```php
auth()->login($user['id']);
```

Before logging in, the Auth class checks whether the account exists and is eligible.

It also regenerates the session ID before storing the authenticated account ID, helping protect against session fixation.

An exception is thrown if the account is not eligible or session regeneration fails.

## 6. Logging out

```php
auth()->logout();
```

Logout removes the authentication session value and regenerates the session ID.

## 7. Protecting routes

Use middleware to protect authenticated pages.

For example:

```php
Router::group('', ['Auth'], function () {
    Router::get('/dashboard', 'DashboardController@index');
    Router::get('/profile', 'ProfileController@index');
});

Router::group('', ['Guest'], function () {
    Router::get('/login', 'AuthController@showLogin');
});
```

The `Auth` and `Guest` middleware names above are application-level examples. Implement them according to your application's access rules.

## 8. Multiple guards

Different authentication guards can use different session keys and account finders.

For example:

```php
auth('user')->check();
auth('admin')->check();
```

This allows an application to maintain separate user and administrator authentication contexts.

## 9. Account eligibility

The Auth class receives a callback responsible for finding eligible accounts.

The callback should return an array or object for an eligible account, and `null` otherwise.

For example, an application might only allow active accounts:

```php
static function (int|string $id): ?array {
    $account = User::where('id', '=', $id)->first();

    if (
        $account === null ||
        ($account['status'] ?? null) !== 'active'
    ) {
        return null;
    }

    return $account;
}
```

This keeps account-specific business rules outside the core authentication class.

## Security notes

- Always use HTTPS in production.
- Regenerate session IDs during authentication.
- Check account eligibility rather than trusting a stored session ID alone.
- Never store plaintext passwords in sessions.
- Apply authorization checks independently of authentication.
- Protect login endpoints against brute-force attempts using rate limiting.
- Use secure session cookie settings.

Authentication confirms who the user is. Authorization determines what that user is allowed to do.

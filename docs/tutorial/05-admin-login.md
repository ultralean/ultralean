---
title: Tutorial 5 — Admin login
key: tutorial-05
layout: tutorial
---

# 5. Build the admin login

The admin area will use sessions and password hashing.

## 5.1 Keep authentication in the application

Authentication is application-specific, so the reusable framework keeps the actual auth class under:

```text
app/Auth/Auth.php
```

This lets each application decide how users, roles and login rules work without modifying ultralean Core.

## 5.2 Create the login page

Add routes:

```php
Router::get('/login', 'AuthController@login')
    ->name('login');

Router::post('/login', 'AuthController@authenticate', ['Csrf', 'RateLimit'])
    ->name('login.submit');

Router::post('/logout', 'AuthController@logout', ['Csrf'])
    ->name('logout');
```

The GET route displays the form. The POST route checks the submitted credentials.

## 5.3 Verify the password

Passwords must never be stored as plain text.

A login check follows this general pattern:

```php
if (password_verify($password, $user['password_hash'])) {
    Auth::login($user);
}
```

When creating a password:

```php
$hash = password_hash($password, PASSWORD_DEFAULT);
```

## 5.4 Start the session only when needed

The session should not be opened on every public request just because the application has authentication code. The auth/session components are lazy.

## 5.5 Create the admin group

```php
Router::group('/admin', ['AdminAuth']);

Router::get('/', 'Admin\\HomeController@index')
    ->name('admin.home');

Router::get('/posts', 'Admin\\PostController@index')
    ->name('admin.posts');

Router::groupEnd();
```

Now the admin area has a separate URL space and middleware requirement.

## 5.6 Build the dashboard

Create:

```text
app/Controllers/Admin/HomeController.php
app/Views/admin/home.php
```

The controller can simply render the dashboard view.

## Next

Continue to [Tutorial 6 — Protect the admin area]({{ '/tutorial/06-admin-security/' | relative_url }}).

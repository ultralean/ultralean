---
layout: docs
key: routing
title: Routing
---

# Routing

Routes are defined in `app/Routes/web.php` and `app/Routes/api.php`.

## Basic routes

```php
Router::get('/', 'HomeController@index');
Router::post('/users', 'UserController@store');
Router::put('/users/{id}', 'UserController@update');
Router::patch('/users/{id}', 'UserController@patch');
Router::delete('/users/{id}', 'UserController@destroy');
Router::options('/users', 'UserController@options');
```

Supported registration methods:

- `get`
- `post`
- `put`
- `patch`
- `delete`
- `options`
- `any`
- `match`

## Controller syntax

```php
Router::get('/about', 'HomeController@about');
Router::get('/admin', 'Admin\\HomeController@index');
```

The controller is instantiated only after the route matches.

## Route parameters

```php
Router::get('/users/{id}', 'UserController@show');
```

The controller receives the route parameter according to the framework's controller invocation.

## Named routes

```php
Router::get('/users/{id}', 'UserController@show')
    ->name('users.show');
```

Generate a URL:

```php
url('users.show', ['id' => 123]);
```

## Middleware

```php
Router::post('/login', 'AuthController@authenticate', ['Csrf', 'RateLimit']);
```

Middleware runs only for the matched route.

## Groups

```php
Router::group('/admin', ['AdminAuth']);

Router::get('/', 'Admin\\HomeController@index');
Router::get('/users', 'Admin\\UserController@index');

Router::group('/users', ['AdminAuth']);
Router::get('/', 'Admin\\UserController@index');
Router::groupEnd();

Router::groupEnd();
```

Groups can add a prefix and middleware.

## Canonical URLs

Repeated path slashes are automatically cleaned and redirected.

```text
/admin///users////123
```

becomes:

```text
/admin/users/123
```

The browser receives a 301 redirect, so the address bar changes.

Query strings are preserved:

```text
/admin///users/?active_from=20220826&active_to=20250604
```

becomes:

```text
/admin/users?active_from=20220826&active_to=20250604
```

Canonicalization is not configurable off because a URL should have one stable path representation.

GET and HEAD requests are canonicalized automatically. State-changing requests are not redirected.

## Static files

The router does not process static files. Apache checks whether a requested path is an existing file or directory before sending it to PHP.

## 405 responses

If a path exists but the HTTP method is not allowed, ultralean returns `405 Method Not Allowed` and includes the allowed methods instead of incorrectly returning 404.

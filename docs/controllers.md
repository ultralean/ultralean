---
layout: docs
key: controllers
title: Controllers
---

# Controllers

Controllers handle incoming requests and coordinate application logic.

In ultralean, controllers are regular PHP classes. There is no mandatory dependency injection container or complicated controller architecture.

## 1. Location

Application controllers belong in:

```text
app/Controllers/
```

For example:

```text
app/
└── Controllers/
    ├── HomeController.php
    ├── UserController.php
    └── AuthController.php
```

## 2. Creating a controller

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use System\Core\Response;

final class UserController
{
    public function index(): Response
    {
        return Response::make('Users');
    }
}
```

## 3. Registering routes

Connect controller methods to routes:

```php
Router::get(
    '/users',
    'UserController@index'
);

Router::get(
    '/users/{id}',
    'UserController@show'
);
```

Dynamic route parameters are passed to the controller method in route order.

```php
public function show(string $id): Response
{
    return Response::make('User ID: ' . e($id));
}
```

## 4. Accessing the request

Controllers can access the current Request object:

```php
$request = $this->request();
```

For example:

```php
public function store(): Response
{
    $request = $this->request();

    $name = $request->input('name');

    return Response::make('Received: ' . e($name));
}
```

See [Request]({{ '/request/' | relative_url }}) for available methods.

## 5. Returning views

The base controller provides a view shortcut:

```php
return $this->view('users/index', [
    'users' => $users,
]);
```

The view receives the supplied data.

## 6. Returning JSON

```php
return $this->json([
    'success' => true,
    'message' => 'User created.',
]);
```

## 7. Redirecting

```php
return $this->redirect('/users');
```

You can also redirect back to the previous page:

```php
return back();
```

To preserve submitted input:

```php
return back()->withData();
```

See [Forms]({{ '/forms/' | relative_url }}) for the old-input workflow.

## 8. Validation

Use the validation helper:

```php
$validator = validate(
    $this->request()->input(),
    [
        'name' => 'required|min:3',
        'email' => 'required|email',
    ]
);

if ($validator->fails()) {
    return back()->withData();
}
```

See [Validation]({{ '/validator/' | relative_url }}) for supported rules.

## 9. Middleware

Middleware can be assigned to routes:

```php
Router::get(
    '/dashboard',
    'DashboardController@index',
    ['Auth']
);
```

Middleware can also be applied to route groups:

```php
Router::group('/admin', ['Auth']);

Router::get(
    '/dashboard',
    'DashboardController@index'
);

Router::groupEnd();
```

Middleware handles concerns such as authentication, CSRF protection and request filtering before the controller executes.

## Design approach

Controllers should remain small and readable.

A good controller generally:

1. Receives the request.
2. Validates incoming data.
3. Checks authorization.
4. Calls the appropriate application or model logic.
5. Returns a response.

Keep reusable business logic outside controllers when it becomes complex.

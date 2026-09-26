---
layout: docs
key: http
title: Requests and responses
---

# Requests and responses

## Request object

Controllers can access the request through the base controller:

```php
$request = $this->request();
```

Available methods include:

```php
$request->method();
$request->path();
$request->uri();
$request->queryString();
$request->query('page');
$request->input('name');
$request->json();
$request->all();
$request->header('Accept');
$request->ip();
$request->wantsJson();
$request->isAjax();
```

## Query parameters

For:

```text
/users?active_from=20220826&active_to=20250604
```

use:

```php
$from = $request->query('active_from');
$to = $request->query('active_to');
```

Query parameters are intentionally preserved during canonical URL redirects.

## Form input

```php
$name = $request->input('name');
```

`all()` can retrieve the combined request input.

## JSON input

For an API request containing:

```json
{"name":"Imran"}
```

use:

```php
$data = $request->json();
$name = $request->input('name');
```

## Responses

HTML/text:

```php
return Response::make('Hello');
```

Status:

```php
return Response::make('Created', 201);
```

Redirect:

```php
return Response::redirect('/login');
```

No content:

```php
return Response::noContent();
```

JSON:

```php
return Response::json([
    'ok' => true,
]);
```

Created JSON:

```php
return Response::created([
    'id' => $id,
]);
```

## Controller shortcuts

The base controller provides:

```php
$this->view('users/index', $data);
$this->redirect('/login');
$this->json($data);
```

## API content negotiation

If a request has `Accept: application/json`, the error handler can return JSON. Requests to `/api/...` are also treated as API requests for error rendering.

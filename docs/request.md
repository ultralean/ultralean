
---
layout: docs
key: request
title: Request
---

# Request

The Request class provides access to the current HTTP request.

Controllers can retrieve the current request through:

```php
$request = $this->request();
```

## 1. HTTP information

```php
$request->method();

$request->path();

$request->uri();

$request->queryString();
```

For example:

```php
if ($request->method() === 'POST') {
    // Handle POST request.
}
```

## 2. Query parameters

For a URL:

```text
/users?page=2&active=1
```

Retrieve values using:

```php
$page = $request->query('page', 1);

$active = $request->query('active', 0);
```

Retrieve all query parameters:

```php
$query = $request->query();
```

Query parameters are also preserved during canonical route redirects.

## 3. Form input

Retrieve a submitted field:

```php
$name = $request->input('name');
```

Provide a default:

```php
$name = $request->input('name', 'Guest');
```

Retrieve all submitted input:

```php
$data = $request->input();
```

For ordinary form submissions, input comes from `$_POST`.

## 4. JSON input

For requests with an `application/json` content type:

```php
$data = $request->json();
```

Example:

```json
{
    "name": "Imran",
    "email": "user@example.com"
}
```

Retrieve individual values:

```php
$name = $request->input('name');
```

JSON decoding is cached during the current request.

Invalid or non-array JSON input results in an empty array.

## 5. Combined input

Use:

```php
$data = $request->all();
```

This combines query parameters and request input.

Request input takes precedence when the same key exists in both sources.

## 6. Headers

Retrieve a request header:

```php
$accept = $request->header('Accept');

$authorization = $request->header('Authorization');
```

Provide a default:

```php
$accept = $request->header(
    'Accept',
    'text/html'
);
```

## 7. Client IP

```php
$ip = $request->ip();
```

The method returns the remote address reported by the web server.

Do not blindly trust forwarded IP headers unless your application is configured with a trusted proxy policy.

## 8. Request type

Check whether the request expects JSON:

```php
if ($request->wantsJson()) {
    // Return JSON.
}
```

Check for an AJAX request:

```php
if ($request->isAjax()) {
    // Handle AJAX request.
}
```

## 9. Old form input

The Request class provides access to named form state through:

```php
$form = $request->formState('profile');
```

Usually, the simpler helper is preferred:

```php
$form = form_old('profile');
```

Retrieve values:

```php
$name = $form->old('name', '');
```

See [Forms]({{ '/forms/' | relative_url }}) for complete usage.

## Available methods

| Method | Purpose |
|---|---|
| `method()` | HTTP method |
| `path()` | Request path |
| `uri()` | Complete request URI |
| `queryString()` | Query string |
| `query()` | Query parameters |
| `input()` | Submitted input |
| `json()` | Decoded JSON |
| `all()` | Combined input |
| `header()` | Request header |
| `ip()` | Remote IP address |
| `wantsJson()` | JSON preference |
| `isAjax()` | AJAX detection |
| `formState()` | Named form state |

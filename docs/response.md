---
layout: docs
key: response
title: Response
---

# Response

The Response class represents an HTTP response.

Controllers can return Response instances directly.

## 1. Basic response

```php
return Response::make('Hello');
```

Set an HTTP status:

```php
return Response::make(
    'Created',
    201
);
```

You can also provide response headers:

```php
return Response::make(
    'Hello',
    200,
    [
        'Content-Type' => 'text/plain; charset=UTF-8',
    ]
);
```

## 2. Redirect

```php
return Response::redirect('/login');
```

The default redirect status is 302.

You can specify another supported status:

```php
return Response::redirect('/dashboard', 303);
```

For convenience, use:

```php
return back();
```

This redirects to the referring page when it passes the local-host check, otherwise it redirects to `/`.

## 3. Preserve form input

When validation fails, preserve submitted input:

```php
return back()->withData();
```

You can also provide selected data:

```php
return back()->withData([
    'name' => request()->input('name'),
]);
```

The method:
- Requires a redirect response.
- Accepts only local redirect destinations.
- Uses the submitted form identifier.
- Filters sensitive input.
- Stores the form state with automatic expiration.
- Adds the old-input identifier to the redirect URL.

See [Forms]({{ '/forms/' | relative_url }}) for details.

## 4. JSON response

Return JSON:

```php
return Response::json([
    'success' => true,
    'message' => 'Saved successfully.',
]);
```

Specify a status:

```php
return Response::json(
    ['error' => 'Not found'],
    404
);
```

Additional headers can be provided:

```php
return Response::json(
    ['success' => true],
    200,
    [
        'Cache-Control' => 'no-store',
    ]
);
```

The JSON response automatically sets:

```http
Content-Type: application/json; charset=UTF-8
```

## 5. Created response

For successfully created resources:

```php
return Response::created([
    'id' => 123,
    'name' => 'New user',
]);
```

This returns HTTP 201.

## 6. No-content response

For operations that do not need a response body:

```php
return Response::noContent();
```

This returns HTTP 204.

You can also supply headers:

```php
return Response::noContent([
    'Cache-Control' => 'no-store',
]);
```

## 7. Sending the response

The application normally handles sending the returned Response automatically.

The `send()` method:
- Sets the HTTP status.
- Sends response headers.
- Outputs the response body.
- Adds the development environment badge to eligible HTML responses.

You generally should not call `send()` manually inside controllers.

## Available methods

| Method | Purpose |
|---|---|
| `make()` | Create a normal response |
| `redirect()` | Create a redirect |
| `json()` | Create a JSON response |
| `created()` | Create a 201 response |
| `noContent()` | Create a 204 response |
| `withData()` | Preserve form input |
| `send()` | Send the response |

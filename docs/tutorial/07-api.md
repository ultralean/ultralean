---
title: Tutorial 7 — JSON API
key: tutorial-07
layout: tutorial
---

# 7. Add a JSON API

A single ultralean application can serve normal HTML pages and JSON endpoints.

## 7.1 Create an API route

Open:

```text
app/Routes/api.php
```

Add:

```php
Router::get('/api/health', 'ApiController@health')
    ->name('api.health');
```

## 7.2 Return JSON

In the controller:

```php
public function health()
{
    return Response::json([
        'ok' => true,
        'service' => 'ultralean-demo',
    ]);
}
```

The response is JSON with the correct content type.

## 7.3 Read JSON input

For a POST request:

```php
$data = $request->json();
$name = $request->input('name');
```

Use validation before storing the values.

## 7.4 API errors

The framework can return JSON-friendly errors when the request accepts JSON or the URL is under `/api/`.

For example, a missing resource should return an appropriate HTTP status such as `404`, not a successful `200` response containing an error string.

## 7.5 Do not add unnecessary API machinery

ultralean does not force CORS, API tokens, versioning, JWT or a particular authentication scheme. Add those when the application actually requires them.

This keeps the core small while allowing the application to grow around it.

## Next

Continue to [Tutorial 8 — Use runtime features correctly]({{ '/tutorial/08-runtime/' | relative_url }}).

---
layout: docs
key: http
title: HTTP requests and responses
---

# HTTP requests and responses

ultralean provides lightweight Request and Response classes for handling HTTP communication.

## Request

Controllers can access the current request through:

```php
$request = $this->request();
```

The Request class provides access to:

- HTTP method and path
- Query parameters
- Form input
- JSON input
- Request headers
- Client IP
- Old form input

See [Request]({{ '/request/' | relative_url }}) for the complete reference.

## Response

Controllers can return responses directly:

```php
return Response::make('Hello');
```

Common response types include:

```php
return Response::make('Created', 201);

return Response::redirect('/login');

return Response::noContent();

return Response::json([
    'ok' => true,
]);
```

See [Response]({{ '/response/' | relative_url }}) for more examples.

## Form handling

ultralean includes CSRF protection, named forms, old-input restoration and automatic expiration of preserved form data.

A typical form looks like:

```php
<form method="POST" action="/profile">
    <?= csrf_field('profile') ?>

    <input
        type="text"
        name="name"
        value="<?= e(form_old('profile')->old('name', '')) ?>"
    >

    <button type="submit">Save</button>
</form>
```

After validation fails:

```php
return back()->withData();
```

The submitted input is preserved and can be restored when the user returns to the form.

See [Forms]({{ '/forms/' | relative_url }}) for the complete guide.

## JSON input

For an API request containing:

```json
{
    "name": "Imran"
}
```

Use:

```php
$data = $request->json();

$name = $request->input('name');
```

JSON input is also available through `input()` when the request uses an `application/json` content type.

## Controller shortcuts

The base controller provides convenient methods:

```php
$this->view('users/index', $data);

$this->redirect('/login');

$this->json($data);
```

## API content negotiation

If a request has:

```http
Accept: application/json
```

the error handler can return JSON.

Requests to `/api/...` are also treated as API requests for error rendering.

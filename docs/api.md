---
layout: docs
key: api
title: APIs
---

# Building APIs

ultralean can serve HTML pages and JSON APIs from the same installation.

## API routes

Put API routes in:

```text
app/Routes/api.php
```

Example:

```php
Router::get('/api/health', 'ApiController@health')
    ->name('api.health');
```

## JSON response

```php
return Response::json([
    'ok' => true,
    'message' => 'Hello',
]);
```

Status code:

```php
return Response::json([
    'error' => 'Not found',
], 404);
```

Created response:

```php
return Response::created([
    'id' => $id,
]);
```

## JSON request

Send:

```http
Content-Type: application/json
Accept: application/json
```

Then:

```php
$data = $this->request()->json();
```

or:

```php
$name = $this->request()->input('name');
```

## API errors

Requests to `/api/...` are rendered as JSON by the error handler.

Development/debug responses can contain diagnostic details. Production responses contain safe information such as status, type and friendly message without exposing source code or stack traces.

## Authentication

API authentication is intentionally not forced by Core. Choose what your application requires:

- session authentication for browser-oriented APIs
- API keys
- bearer tokens
- signed requests
- OAuth/OIDC through an application-specific integration

Keep API authentication in `app/` so it can be changed without modifying Core.

## CORS

CORS is not enabled globally by default. Add the policy appropriate to your API rather than exposing every endpoint automatically.

## Example API controller

```php
namespace App\Controllers;

use System\Core\Controller;
use System\Core\Response;

final class ApiController extends Controller
{
    public function users(): Response
    {
        $users = User::all();

        return Response::json([
            'data' => $users,
        ]);
    }
}
```

For larger APIs, keep API controllers/models/services inside `app/` and add only the reusable HTTP primitives that the project actually needs.

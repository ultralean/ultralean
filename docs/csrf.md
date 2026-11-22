---
layout: docs
key: csrf
title: CSRF protection
---

# CSRF protection

Cross-Site Request Forgery (CSRF) is an attack in which an attacker attempts to make a user's browser submit an unwanted request to an application where the user is authenticated.

ultralean provides a lightweight `Csrf` class and `CsrfMiddleware` to protect browser-based state-changing requests.

## 1. Adding CSRF protection to forms

Use the global helper:

```php
<form method="POST" action="/profile">
    <?= csrf_field() ?>

    <input type="text" name="name">

    <button type="submit">Save</button>
</form>
```

The helper generates three hidden fields:

- `_token` — the session-based CSRF token.
- `_form_key` — an independent identifier for the submitted form.
- `_form_name` — the logical form name.

The form identifier is also used by the form-state system to associate preserved input with the correct form.

See [Forms]({{ '/forms/' | relative_url }}) for details.

## 2. Protecting routes

Register the CSRF middleware:

```php
Router::post(
    '/profile',
    'ProfileController@update',
    ['Csrf']
);
```

You can also apply it to a group:

```php
Router::group('', ['Csrf'], function () {
    Router::post('/profile', 'ProfileController@update');
    Router::post('/settings', 'SettingsController@update');
    Router::delete('/account', 'AccountController@delete');
});
```

## 3. How verification works

The middleware checks requests using the following methods:

- POST
- PUT
- PATCH
- DELETE

It retrieves the token from either:

```text
_token
```

or:

```http
X-CSRF-TOKEN
```

The token is compared with the session token using PHP's `hash_equals()`.

Requests with invalid or missing tokens are rejected.

## 4. Using CSRF with JavaScript

For AJAX requests, retrieve the token using:

```php
<?= e(csrf_token()) ?>
```

For example, expose it in your page:

```html
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
```

Then send it with your request:

```javascript
const token = document
    .querySelector('meta[name="csrf-token"]')
    .content;

fetch('/profile', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token
    },
    body: JSON.stringify({
        name: 'Imran'
    })
});
```

## 5. Generating a token manually

```php
$token = csrf_token();
```

You can also access the CSRF service:

```php
$token = csrf()->token();
```

## 6. CSRF and APIs

Browser-session authenticated endpoints should use CSRF protection when requests can be triggered through the user's browser.

Stateless APIs authenticated using bearer tokens generally require a different security design.

Do not blindly apply browser CSRF middleware to every API endpoint.

## Security notes

- Never place CSRF tokens in URLs.
- Do not expose tokens to third-party websites.
- Use HTTPS.
- Protect all browser-based state-changing routes.
- Do not treat the form identifier as a CSRF token.
- CSRF protection does not replace authentication or authorization.

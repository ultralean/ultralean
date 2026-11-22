---
layout: docs
key: forms
title: Forms and form state
---

# Forms and form state

ultralean provides lightweight form handling with built-in CSRF protection, named forms, old-input restoration and automatic expiration.

The goal is to make common form workflows simple without requiring developers to manually manage temporary form data in every controller.

## 1. Creating a form

Use `csrf_field()` inside every browser form that submits a state-changing request.

```php
<form method="POST" action="/profile">
    <?= csrf_field('profile') ?>

    <label>Name</label>
    <input type="text" name="name">

    <button type="submit">Save</button>
</form>
```

The optional argument identifies the logical form.

```php
<?= csrf_field('profile') ?>
```

If no name is provided, the form uses `default`.

Each call generates:
- The session-based CSRF token.
- A unique random form identifier.
- The logical form name.

The form identifier allows ultralean to associate preserved input with the correct form.

## 2. Handling submitted data

Access submitted input through the Request object:

```php
public function update(): Response
{
    $request = $this->request();

    $name = $request->input('name');

    // Process the submitted data.

    return back();
}
```

You can also retrieve all submitted input:

```php
$data = $request->input();
```

## 3. Preserving old input

When validation fails, use:

```php
return back()->withData();
```

This automatically:
1. Identifies the submitted form.
2. Filters sensitive and unsupported values.
3. Stores the remaining input in the session.
4. Sets an expiration time.
5. Adds the form identifier to the redirect URL.

You do not need to manually copy every input field into flash data.

You can also provide custom data:

```php
return back()->withData([
    'name' => $request->input('name'),
    'email' => $request->input('email'),
]);
```

This is useful when you only want to preserve selected values.

## 4. Restoring old input

Use `form_old()` with the same form name:

```php
<input
    type="text"
    name="name"
    value="<?= e(form_old('profile')->old('name', '')) ?>"
>
```

For multiple fields:

```php
<input
    type="text"
    name="name"
    value="<?= e(form_old('profile')->old('name', '')) ?>"
>

<input
    type="email"
    name="email"
    value="<?= e(form_old('profile')->old('email', '')) ?>"
>
```

The second argument provides a default value when no old input exists.

You can also retrieve all preserved input:

```php
$data = form_old('profile')->all();
```

For backward-compatible single-form usage, the global `old()` helper is available:

```php
<input
    type="text"
    name="name"
    value="<?= e(old('name', '')) ?>"
>
```

The `old()` helper refers to the default form.

## 5. Multiple forms on the same page

Each logical form should have its own name.

For example, a page containing profile and password forms:

```php
<form method="POST" action="/profile">
    <?= csrf_field('profile') ?>

    <h3>Profile information</h3>

    <input
        type="text"
        name="name"
        value="<?= e(form_old('profile')->old('name', '')) ?>"
    >

    <button type="submit">Update profile</button>
</form>

<form method="POST" action="/password">
    <?= csrf_field('password') ?>

    <h3>Change password</h3>

    <input
        type="password"
        name="current_password"
    >

    <input
        type="password"
        name="new_password"
    >

    <button type="submit">Change password</button>
</form>
```

Each form has:
- Its own logical name.
- Its own randomly generated identifier.
- Its own preserved input record.

When the profile form fails validation, only its preserved input is restored.

The password form does not accidentally receive the profile form's data.

**Important:** Use unique logical form names for different forms on the same page. Reusing a name makes them share the same logical form state.

## 6. Multiple browser tabs

Form identifiers are generated independently whenever `csrf_field()` is rendered.

For example:

- Tab A renders a profile form with identifier A.
- Tab B renders another profile form with identifier B.
- Tab A submits and preserves its input.
- Tab B submits and preserves its input.

Each redirect carries its own identifier, so the old-input records remain associated with their originating forms.

This avoids the common problem of using one global flash-data key for every browser tab.

The CSRF token itself is session-based and shared across tabs. Form identifiers provide old-input isolation; they are not substitutes for CSRF protection.

## 7. FormState class

The `FormState` class handles the storage and retrieval of preserved form data.

Normally, developers do not need to instantiate it directly.

The helper:

```php
$form = form_old('profile');
```

returns the corresponding FormState instance.

Available methods:

```php
$form->old('name');

$form->old('name', 'Default name');

$form->all();
```

Form state is loaded lazily and cached independently for each form name during the current request.

## 8. Automatic expiration and storage limits

Preserved form data has bounded storage.

Current defaults:

| Setting | Value |
|---|---:|
| Expiration | 600 seconds |
| Maximum records | 10 |
| Maximum data size | 64 KB |
| Consumption | Once |
| Storage | Session |

Expired and malformed records are removed when form state is stored or retrieved.

Records are consumed when successfully retrieved, preventing the same old input from being repeatedly restored.

The storage limit prevents unlimited growth of session data.

## 9. Sensitive input filtering

Old-input preservation is intentionally conservative.

The filtering mechanism excludes field names associated with:

- Passwords
- Secrets
- Tokens
- API keys
- Authorization data
- CSRF and form-identification fields

It also excludes unsupported complex values, such as objects and resources.

Nested arrays are filtered recursively.

Files are not preserved by old-input restoration.

**Never rely on this filtering as your only security control.** Validate incoming data and never trust restored input.

## 10. Complete controller example

A typical validation workflow:

```php
public function update(): Response
{
    $request = $this->request();

    $validator = validate(
        $request->input(),
        [
            'name' => 'required|min:3|max:100',
            'email' => 'required|email',
        ]
    );

    if ($validator->fails()) {
        return back()->withData();
    }

    // Save validated input.

    session()->flash('success', 'Profile updated.');

    return $this->redirect('/profile');
}
```

The view:

```php
<form method="POST" action="/profile">
    <?= csrf_field('profile') ?>

    <input
        type="text"
        name="name"
        value="<?= e(form_old('profile')->old('name', '')) ?>"
    >

    <input
        type="email"
        name="email"
        value="<?= e(form_old('profile')->old('email', '')) ?>"
    >

    <button type="submit">Save</button>
</form>
```

## 11. Important security considerations

- Always enable CSRF middleware for browser state-changing routes.
- Never treat hidden form identifiers as authorization credentials.
- Always validate submitted data on the server.
- Escape restored input before displaying it in HTML.
- Do not preserve passwords, authentication tokens or other secrets.
- Do not assume that a valid CSRF token means the user is authorized to perform an operation.
- Use HTTPS in production.
- Apply authorization checks independently of form validation.

## Summary

The intended workflow is simple:

```php
// View
<?= csrf_field('profile') ?>

<?= e(form_old('profile')->old('name', '')) ?>
```

```php
// Controller
if ($validator->fails()) {
    return back()->withData();
}
```

ultralean handles form identification, filtering, bounded storage, expiration and old-input retrieval.

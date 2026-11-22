
---
layout: docs
key: validator
title: Validation
---

# Validation

ultralean provides a lightweight Validator class for validating application input.

Validation rules are defined using simple strings or arrays.

## 1. Basic validation

Use the global `validate()` helper:

```php
$validator = validate(
    $request->input(),
    [
        'name' => 'required|min:3|max:100',
        'email' => 'required|email',
    ]
);
```

Check the result:

```php
if ($validator->fails()) {
    return back()->withData();
}
```

Or:

```php
if ($validator->passes()) {
    // Continue processing.
}
```

## 2. Available rules

| Rule | Description |
|---|---|
| `required` | Value must not be null or empty |
| `email` | Must be a valid email address |
| `url` | Must be a valid URL |
| `numeric` | Must be numeric |
| `integer` | Must be a valid integer |
| `min:value` | Minimum string length or numeric value |
| `max:value` | Maximum string length or numeric value |
| `same:field` | Must match another field |
| `in:a,b,c` | Must match one of the specified values |
| `unique:table,column` | Must not already exist in the database |
| `exists:table,column` | Must already exist in the database |

Rules are separated using `|`.

## 3. Required and email

```php
$validator = validate(
    $request->input(),
    [
        'email' => 'required|email',
    ]
);
```

## 4. Minimum and maximum

For strings, `min` and `max` check character length.

For numeric values, they check the numeric value.

```php
[
    'name' => 'required|min:3|max:50',
    'age' => 'numeric|min:18|max:100',
]
```

## 5. Comparing fields

Use `same` to compare two submitted fields:

```php
[
    'password' => 'required',
    'password_confirmation' => 'required|same:password',
]
```

## 6. Allowed values

Use `in` when a field must match one of a predefined set:

```php
[
    'status' => 'required|in:active,inactive,pending',
]
```

## 7. Database validation

Check whether a value already exists:

```php
[
    'email' => 'required|email|unique:users,email',
]
```

Check whether a database record exists:

```php
[
    'user_id' => 'required|integer|exists:users,id',
]
```

Database table and column identifiers must be valid identifiers.

These rules use the application's default database connection.

## 8. Error messages

Retrieve all errors:

```php
$errors = $validator->errors();
```

The result is grouped by field:

```php
[
    'name' => [
        'The name field is required.',
    ],
]
```

Retrieve the first error:

```php
$message = $validator->first();
```

Retrieve the first error for a particular field:

```php
$message = $validator->first('email');
```

If no error exists, `first()` returns `null`.

## 9. Complete example

```php
public function store(): Response
{
    $request = $this->request();

    $validator = validate(
        $request->input(),
        [
            'name' => 'required|min:3|max:100',
            'email' => 'required|email|unique:users,email',
            'age' => 'required|integer|min:18',
        ]
    );

    if ($validator->fails()) {
        return back()->withData();
    }

    // Create the user.

    return $this->redirect('/users');
}
```

## Important notes

- Unknown rules are currently ignored.
- Rules are evaluated in the order provided.
- Empty values skip rules other than `required`.
- Validation does not automatically sanitize or escape values.
- Validation does not replace authorization.
- Database uniqueness checks are not a substitute for database unique constraints.

For production applications, enforce important uniqueness requirements at the database level as well.

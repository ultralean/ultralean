---
layout: docs
key: models
title: Models
---

# Models

Models provide a lightweight interface for working with application data.

ultralean keeps the database layer close to PDO. It does not require a heavyweight ORM or hide SQL behind complicated abstractions.

## 1. Location

Application models belong in:

```text
app/Models/
```

For example:

```text
app/
└── Models/
    ├── User.php
    └── Product.php
```

## 2. Using models

Models can be used to retrieve and manipulate application data.

For example, the authentication system can retrieve a record using:

```php
$account = User::where('id', '=', $id)->first();
```

The model layer provides a thin interface over database operations.

## 3. Database access

You can also use PDO directly:

```php
$stmt = db()->prepare(
    'SELECT * FROM users WHERE email = :email'
);

$stmt->execute([
    'email' => $email,
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);
```

This is intentional. You are not required to use models for every database operation.

## 4. Database helper functions

ultralean provides convenient database helpers:

```php
// Retrieve one record.
$user = db_fetch(
    'SELECT * FROM users WHERE id = :id',
    ['id' => 1]
);

// Retrieve multiple records.
$users = db_fetch_all(
    'SELECT * FROM users'
);

// Retrieve a single value.
$count = db_value(
    'SELECT COUNT(*) FROM users'
);

// Execute an INSERT, UPDATE or DELETE.
$affected = db_execute(
    'DELETE FROM users WHERE id = :id',
    ['id' => 1]
);
```

## 5. Named connections

When multiple database connections are configured, specify the connection name:

```php
$users = db_fetch_all(
    'SELECT * FROM users',
    [],
    'mysql'
);
```

The default connection is used when no connection name is supplied.

## 6. SQL security

Always use prepared statements for values:

```php
$stmt = db()->prepare(
    'SELECT * FROM users WHERE email = :email'
);

$stmt->execute([
    'email' => $email,
]);
```

Never concatenate untrusted values into SQL.

SQL identifiers, such as table and column names, cannot be safely parameterized as ordinary SQL values. Use trusted, validated identifiers when constructing dynamic SQL.

## Design approach

The model layer is intentionally thin.

Use models when they make application code clearer, use PDO when direct database access is more appropriate, and keep SQL visible when that improves understanding and control.

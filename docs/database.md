---
layout: docs
key: database
title: Database and models
---

# Database and models

ultralean uses PDO directly and provides a small convenience layer above it. It is not an ORM.

## Supported databases

- MySQL
- PostgreSQL
- SQLite

## Lazy connections

A database connection is not opened merely because the application starts.

```php
$db = db();
```

opens the configured default connection when required.

Named connection:

```php
$db = db('analytics');
```

Only that named connection is initialized.

## Raw PDO

Raw PDO remains available:

```php
$pdo = db();

$stmt = $pdo->prepare(
    'SELECT * FROM users WHERE email = :email'
);
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();
```

This is intentional. You are not forced to use the model helpers.

## Database helpers

```php
db_fetch($sql, $params = [], $connection = null);
db_fetch_all($sql, $params = [], $connection = null);
db_value($sql, $params = [], $connection = null);
db_execute($sql, $params = [], $connection = null);
```

## Transactions

```php
Database::transaction(function () {
    User::create([...]);
    db_execute('UPDATE accounts SET balance = balance - 10 WHERE id = :id', [
        'id' => 1,
    ]);
});
```

## Model definition

```php
namespace App\Models;

use System\Core\Model;

final class User extends Model
{
    protected static string $table = 'users';
}
```

## Find records

```php
User::find(1);
User::findRecord(1);
User::all();
```

`find()` returns an array or `null`. `findRecord()` returns a record object with attribute access and date/time handling.

## Conditions

Both forms are supported:

```php
User::where('email', $email)->first();
User::where('status', '=', 'active')->get();
```

Additional query methods:

```php
whereIn()
whereNull()
whereNotNull()
exists()
first()
firstOrFail()
get()
count()
sum()
avg()
min()
max()
orderBy()
limit()
offset()
```

Example:

```php
$users = User::where('status', '=', 'active')
    ->orderBy('created_at', 'DESC')
    ->limit(20)
    ->get();
```

## Insert/update/delete

```php
$id = User::create([
    'username' => 'alice',
]);

User::update($id, [
    'username' => 'alice2',
]);

User::delete($id);
```

Query-builder `update()` and `delete()` require a WHERE condition to prevent accidental full-table changes.

## Timestamps

Models use UTC timestamps by default. `created_at` and `updated_at` are generated in UTC ISO-8601 format when timestamps are enabled.

A model can disable timestamps if appropriate:

```php
protected static bool $timestamps = false;
```

## Security

Always bind values. Do not concatenate user input into SQL.

Column names are validated by the model layer, but values should still be bound through PDO parameters.

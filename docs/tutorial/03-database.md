---
title: Tutorial 3 — Database & migrations
key: tutorial-03
layout: tutorial
---

# 3. Create the database and migrations

Our application needs users and posts.

## 3.1 Why migrations?

A migration is a versioned database change. Instead of manually creating tables on every machine, keep the SQL in the project and run:

```bash
php ul migrate
```

The same migration engine can also be used from the browser through `public/migrate.php` when a hosting account does not provide SSH.

## 3.2 Create the users migration

Run:

```bash
php ul make:migration create_users
```

Open the generated file in `app/Migrations/` and define the table. A simple version is:

```sql
-- UP

CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at VARCHAR(40) NOT NULL,
    updated_at VARCHAR(40) NOT NULL
);

-- DOWN

DROP TABLE users;
```

For MySQL/PostgreSQL you can use the driver's appropriate SQL or provide `.mysql.sql` and `.pgsql.sql` overrides when the syntax differs.

## 3.3 Create the posts migration

Run:

```bash
php ul make:migration create_posts
```

Use columns such as:

```sql
-- UP

CREATE TABLE posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    body TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'published',
    created_at VARCHAR(40) NOT NULL,
    updated_at VARCHAR(40) NOT NULL
);

-- DOWN

DROP TABLE posts;
```

## 3.4 Run the migrations

```bash
php ul migrate
```

Then:

```bash
php ul migrate:status
```

You should see the applied migrations.

## 3.5 Learn rollback and fresh

During development:

```bash
php ul migrate:rollback
```

To remove all migrations and rebuild the database:

```bash
php ul migrate:fresh --force
```

`fresh` is destructive. Never run destructive migration commands against a database you need to preserve unless you have deliberately backed it up.

## 3.6 Database connections are lazy

Calling:

```php
Database::connection();
```

opens the default database connection. If a request never touches the database, ultralean does not create that connection merely because the framework exists.

## Next

Continue to [Tutorial 4 — Models and CRUD]({{ '/tutorial/04-models/' | relative_url }}).

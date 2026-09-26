---
title: Tutorial 4 — Models & CRUD
key: tutorial-04
layout: tutorial
---

# 4. Add models and CRUD

ultralean's model layer is intentionally thin. It gives you useful query helpers without becoming an ORM.

## 4.1 Create the Post model

Create:

```text
app/Models/Post.php
```

```php
<?php

namespace App\Models;

use System\Core\Model;

final class Post extends Model
{
    protected static string $table = 'posts';
}
```

## 4.2 Read records

```php
$post = Post::find(1);
```

For a record object:

```php
$post = Post::findRecord(1);
```

Query conditions can be chained:

```php
$posts = Post::where('status', '=', 'published')
    ->orderBy('created_at', 'desc')
    ->limit(20)
    ->get();
```

## 4.3 Create a record

```php
$post = Post::create([
    'title' => $title,
    'slug' => $slug,
    'body' => $body,
    'status' => 'published',
]);
```

The framework handles the query; you still have access to raw PDO when a query is better expressed directly in SQL.

## 4.4 Update and delete

```php
Post::update($id, [
    'title' => $title,
    'body' => $body,
]);

Post::delete($id);
```

The model layer protects update/delete operations from accidentally running without a WHERE condition.

## 4.5 Build the public post list

Create `PostController` with an `index()` action:

```php
public function index()
{
    $posts = Post::where('status', '=', 'published')
        ->orderBy('created_at', 'desc')
        ->get();

    return $this->view('posts/index', [
        'title' => 'Posts',
        'posts' => $posts,
    ]);
}
```

Add:

```php
Router::get('/posts', 'PostController@index')
    ->name('posts.index');
```

## 4.6 Build the detail page

```php
public function show($id)
{
    $post = Post::findRecord($id);

    if (!$post) {
        return Response::make('Not found', 404);
    }

    return $this->view('posts/show', [
        'title' => $post->title,
        'post' => $post,
    ]);
}
```

Route:

```php
Router::get('/posts/{id}', 'PostController@show')
    ->name('posts.show');
```

## Next

Continue to [Tutorial 5 — Build the admin login]({{ '/tutorial/05-admin-login/' | relative_url }}).

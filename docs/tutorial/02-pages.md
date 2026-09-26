---
title: Tutorial 2 — Pages, routes & views
key: tutorial-02
layout: tutorial
---

# 2. Build pages, routes and views

We will create Home, About, Posts and Post-detail pages.

## 2.1 Create a controller

Create:

```text
app/Controllers/HomeController.php
```

A simple controller can look like:

```php
<?php

namespace App\Controllers;

use System\Core\Controller;

final class HomeController extends Controller
{
    public function index()
    {
        return $this->view('home', [
            'title' => 'Home',
        ]);
    }

    public function about()
    {
        return $this->view('about', [
            'title' => 'About',
        ]);
    }
}
```

The controller is not loaded until a matching route actually needs it.

## 2.2 Add routes

Open:

```text
app/Routes/web.php
```

Add:

```php
Router::get('/', 'HomeController@index')
    ->name('home');

Router::get('/about', 'HomeController@about')
    ->name('about');
```

Named routes allow application code to generate URLs without hard-coding paths.

## 2.3 Create the views

Create:

```text
app/Views/home.php
app/Views/about.php
```

A view can use a layout:

```php
<?php $this->extend('layouts/app'); ?>

<?php $this->section('content'); ?>
<h1>Welcome</h1>
<p>This is the home page.</p>
<?php $this->endSection(); ?>
```

The layout can contain the common HTML document, navigation and footer.

## 2.4 Add a shared layout

Create:

```text
app/Views/layouts/app.php
```

A minimal layout:

```php
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Blog') ?></title>
</head>
<body>
    <nav>
        <a href="<?= url('home') ?>">Home</a>
        <a href="<?= url('about') ?>">About</a>
    </nav>

    <?= $this->yield('content') ?>
</body>
</html>
```

## 2.5 Add a dynamic route

Later we need a post detail URL:

```php
Router::get('/posts/{id}', 'PostController@show')
    ->name('posts.show');
```

The router extracts `id` and passes it to the controller action.

## What you have learned

You now have the core ultralean request flow:

```text
Browser
  ↓
public/index.php
  ↓
Router
  ↓
Controller
  ↓
View
  ↓
Response
```

## Next

Continue to [Tutorial 3 — Database and migrations]({{ '/tutorial/03-database/' | relative_url }}).

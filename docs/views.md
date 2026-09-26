---
layout: docs
key: views
title: Views
---

# Views

Views use plain PHP. ultralean provides a small layout/section helper without compiling templates.

## Render a view

```php
return $this->view('users/index', [
    'users' => $users,
]);
```

## Layout

A view can extend a layout:

```php
<?php $this->extend('layouts/app'); ?>

<?php $this->section('content'); ?>

<h1>Users</h1>

<?php $this->endSection(); ?>
```

The layout can use:

```php
<?= $this->yield('content') ?>
```

## Head section

```php
<?php $this->section('head'); ?>
<link rel="stylesheet" href="/assets/users.css">
<?php $this->endSection(); ?>
```

## Partials

```php
<?= $this->partial('partials/nav', ['user' => $user]) ?>
```

## Escaping

Use the escaping helper for untrusted output:

```php
<?= e($user['name']) ?>
```

Do not print untrusted HTML directly unless your application has deliberately sanitized it.

## Plain PHP remains available

Because views are PHP files, you can use normal PHP control flow, includes and functions. There is no template compiler to learn.

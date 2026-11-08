<?php

declare(strict_types=1);

use System\Core\Router;

Router::get('/', 'HomeController@index')
    ->name('home');

Router::get('/about', 'HomeController@about')
    ->name('about');

    Router::get('/account', 'HomeController@account', ['UserAuth'])
    ->name('account');

Router::get('/login', 'AuthController@login')
    ->name('login');

Router::post('/login', 'AuthController@authenticate', ['Csrf', 'RateLimit'])
    ->name('login.submit');

Router::post('/logout', 'AuthController@logout', ['Csrf'])
    ->name('logout');

// Admin Routes for login and logout
Router::get('/admin/login', 'Admin\AuthController@login')
    ->name('admin.login');

Router::post('/admin/login', 'Admin\AuthController@authenticate', ['Csrf', 'RateLimit'])
    ->name('admin.login.submit');

Router::post('/admin/logout', 'Admin\AuthController@logout', ['Csrf'])
    ->name('admin.logout');

// Admin Routes (grouped & authenticated)

Router::group('/admin', ['AdminAuth']);

    Router::get('/', 'Admin\HomeController@index')
        ->name('admin.home');

    Router::get('/account', 'Admin\UserController@profile')
        ->name('admin.account');

    Router::post('/account', 'Admin\UserController@update', ['Csrf'])
        ->name('admin.account.update');

    Router::get('/users/{id}', 'Admin\UserController@show')
        ->name('admin.users.show');

Router::groupEnd();

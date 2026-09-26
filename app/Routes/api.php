<?php

declare(strict_types=1);

use System\Core\Router;

Router::get('/api/health', 'ApiController@health')
    ->name('api.health');

<?php

return [
    // Connection used by db() and by migrations when --database is omitted.
    // Possible values must match a connection name below, for example: sqlite, mysql, pgsql.
    'default' => 'sqlite',

    'connections' => [
        'mysql' => [
            // Supported drivers: mysql, pgsql, sqlite.
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'ultralean',
            'username' => 'root',
            'password' => '',
            // MySQL example: utf8mb4.
            'charset' => 'utf8mb4',
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => '127.0.0.1',
            'port' => 5432,
            'database' => 'ultralean',
            'username' => 'postgres',
            'password' => '',
            // PostgreSQL example: UTF8.
            'charset' => 'UTF8',
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            // SQLite uses a file instead of host/port/user/password.
            'database' => app_path('Storage/database.sqlite'),
        ],
    ],
];

<?php

declare(strict_types=1);

/**
 * This config file is used to define authentication guards and their corresponding models and session keys.
 * The 'defaults' section specifies the default guard to be used for authentication.
 * Each guard is defined in the 'guards' section, where you can specify the model class and the session key used to store the authenticated user's ID.
 *
 * Examples of how to use the auth() helper function with different guards:
 *
 *    auth()->check();                 // Default user guard
 *    auth('user')->check();           // Regular user
 *    auth('admin')->check();          // Admin
 *    auth('superadmin')->check();     // Superadmin
 *    auth('temporary')->check();      // Temporary user
 * 
 * Login and logout follow the same pattern:
 * auth('admin')->login($admin['id']);
 * auth('admin')->logout();
 */

return [
    'defaults' => [
        'guard' => 'admin',
    ],

    'guards' => [
        'user' => [
            'model' => \App\Models\User::class,
            'session_key' => '_auth.user_id',
        ],

        'admin' => [
            'model' => \App\Models\AdminAccount::class,
            'session_key' => '_auth.admin_id',
        ],

/*
        'superadmin' => [
            'model' => \App\Models\SuperAdmin::class,
            'session_key' => '_auth.superadmin_id',
        ],

        'temporary' => [
            'model' => \App\Models\TemporaryUser::class,
            'session_key' => '_auth.temporary_id',
        ], */
    ],
];
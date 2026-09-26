<?php

return [
    // Credentials for public/migrate.php only. This is NOT the application's login.
    // Change this password before using the migration page outside local development.
    'username' => 'admin',
    'password_hash' => '$2y$12$uTjGA7KdptjmOt1Wri6QeOjZ3Mw3R2Dc3o4sk42z6ilg0vUbZfoQq',

    // When true, production blocks the normal website while public/migrate.php exists.
    // Delete or move public/migrate.php after completing migrations in production.
    'production_block' => true,
];

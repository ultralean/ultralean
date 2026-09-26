<?php

return [
    // Application name shown by the error pages and other built-in UI.
    'name' => 'ultralean',

    // Runtime environment. Supported values: development, staging, production.
    'env' => 'development',

    // Show detailed exception information when true. Recommended: true locally,
    // false in production. Production still shows the correct HTTP status and a
    // safe, friendly message.
    'debug' => true,

    // Application-facing timezone. This does NOT change ultralean's timestamp
    // storage rule: model timestamps are always stored as UTC ISO-8601 values.
    // Examples: UTC, Asia/Karachi, Europe/London, America/New_York.
    'timezone' => 'Asia/Karachi',

    // Public upload location used by the application.
    'upload_path' => public_path('uploads'),
    'upload_url' => '/uploads',

    // Error logging is disabled by default.
    'errors' => [
        'logging' => [
            'enabled' => false,

            // Select the minimum severity levels you want to record.
            // Supported levels, from highest to lowest severity:
            // emergency, alert, critical, error, warning, notice, info, debug.
            //
            // A selected level also records every MORE severe level above it.
            // Examples:
            // []                  = record nothing.
            // ['error']           = emergency, alert, critical, error.
            // ['warning']         = emergency through warning.
            // ['info']            = emergency through info.
            // ['debug']           = every level.
            // ['error', 'info']   = emergency through info (the union of both).
            'levels' => [],

            // Number of days of log files to retain.
            // 0 = keep old log files indefinitely.
            // 10 = keep approximately the last 10 days and remove older files.
            'retention_days' => 10,

            // Log cleanup is deliberately infrequent. The logger checks for
            // old files only after this many log writes, not on every request.
            'cleanup_every' => 100,
        ],
    ],

    // File-backed rate limiting. It is enabled here, but only routes listed in
    // 'routes' receive a custom rule. Other routes use the general rule.
    'rate_limit' => [
        'enabled' => true,

        // General rule: maximum number of requests in the time window.
        // Example: 60 requests in 60 seconds per source IP/path.
        'limit' => 60,
        'window' => 60,

        'storage' => storage_path('cache/rate_limit'),

        // Expired rate-limit files are cleanup candidates after this age.
        // 86400 = 24 hours. 43200 = 12 hours.
        'cleanup_after' => 86400,

        // Cleanup is attempted only after this many rate-limit operations.
        // This avoids scanning the cache directory on every request.
        'cleanup_every' => 100,

        'routes' => [
            '/login' => [
                // Login has two layers:
                // account_limit/account_window = strict protection for one
                // account identifier; different users behind one corporate NAT
                // therefore have separate account buckets.
                // ip_limit/ip_window = broader protection for one source IP.
                'account_field' => 'username',
                'account_limit' => 5,
                'account_window' => 300, // 5 attempts in 5 minutes.
                'ip_limit' => 300,
                'ip_window' => 60,       // 300 requests in 60 seconds.
            ],
        ],
    ],
];

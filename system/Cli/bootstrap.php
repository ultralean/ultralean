<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

// CLI utilities do not need the full application runtime. Use the configured
// application timezone when it is available, but keep the CLI bootstrap usable
// even when another application config file has a problem.
try {
    date_default_timezone_set((string) config('app.timezone', 'UTC'));
} catch (\Throwable) {
    date_default_timezone_set('UTC');
}

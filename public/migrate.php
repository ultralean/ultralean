<?php

declare(strict_types=1);

// This file intentionally boots only the CLI side of ultralean. It does not
// load routes, controllers, the normal application, or the demo admin auth.
require dirname(__DIR__) . '/system/Cli/bootstrap.php';

$configWarnings = [];
$config = migrate_config('migrate', [], $configWarnings);
$production = strtolower((string) migrate_config('app.env', 'development', $configWarnings)) === 'production';
$defaultMigrationHash = '$2y$12$uTjGA7KdptjmOt1Wri6QeOjZ3Mw3R2Dc3o4sk42z6ilg0vUbZfoQq';
$productionPasswordNotChanged = $production
    && hash_equals($defaultMigrationHash, (string) ($config['password_hash'] ?? ''));
$sessionName = 'ul_migrate';
ini_set('session.use_strict_mode', '1');
session_name($sessionName);
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Strict',
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'path' => '/',
]);
session_start();

if (!isset($_SESSION['_csrf'])) {
    $_SESSION['_csrf'] = bin2hex(random_bytes(32));
}

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header('Cache-Control: no-store');

function migrate_csrf(): string
{
    return '<input type="hidden" name="_token" value="' . e($_SESSION['_csrf']) . '">';
}

function migrate_auth(): bool
{
    return !empty($_SESSION['_authenticated']);
}

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: migrate.php');
    exit;
}

$error = null;
$output = null;

if ($configWarnings !== []) {
    $error = implode(' ', $configWarnings);
}

if (!migrate_auth()) {
    if ($productionPasswordNotChanged) {
        $error = 'Production migration access is disabled until you replace the demo password hash in app/Config/migrate.php.';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!hash_equals((string) $_SESSION['_csrf'], (string) ($_POST['_token'] ?? ''))) {
            http_response_code(419);
            exit('Security token expired. Reload the page and try again.');
        }

        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $limited = migrate_login_rate_limited($username);
        $valid = !$limited
            && hash_equals((string) ($config['username'] ?? ''), $username)
            && password_verify($password, (string) ($config['password_hash'] ?? ''));

        if ($valid) {
            session_regenerate_id(true);
            $_SESSION['_authenticated'] = true;
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
            header('Location: migrate.php');
            exit;
        }

        $error = $limited
            ? 'Too many sign-in attempts. Please wait before trying again.'
            : 'The migration username or password is incorrect.';
    }

    echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ultralean migrations</title><style>' . migrate_css() . '</style></head><body><main class="box"><h1>Migration Manager</h1><p>This page runs the same migration commands as <code>php ul</code>. It is intended for shared hosting where SSH is unavailable.</p>';
    if ($error) echo '<div class="error">' . e($error) . '</div>';
    echo '<form method="post">' . migrate_csrf() . '<label>Username<input name="username" autocomplete="username" required></label><label>Password<input type="password" name="password" autocomplete="current-password" required></label><button>Sign in</button></form></main></body></html>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals((string) $_SESSION['_csrf'], (string) ($_POST['_token'] ?? ''))) {
        http_response_code(419);
        exit('Security token expired. Reload the page and try again.');
    }

    $command = trim((string) ($_POST['command'] ?? ''));
    $database = trim((string) ($_POST['database'] ?? ''));
    $args = $database !== '' ? ['--database=' . $database] : [];

    if (in_array($command, ['migrate:reset', 'migrate:fresh'], true)) {
        if (($_POST['confirm'] ?? '') !== 'yes') {
            $error = 'Confirm the destructive migration action before continuing.';
        } else {
            $args[] = '--force';
        }
    }

    if ($error === null) {
        if ($command === 'make:migration') {
            $name = trim((string) ($_POST['name'] ?? ''));
            if ($name === '') {
                $error = 'Migration name is required.';
            } else {
                $args[] = $name;
            }
        }

        if ($error === null) {
            ob_start();
            $exitCode = (new System\Cli\Cli())->run(array_merge(['ul', $command], $args));
            $output = trim((string) ob_get_clean());
            if ($exitCode !== 0 && $output === '') {
                $error = 'The command could not be completed. Check the configuration and try again.';
            }
        }
    }
}

$commands = (new System\Cli\Cli())->commands();
$connections = migrate_config('database.connections', [], $configWarnings);
if ($configWarnings !== []) {
    $error ??= implode(' ', $configWarnings);
}

if (!is_array($connections)) {
    $connections = [];
}

$productionWarning = strtolower((string) migrate_config('app.env', 'development')) === 'production'
    ? '<div class="warning"><strong>Production:</strong> after migrations finish, delete or move <code>public/migrate.php</code>. The normal site is intentionally blocked while this file exists.</div>'
    : '';

echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ultralean Migration Manager</title><style>' . migrate_css() . '</style></head><body><main class="box"><div class="top"><div><h1>Migration Manager</h1><p>CLI-compatible migration controls.</p></div><a href="?logout=1">Log out</a></div>';
echo $productionWarning;
if ($error) echo '<div class="error">' . e($error) . '</div>';
if ($output !== null) echo '<h2>Command output</h2><pre>' . e($output) . '</pre>';
echo '<form method="post">' . migrate_csrf() . '<label>Database<select name="database"><option value="">Default (' . e((string) migrate_config('database.default', '')) . ')</option>';
foreach ($connections as $name => $_connection) echo '<option value="' . e((string) $name) . '">' . e((string) $name) . '</option>';
echo '</select></label><div class="actions">';
foreach ($commands as $name => $description) {
    if ($name === 'make:migration') continue;
    $danger = in_array($name, ['migrate:reset', 'migrate:fresh'], true);
    echo '<button name="command" value="' . e($name) . '"' . ($danger ? ' class="danger"' : '') . '>' . e($name) . '</button>';
}
echo '</div><label class="check"><input type="checkbox" name="confirm" value="yes"> I understand that reset/fresh can delete database structures.</label></form>';
echo '<hr><form method="post">' . migrate_csrf() . '<input type="hidden" name="command" value="make:migration"><label>New migration name<input name="name" placeholder="create_posts" required></label><button>Create migration</button></form>';
echo '<p class="small">This page is deliberately separate from the normal application. It uses the same CLI command classes and arguments.</p></main></body></html>';

function migrate_config(string $key, mixed $default = null, ?array &$warnings = null): mixed
{
    try {
        return config($key, $default);
    } catch (\Throwable $e) {
        if ($warnings !== null) {
            $warnings[] = 'Configuration error while reading ' . $key . ': ' . $e->getMessage();
        }
        return $default;
    }
}

function migrate_login_rate_limited(string $username): bool
{
    static $operations = 0;
    $directory = storage_path('cache/migrate_rate_limit');
    $operations++;

    $cleanupEvery = max(1, (int) config('app.rate_limit.cleanup_every', 100));
    if ($operations % $cleanupEvery === 0) {
        migrate_cleanup_rate_limit_files($directory);
    }
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        // Do not make the migration utility unusable because rate-limit storage is unavailable.
        return false;
    }

    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    $account = strtolower(trim($username));

    // Account bucket: protects the migration credential even when the attacker rotates IPs.
    if (migrate_consume_bucket($directory, 'account|' . $account, 5, 300)) {
        return true;
    }

    // Broader IP bucket: protects the endpoint from one automated source.
    return migrate_consume_bucket($directory, 'ip|' . $ip, 60, 60);
}


function migrate_cleanup_rate_limit_files(string $directory): void
{
    $age = max(0, (int) config('app.rate_limit.cleanup_after', 86400));
    if ($age === 0 || !is_dir($directory)) {
        return;
    }

    $cutoff = time() - $age;
    foreach (glob($directory . '/*.tmp') ?: [] as $file) {
        if (!is_file($file)) {
            continue;
        }

        $contents = @file_get_contents($file);
        $parts = explode('|', trim($contents ?: ''));
        $expires = count($parts) === 2 ? (int) $parts[1] : 0;
        if ($expires > 0 && $expires < $cutoff) {
            @unlink($file);
        }
    }
}

function migrate_consume_bucket(string $directory, string $identity, int $limit, int $window): bool
{
    $file = $directory . '/' . hash('sha256', $identity) . '.tmp';
    $handle = @fopen($file, 'c+');
    if ($handle === false) {
        return false;
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            return false;
        }

        $contents = stream_get_contents($handle) ?: '';
        $parts = explode('|', trim($contents));
        $count = count($parts) === 2 ? (int) $parts[0] : 0;
        $expires = count($parts) === 2 ? (int) $parts[1] : 0;
        $now = time();

        if ($expires <= $now) {
            $count = 0;
            $expires = $now + $window;
        }

        $count++;
        $blocked = $count > $limit;

        if (!$blocked) {
            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, $count . '|' . $expires);
            fflush($handle);
        }

        flock($handle, LOCK_UN);
        return $blocked;
    } finally {
        fclose($handle);
    }
}

function migrate_css(): string
{
    return 'body{margin:0;background:#0b1120;color:#e2e8f0;font:16px/1.6 system-ui,sans-serif}.box{max-width:850px;margin:6vh auto;padding:28px}h1{margin-top:0}.top{display:flex;justify-content:space-between;gap:20px}.top a,.top .link{color:#7dd3fc}.inline{margin:0}.link{background:none;border:0;padding:0;margin:0;font:inherit;font-weight:600;cursor:pointer}.warning{padding:14px;background:#422006;color:#fed7aa;border-radius:10px;margin:18px 0}.error{padding:14px;background:#450a0a;color:#fecaca;border-radius:10px;margin:18px 0}label{display:block;margin:14px 0;font-weight:600}input,select{display:block;width:100%;box-sizing:border-box;margin-top:6px;padding:11px;border-radius:8px;border:1px solid #334155;background:#111827;color:#e2e8f0}button{margin:6px 6px 6px 0;padding:10px 14px;border:0;border-radius:8px;background:#38bdf8;color:#082f49;font-weight:700;cursor:pointer}.danger{background:#ef4444;color:#fff}.check{font-weight:400}.check input{display:inline;width:auto;margin-right:6px}pre{padding:16px;background:#020617;border-radius:10px;overflow:auto}code{color:#7dd3fc}.small{color:#94a3b8;font-size:14px}hr{border:0;border-top:1px solid #1e293b;margin:30px 0}.actions{display:flex;flex-wrap:wrap;gap:4px}';
}

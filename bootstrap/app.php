<?php

declare(strict_types=1);

$applicationRoot = dirname(__DIR__);

require_once $applicationRoot . '/app/Helpers/helpers.php';
require_once $applicationRoot . '/app/Core/Config.php';
require_once $applicationRoot . '/app/Core/Autoloader.php';

\App\Core\Autoloader::register();
require_once base_path('app/Core/ExceptionHandler.php');

$envFile = base_path('.env');
if (is_file($envFile)) {
    $envValues = parse_ini_file($envFile, false, INI_SCANNER_TYPED) ?: [];
    foreach ($envValues as $key => $value) {
        $_ENV[$key] = (string) $value;
        $_SERVER[$key] = (string) $value;
    }
}

\App\Core\Config::set('app', require base_path('config/app.php'));
\App\Core\Config::set('database', require base_path('config/database.php'));
\App\Core\Config::set('razorpay', require base_path('config/razorpay.php'));

$resolvedAppUrl = rtrim((string) resolve_base_url(), '/');
if ($resolvedAppUrl !== '') {
    \App\Core\Config::set('app', array_merge(
        (array) config('app', []),
        ['url' => $resolvedAppUrl]
    ));
}

$appKey = trim((string) config('app.encryption_key', ''));
if ($appKey === '') {
    throw new RuntimeException('APP_KEY must be configured before the application can start.');
}

if (strlen($appKey) < 32) {
    throw new RuntimeException('APP_KEY must be at least 32 characters long.');
}

$normalizedAppKey = strtolower($appKey);
foreach (['replace_with_a_long_random_secret_key', 'changeme', 'app_key', 'default_app_key'] as $blockedValue) {
    if (str_contains($normalizedAppKey, $blockedValue)) {
        throw new RuntimeException('APP_KEY is using an insecure placeholder and must be rotated.');
    }
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', base_path());
}

if (!defined('BASE_URL')) {
    define('BASE_URL', rtrim((string) config('app.url', ''), '/'));
}

if (!defined('ASSET_URL')) {
    define('ASSET_URL', BASE_URL . '/assets/');
}

date_default_timezone_set((string) config('app.timezone', 'Asia/Kolkata'));
\App\Core\ExceptionHandler::register();

if (PHP_SAPI !== 'cli') {
    \App\Core\Session::start();
}

$router = new \App\Core\Router();
$router->aliasMiddleware('auth', \App\Middleware\AuthMiddleware::class);
$router->aliasMiddleware('csrf', \App\Middleware\CsrfMiddleware::class);
$router->aliasMiddleware('guest', \App\Middleware\GuestMiddleware::class);
$router->aliasMiddleware('role', \App\Middleware\RoleMiddleware::class);
$router->aliasMiddleware('permission', \App\Middleware\PermissionMiddleware::class);

return $router;

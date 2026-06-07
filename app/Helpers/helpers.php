<?php

declare(strict_types=1);

use App\Core\Config;

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $base = dirname(__DIR__, 2);
        return $path === '' ? $base : $base . DIRECTORY_SEPARATOR . ltrim($path, '\\/');
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('asset')) {
    function asset(string $path = ''): string
    {
        $assetUrl = defined('ASSET_URL')
            ? rtrim(ASSET_URL, '/')
            : rtrim((string) config('app.url', ''), '/') . '/assets';

        return $path === '' ? $assetUrl : $assetUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void
    {
        header('Location: ' . url($path), true, 302);
        exit;
    }
}

if (!function_exists('e')) {
    function e(null|string|int|float $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $baseUrl = defined('BASE_URL')
            ? rtrim(BASE_URL, '/')
            : rtrim((string) config('app.url', ''), '/');

        return $path === '' ? $baseUrl : $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('pagination_url')) {
    function pagination_url(string $path, array $query = [], int $page = 1): string
    {
        $query['page'] = max(1, $page);
        $queryString = http_build_query(array_filter(
            $query,
            static fn (mixed $value): bool => $value !== null && $value !== ''
        ));

        $target = url($path);

        return $queryString === '' ? $target : $target . '?' . $queryString;
    }
}

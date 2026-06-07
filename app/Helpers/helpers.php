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
            : rtrim(resolve_base_url(), '/');

        if ($path === '') {
            return $baseUrl;
        }

        $normalizedPath = ltrim($path, '/');

        if (should_use_index_routes()) {
            return $baseUrl . '/index.php/' . $normalizedPath;
        }

        return $baseUrl . '/' . $normalizedPath;
    }
}

if (!function_exists('resolve_base_url')) {
    function resolve_base_url(): string
    {
        $configured = rtrim((string) config('app.url', ''), '/');

        if (PHP_SAPI === 'cli') {
            return $configured;
        }

        $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
        $isLocalConfigured = $configured === ''
            || str_contains(strtolower($configured), 'localhost')
            || str_contains(strtolower($configured), '127.0.0.1');

        if ($host === '') {
            return $configured;
        }

        $isLocalHost = $host === 'localhost'
            || str_starts_with($host, '127.0.0.1')
            || str_starts_with($host, '[::1]');

        if (!$isLocalConfigured || $isLocalHost) {
            return $configured;
        }

        $https = $_SERVER['HTTPS'] ?? '';
        $scheme = (!empty($https) && strtolower((string) $https) !== 'off') ? 'https' : 'http';
        $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $basePath = trim(dirname($scriptName), '/');

        $derived = $scheme . '://' . $host;
        return $basePath === '' ? $derived : $derived . '/' . $basePath;
    }
}

if (!function_exists('should_use_index_routes')) {
    function should_use_index_routes(): bool
    {
        if (PHP_SAPI === 'cli') {
            return false;
        }

        $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
        if ($host === '' || $host === 'localhost' || str_starts_with($host, '127.0.0.1') || str_starts_with($host, '[::1]')) {
            return false;
        }

        $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        if (str_contains($requestUri, '/index.php/')) {
            return true;
        }

        return true;
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

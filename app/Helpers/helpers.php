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

        return true;
    }
}

if (!function_exists('label_case')) {
    function label_case(string $key): string
    {
        static $labels = [
            'N/A' => 'N/A',
            'E-VERIFICATION' => 'e-Verification',
            'E_VERIFICATION' => 'e-Verification',
            'E_VERIFICATION_PENDING' => 'e-Verification Pending',
            'E_VERIFICATION_DONE' => 'e-Verification Complete',
            'CLIENT_CLARIFICATION_PENDING' => 'Client Clarification Pending',
            'FOLLOWED_UP' => 'Followed Up',
            'IN_PROGRESS' => 'In Progress',
            'NOT_STORED' => 'Not Stored',
            'SECURE_CUSTODY' => 'Secure Custody',
            'WITH_CLIENT' => 'With Client',
            'WITH_OFFICE' => 'With Office',
            'WITH_STAFF' => 'With Staff',
        ];

        $normalized = strtoupper(trim($key));
        if (isset($labels[$normalized])) {
            return $labels[$normalized];
        }

        static $acronyms = ['sla', 'pso', 'dsc', 'gst', 'tds', 'pan', 'tan', 'itr', 'id', 'crm', 'arn'];
        $words = preg_split('/[_\s]+/', trim($key)) ?: [];
        foreach ($words as &$word) {
            $word = in_array(strtolower($word), $acronyms, true)
                ? strtoupper($word)
                : ucfirst(strtolower($word));
        }
        return implode(' ', $words);
    }
}

if (!function_exists('money_inr')) {
    function money_inr(float|int|string|null $amount): string
    {
        return 'INR ' . number_format((float) ($amount ?? 0), 2, '.', ',');
    }
}

if (!function_exists('metric_severity')) {
    function metric_severity(string $key): string
    {
        $key = strtolower($key);
        $danger = ['breach', 'overdue'];
        $warning = ['pending', 'unpaid', 'due', 'unbilled'];
        $success = ['closed', 'online', 'approved', 'recently', 'paid'];

        foreach ($danger as $needle) {
            if (str_contains($key, $needle)) {
                return 'danger';
            }
        }
        foreach ($warning as $needle) {
            if (str_contains($key, $needle)) {
                return 'warning';
            }
        }
        foreach ($success as $needle) {
            if (str_contains($key, $needle)) {
                return 'success';
            }
        }

        return 'neutral';
    }
}

if (!function_exists('severity_hex')) {
    function severity_hex(string $severity): string
    {
        return match ($severity) {
            'danger' => '#dc2626',
            'warning' => '#d97706',
            'success' => '#16a34a',
            default => '#1499a8',
        };
    }
}

if (!function_exists('metric_icon_svg')) {
    function metric_icon_svg(string $severity): string
    {
        $paths = [
            'danger' => '<path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
            'warning' => '<circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/>',
            'success' => '<path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22,4 12,14.01 9,11.01"/>',
            'neutral' => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
        ];
        $inner = $paths[$severity] ?? $paths['neutral'];

        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' . $inner . '</svg>';
    }
}

if (!function_exists('status_severity')) {
    function status_severity(string $value): string
    {
        $value = strtoupper($value);
        $danger = ['OVERDUE', 'BREACH', 'REJECTED', 'CANCELLED', 'BLOCKED', 'UNPAID', 'FAILED', 'DISPUTED'];
        $warning = ['PENDING', 'SUBMITTED', 'UNDER_REVIEW', 'DRAFT', 'SENT', 'PARTIALLY'];
        $success = ['DONE', 'CLOSED', 'APPROVED', 'PAID', 'COMPLETED', 'ACTIVE', 'VERIFIED'];

        foreach ($danger as $needle) {
            if (str_contains($value, $needle)) {
                return 'danger';
            }
        }
        foreach ($warning as $needle) {
            if (str_contains($value, $needle)) {
                return 'warning';
            }
        }
        foreach ($success as $needle) {
            if (str_contains($value, $needle)) {
                return 'success';
            }
        }

        return 'neutral';
    }
}

if (!function_exists('priority_severity')) {
    function priority_severity(string $priority): string
    {
        return match (strtoupper($priority)) {
            'CRITICAL' => 'danger',
            'HIGH' => 'warning',
            'LOW' => 'neutral',
            default => 'neutral',
        };
    }
}

if (!function_exists('due_badge')) {
    function due_badge(string $datetime): array
    {
        $timestamp = strtotime($datetime);
        if ($timestamp === false) {
            return ['severity' => 'neutral', 'label' => '-'];
        }

        $daysUntil = (int) floor((strtotime(date('Y-m-d', $timestamp)) - strtotime(date('Y-m-d'))) / 86400);

        if ($daysUntil <= 1) {
            return ['severity' => 'danger', 'label' => $daysUntil <= 0 ? 'Due Today' : 'Tomorrow'];
        }
        if ($daysUntil <= 5) {
            return ['severity' => 'warning', 'label' => "In {$daysUntil} days"];
        }

        return ['severity' => 'info', 'label' => "In {$daysUntil} days"];
    }
}

if (!function_exists('relative_time')) {
    function relative_time(string $datetime): string
    {
        $timestamp = strtotime($datetime);
        if ($timestamp === false) {
            return $datetime;
        }

        $diff = time() - $timestamp;
        if ($diff < 60) {
            return 'Just now';
        }
        if ($diff < 3600) {
            $minutes = (int) floor($diff / 60);
            return $minutes . ' minute' . ($minutes === 1 ? '' : 's') . ' ago';
        }
        if ($diff < 86400) {
            $hours = (int) floor($diff / 3600);
            return $hours . ' hour' . ($hours === 1 ? '' : 's') . ' ago';
        }
        if ($diff < 172800) {
            return 'Yesterday';
        }
        $days = (int) floor($diff / 86400);
        if ($days < 7) {
            return $days . ' days ago';
        }

        return date('d M Y', $timestamp);
    }
}

if (!function_exists('queue_cell_html')) {
    function queue_cell_html(string $key, mixed $value): string
    {
        $value = trim((string) ($value ?? ''));
        $keyLower = strtolower($key);

        if ($value === '') {
            return '<span class="subtle">&mdash;</span>';
        }

        if (str_contains($keyLower, 'status') || str_contains($keyLower, 'stage')) {
            $severity = status_severity($value);
            return '<span class="badge badge-' . e($severity) . '">' . e(label_case($value)) . '</span>';
        }

        if (str_contains($keyLower, 'client_name') || $keyLower === 'name') {
            return '<span class="cell-with-avatar">'
                . '<span class="avatar-chip">' . e(strtoupper(substr($value, 0, 1))) . '</span>'
                . '<span>' . e($value) . '</span></span>';
        }

        if (str_ends_with($keyLower, '_at')) {
            $timestamp = strtotime($value);
            if ($timestamp !== false) {
                return e(date('d M Y', $timestamp));
            }
        }

        return e($value);
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

<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'Compliance Management System'),
    'env' => env('APP_ENV', 'local'),
    'debug' => filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOL),
    'url' => env('APP_URL', 'http://localhost/etask/public'),
    'timezone' => env('APP_TIMEZONE', 'Asia/Kolkata'),
    'session_name' => env('SESSION_NAME', 'COMPLIANCESESSID'),
    'encryption_key' => trim((string) env('APP_KEY', '')),
    'upload_max_bytes' => (int) env('UPLOAD_MAX_BYTES', '5242880'),
    'private_storage_path' => env('PRIVATE_STORAGE_PATH', dirname(base_path(), 2) . DIRECTORY_SEPARATOR . 'epani_private_storage'),
    'mail_from_name' => env('MAIL_FROM_NAME', 'e-Pani'),
    'mail_from_address' => env('MAIL_FROM_ADDRESS', 'noreply@localhost.test'),
];

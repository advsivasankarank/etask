<?php

declare(strict_types=1);

namespace App\Core;

final class Logger
{
    public static function info(string $event, array $context = []): void
    {
        self::write('INFO', $event, $context);
    }

    public static function warning(string $event, array $context = []): void
    {
        self::write('WARNING', $event, $context);
    }

    public static function error(string $event, array $context = []): void
    {
        self::write('ERROR', $event, $context);
    }

    private static function write(string $level, string $event, array $context): void
    {
        $logDirectory = base_path('storage/logs');
        if (!is_dir($logDirectory) && !mkdir($logDirectory, 0775, true) && !is_dir($logDirectory)) {
            return;
        }

        $payload = [
            'timestamp' => date('c'),
            'level' => $level,
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'uri' => $_SERVER['REQUEST_URI'] ?? null,
            'method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'context' => self::sanitizeContext($context),
        ];

        $encoded = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($encoded === false) {
            return;
        }

        @file_put_contents(
            $logDirectory . DIRECTORY_SEPARATOR . 'application-' . date('Y-m-d') . '.log',
            $encoded . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }

    private static function sanitizeContext(array $context): array
    {
        foreach ($context as $key => $value) {
            if (is_array($value)) {
                $context[$key] = self::sanitizeContext($value);
                continue;
            }

            if (is_object($value)) {
                $context[$key] = '[object ' . $value::class . ']';
                continue;
            }

            if (is_resource($value)) {
                $context[$key] = '[resource]';
                continue;
            }

            if (in_array((string) $key, [
                'password',
                'new_password',
                'current_password',
                '_token',
                'gateway_signature',
                'password_hash',
                'aadhaar_no',
                'aadhaar_ciphertext',
                'aadhaar_iv',
                'app_key',
                'encryption_key',
            ], true)) {
                $context[$key] = '[redacted]';
                continue;
            }

            if (is_string($value) && strlen($value) > 500) {
                $context[$key] = substr($value, 0, 500) . '...';
            }
        }

        return $context;
    }
}

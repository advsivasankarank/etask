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
        if (!is_dir($logDirectory) && !mkdir($logDirectory, 0750, true) && !is_dir($logDirectory)) {
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

        $logFile = $logDirectory . DIRECTORY_SEPARATOR . 'application-' . date('Y-m-d') . '.log';
        $result = file_put_contents($logFile, $encoded . PHP_EOL, FILE_APPEND | LOCK_EX);

        if ($result === false) {
            error_log('e-Pani Logger: Failed to write to ' . $logFile);
        }
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

            $sensitivePatterns = ['password', 'token', 'secret', 'key', 'aadhaar', 'credential', 'otp', 'signature'];
            $normalizedKey = strtolower((string) $key);
            $isSensitive = false;
            foreach ($sensitivePatterns as $pattern) {
                if (str_contains($normalizedKey, $pattern)) {
                    $isSensitive = true;
                    break;
                }
            }

            if ($isSensitive) {
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

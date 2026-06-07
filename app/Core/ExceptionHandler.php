<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

final class ExceptionHandler
{
    public static function register(): void
    {
        ini_set('display_errors', '0');
        error_reporting(E_ALL);

        set_exception_handler(static function (Throwable $exception): void {
            self::handle($exception);
        });

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        register_shutdown_function(static function (): void {
            $error = error_get_last();
            if ($error === null) {
                return;
            }

            if (!in_array($error['type'] ?? 0, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                return;
            }

            self::handle(new \ErrorException(
                (string) ($error['message'] ?? 'Fatal error'),
                0,
                (int) ($error['type'] ?? E_ERROR),
                (string) ($error['file'] ?? 'unknown'),
                (int) ($error['line'] ?? 0)
            ));
        });
    }

    public static function handle(Throwable $exception): void
    {
        Logger::error('application.exception', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);

        if (PHP_SAPI === 'cli') {
            fwrite(STDERR, 'ERROR: ' . $exception->getMessage() . PHP_EOL);
            exit(1);
        }

        http_response_code(500);

        try {
            echo View::render(base_path('app/Views/errors/500.php'), [
                'title' => 'Server Error',
            ], null);
        } catch (Throwable) {
            echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Server Error</title></head><body><h1>500</h1><p>An unexpected error occurred.</p></body></html>';
        }
    }
}

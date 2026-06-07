<?php

declare(strict_types=1);

namespace App\Core;

final class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register(static function (string $class): void {
            $prefixes = [
                'App\\' => base_path('app'),
                'Modules\\' => base_path('modules'),
            ];

            foreach ($prefixes as $prefix => $baseDir) {
                if (!str_starts_with($class, $prefix)) {
                    continue;
                }

                $relativeClass = substr($class, strlen($prefix));
                $file = $baseDir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

                if (is_file($file)) {
                    require_once $file;
                }

                return;
            }
        });
    }
}

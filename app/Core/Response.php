<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function html(string $content, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo $content;
    }

    public static function abort(int $statusCode, string $message): never
    {
        http_response_code($statusCode);
        echo $message;
        exit;
    }

    public static function download(string $absolutePath, string $downloadName, string $mimeType = 'application/octet-stream'): never
    {
        if (!is_file($absolutePath) || !is_readable($absolutePath)) {
            self::abort(404, 'Requested file not found.');
        }

        $fileSize = filesize($absolutePath);
        if ($fileSize === false) {
            self::abort(500, 'Unable to read file.');
        }

        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . rawurlencode($downloadName) . '"');
        header('Content-Length: ' . (string) $fileSize);
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($absolutePath);
        exit;
    }

    public static function inlineFile(string $absolutePath, string $displayName, string $mimeType = 'application/octet-stream'): never
    {
        if (!is_file($absolutePath) || !is_readable($absolutePath)) {
            self::abort(404, 'Requested file not found.');
        }

        $fileSize = filesize($absolutePath);
        if ($fileSize === false) {
            self::abort(500, 'Unable to read file.');
        }

        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: inline; filename="' . rawurlencode($displayName) . '"');
        header('Content-Length: ' . (string) $fileSize);
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=300');

        readfile($absolutePath);
        exit;
    }
}

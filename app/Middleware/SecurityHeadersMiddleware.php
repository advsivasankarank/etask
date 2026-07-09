<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;

final class SecurityHeadersMiddleware
{
    public function handle(Request $request, array $params = []): void
    {
        if (headers_sent()) {
            return;
        }

        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');

        if (config('app.env', 'local') === 'production') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;

final class CsrfMiddleware
{
    public function handle(Request $request, array $params = []): void
    {
        if ($request->method() !== 'POST') {
            return;
        }

        if (Csrf::verify((string) $request->input('_token'))) {
            return;
        }

        Session::flash('error', 'Security token mismatch. Please refresh the page and try again.');

        if ($this->expectsHtmlResponse()) {
            redirect($this->fallbackPath());
        }

        Response::html(
            View::render(base_path('app/Views/errors/403.php'), ['title' => 'Security Validation Failed']),
            403
        );
        exit;
    }

    private function expectsHtmlResponse(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';

        return !is_string($accept) || $accept === '' || str_contains($accept, 'text/html');
    }

    private function fallbackPath(): string
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $refererPath = is_string($referer) ? (parse_url($referer, PHP_URL_PATH) ?: '') : '';

        if (is_string($refererPath) && $refererPath !== '' && str_contains($refererPath, '/public/')) {
            $publicPath = strstr($refererPath, '/public/');

            if (is_string($publicPath)) {
                $path = substr($publicPath, strlen('/public'));
                return $path === '' ? '/dashboard' : $path;
            }
        }

        return '/dashboard';
    }
}

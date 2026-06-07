<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

final class PermissionMiddleware
{
    public function handle(Request $request, array $params = []): void
    {
        if ($params === [] || Auth::canAny(...$params)) {
            return;
        }

        Response::html(
            View::render(base_path('app/Views/errors/403.php'), ['title' => 'Access Denied'], null),
            403
        );
        exit;
    }
}

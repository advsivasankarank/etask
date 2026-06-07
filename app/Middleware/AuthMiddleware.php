<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;

final class AuthMiddleware
{
    public function handle(Request $request, array $params = []): void
    {
        if (!Auth::check()) {
            Session::flash('error', 'Please log in to continue.');
            redirect('/login');
        }

        $user = Auth::user() ?? [];
        $path = $request->path();

        if (!empty($user['must_change_password']) && !in_array($path, ['/change-password', '/logout'], true)) {
            Session::flash('error', 'Please change your password before continuing.');
            redirect('/change-password');
        }
    }
}

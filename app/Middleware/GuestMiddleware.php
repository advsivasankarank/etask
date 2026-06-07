<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;

final class GuestMiddleware
{
    public function handle(Request $request, array $params = []): void
    {
        if (Auth::check()) {
            if (!empty((Auth::user() ?? [])['must_change_password'])) {
                Session::flash('error', 'Please change your password before continuing.');
                redirect('/change-password');
            }

            redirect('/dashboard');
        }
    }
}

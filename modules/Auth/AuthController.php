<?php

declare(strict_types=1);

namespace Modules\Auth;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\AuthService;
use App\Services\UserService;
use Throwable;

final class AuthController
{
    public function __construct(
        private readonly AuthService $authService = new AuthService(),
        private readonly UserService $userService = new UserService()
    ) {
    }

    public function showLogin(): void
    {
        $content = View::render(base_path('modules/Auth/views/login.php'), [
            'title' => 'Login',
            'error' => Session::pullFlash('error'),
            'success' => Session::pullFlash('success'),
            'old_username' => Session::pullFlash('old_username'),
        ], 'auth');

        Response::html($content);
    }

    public function showLanding(): void
    {
        $content = View::render(base_path('modules/Auth/views/landing.php'), [
            'title' => 'Welcome',
            'success' => Session::pullFlash('success'),
        ], null);

        Response::html($content);
    }

    public function login(Request $request): void
    {
        $username = (string) $request->input('username', '');
        $password = (string) $request->input('password', '');
        Session::flash('old_username', $username);

        $result = $this->authService->attempt($username, $password, $request);

        if (!$result['success']) {
            Session::flash('error', $result['message']);
            redirect('/login');
        }

        Session::flash('success', 'Welcome back, ' . (Auth::user()['full_name'] ?? 'User') . '.');

        if (!empty((Auth::user() ?? [])['must_change_password'])) {
            Session::flash('error', 'Please change your password before continuing.');
            redirect('/change-password');
        }

        redirect('/dashboard');
    }

    public function logout(Request $request): void
    {
        Auth::logout();
        Session::flash('success', 'You have been logged out successfully.');
        redirect('/');
    }

    public function showChangePassword(): void
    {
        if (!Auth::check()) {
            Session::flash('error', 'Please log in to continue.');
            redirect('/login');
        }

        $content = View::render(base_path('modules/Auth/views/change-password.php'), [
            'title' => 'Change Password',
            'error' => Session::pullFlash('error'),
            'success' => Session::pullFlash('success'),
        ], 'auth');

        Response::html($content);
    }

    public function changePassword(Request $request): void
    {
        $userId = (int) Auth::id();

        if ($userId <= 0) {
            Session::flash('error', 'Please log in to continue.');
            redirect('/login');
        }

        $newPassword = (string) $request->input('new_password', '');
        $confirmPassword = (string) $request->input('confirm_password', '');

        if ($newPassword !== $confirmPassword) {
            Session::flash('error', 'New password and confirmation password must match.');
            redirect('/change-password');
        }

        try {
            $this->userService->changeOwnPassword(
                $userId,
                (string) $request->input('current_password', ''),
                $newPassword
            );
            $sessionUser = Auth::user() ?? [];
            $sessionUser['must_change_password'] = false;
            Auth::login($sessionUser);
            Session::flash('success', 'Password changed successfully.');
            redirect('/dashboard');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/change-password');
        }
    }
}

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

    public function showLogin(Request $request): void
    {
        $audience = strtolower((string) $request->input('audience', 'internal'));
        if (!in_array($audience, ['internal', 'portal'], true)) {
            $audience = 'internal';
        }

        $content = View::render(base_path('modules/Auth/views/login.php'), [
            'title' => 'Login',
            'audience' => $audience,
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

    public function showForgotPassword(Request $request): void
    {
        $audience = strtolower((string) $request->input('audience', 'portal'));
        if ($audience !== 'portal') {
            $audience = 'portal';
        }

        $content = View::render(base_path('modules/Auth/views/forgot-password.php'), [
            'title' => 'Forgot Password',
            'audience' => $audience,
            'error' => Session::pullFlash('error'),
            'success' => Session::pullFlash('success'),
            'old_username' => Session::pullFlash('old_username'),
            'old_verification' => Session::pullFlash('old_verification'),
        ], 'auth');

        Response::html($content);
    }

    public function forgotPassword(Request $request): void
    {
        $username = (string) $request->input('username', '');
        $verification = (string) $request->input('verification', '');
        Session::flash('old_username', $username);
        Session::flash('old_verification', $verification);

        try {
            $token = $this->authService->startPortalPasswordReset(
                $this->normalizePortalLoginUsername($username),
                $verification,
                $request
            );

            if ($token === null) {
                Session::flash('error', 'The portal username and verification detail did not match our records.');
                redirect('/forgot-password?audience=portal');
            }

            Session::flash('success', 'Verification successful. Set a new password to continue.');
            redirect('/reset-password?audience=portal&token=' . urlencode($token));
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/forgot-password?audience=portal');
        }
    }

    public function showResetPassword(Request $request): void
    {
        $token = trim((string) $request->input('token', ''));
        $audience = strtolower((string) $request->input('audience', 'portal'));
        if ($audience !== 'portal') {
            $audience = 'portal';
        }

        if ($token === '' || $this->authService->validatePortalResetToken($token) === null) {
            Session::flash('error', 'The password reset link is invalid or has expired.');
            redirect('/forgot-password?audience=portal');
        }

        $content = View::render(base_path('modules/Auth/views/reset-password.php'), [
            'title' => 'Reset Password',
            'audience' => $audience,
            'token' => $token,
            'error' => Session::pullFlash('error'),
            'success' => Session::pullFlash('success'),
        ], 'auth');

        Response::html($content);
    }

    public function resetPassword(Request $request): void
    {
        $token = trim((string) $request->input('token', ''));
        $newPassword = (string) $request->input('new_password', '');
        $confirmPassword = (string) $request->input('confirm_password', '');

        if ($newPassword !== $confirmPassword) {
            Session::flash('error', 'New password and confirmation password must match.');
            redirect('/reset-password?audience=portal&token=' . urlencode($token));
        }

        try {
            $this->authService->completePortalPasswordReset($token, $newPassword);
            Session::flash('success', 'Password reset successful. Please sign in with your new password.');
            redirect('/login?audience=portal');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/reset-password?audience=portal&token=' . urlencode($token));
        }
    }

    public function login(Request $request): void
    {
        $username = (string) $request->input('username', '');
        $password = (string) $request->input('password', '');
        $audience = strtolower((string) $request->input('audience', 'internal'));
        if ($audience === 'portal') {
            $username = $this->normalizePortalLoginUsername($username);
        }
        Session::flash('old_username', $username);

        $result = $this->authService->attempt($username, $password, $request);

        if (!$result['success']) {
            Session::flash('error', $result['message']);
            redirect('/login?audience=' . $audience);
        }

        if ($audience === 'portal' && !Auth::isPortalUser()) {
            Auth::logout();
            Session::flash('error', 'Use Internal User login for staff and consultants.');
            redirect('/login?audience=portal');
        }

        if ($audience === 'internal' && Auth::isPortalUser()) {
            Auth::logout();
            Session::flash('error', 'Use Portal User login for client accounts.');
            redirect('/login?audience=internal');
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

    private function normalizePortalLoginUsername(string $username): string
    {
        $trimmed = preg_replace('/\s+/', '', trim($username)) ?? '';
        $digits = preg_replace('/\D+/', '', $trimmed) ?? '';

        if (strlen($digits) === 12) {
            return $digits;
        }

        return strtoupper($trimmed);
    }
}

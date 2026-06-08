<?php

declare(strict_types=1);

namespace Modules\Users;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\UserRepository;
use App\Services\UserService;
use Throwable;

final class UserController
{
    public function __construct(
        private readonly UserRepository $users = new UserRepository(),
        private readonly UserService $userService = new UserService()
    ) {
    }

    public function index(Request $request): void
    {
        $portalOnly = Auth::can('users.manage.portal') && !Auth::can('users.manage.internal');
        $search = trim((string) $request->input('search', ''));
        $page = max(1, (int) $request->input('page', 1));
        $pagination = $this->users->paginateSearch($search, $portalOnly, true, $page, 12);

        $content = View::render(base_path('modules/Users/views/index.php'), [
            'title' => 'Users',
            'activeMenu' => 'users',
            'users' => $pagination['items'],
            'pagination' => $pagination,
            'search' => $search,
            'portalOnly' => $portalOnly,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function create(Request $request): void
    {
        $userType = strtoupper((string) $request->input('user_type', Session::pullFlash('user_type', 'INTERNAL')));
        if (!in_array($userType, ['INTERNAL', 'PORTAL'], true)) {
            $userType = Auth::can('users.manage.portal') && !Auth::can('users.manage.internal') ? 'PORTAL' : 'INTERNAL';
        }

        $this->guardAccessForType($userType);

        $content = View::render(base_path('modules/Users/views/form.php'), [
            'title' => 'Create User',
            'activeMenu' => 'users',
            'mode' => 'create',
            'userType' => $userType,
            'user' => null,
            'selectedRoleIds' => [],
            'roles' => $this->users->activeRoles($userType === 'PORTAL'),
            'clientContacts' => $this->users->activeClientContacts(),
            'old' => Session::pullFlash('old', []),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function store(Request $request): void
    {
        $payload = $request->all();
        $userType = strtoupper((string) ($payload['user_type'] ?? 'INTERNAL'));
        Session::flash('old', $payload);
        Session::flash('user_type', $userType);

        try {
            $userId = $this->userService->create($payload, Auth::user() ?? []);
            Session::flash('success', 'User created successfully.');
            redirect('/users/show?id=' . $userId);
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/users/create?user_type=' . strtolower($userType));
        }
    }

    public function show(Request $request): void
    {
        $user = $this->findAccessibleUser((int) $request->input('id', 0));

        $content = View::render(base_path('modules/Users/views/show.php'), [
            'title' => 'User Details',
            'activeMenu' => 'users',
            'userRecord' => $user,
            'userType' => $this->inferUserType($user),
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function edit(Request $request): void
    {
        $user = $this->findAccessibleUser((int) $request->input('id', 0));
        $userType = $this->inferUserType($user);

        $content = View::render(base_path('modules/Users/views/form.php'), [
            'title' => 'Edit User',
            'activeMenu' => 'users',
            'mode' => 'edit',
            'userType' => $userType,
            'user' => $user,
            'selectedRoleIds' => array_map(static fn (array $role): int => (int) $role['id'], $user['roles'] ?? []),
            'roles' => $this->users->activeRoles($userType === 'PORTAL'),
            'clientContacts' => $this->users->activeClientContacts(),
            'old' => Session::pullFlash('old', []),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function update(Request $request): void
    {
        $userId = (int) $request->input('id', 0);
        $payload = $request->all();
        Session::flash('old', $payload);

        try {
            $this->userService->update($userId, $payload, Auth::user() ?? []);
            Session::flash('success', 'User updated successfully.');
            redirect('/users/show?id=' . $userId);
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/users/edit?id=' . $userId);
        }
    }

    public function archive(Request $request): void
    {
        $userId = (int) $request->input('id', 0);

        try {
            $this->userService->archive($userId, Auth::user() ?? []);
            Session::flash('success', 'User archived successfully.');
            redirect('/users');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/users/show?id=' . $userId);
        }
    }

    public function activate(Request $request): void
    {
        $userId = (int) $request->input('id', 0);

        try {
            $this->userService->activate($userId, Auth::user() ?? []);
            Session::flash('success', 'User activated successfully.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/users/show?id=' . $userId);
    }

    public function resetPassword(Request $request): void
    {
        $userId = (int) $request->input('id', 0);

        try {
            $this->userService->resetPassword($userId, (string) $request->input('new_password', ''), Auth::user() ?? []);
            Session::flash('success', 'Password reset successfully. User must change password on next login.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/users/show?id=' . $userId);
    }

    public function rights(Request $request): void
    {
        $this->guardRightsManagementAccess();
        $userId = (int) $request->input('id', 0);
        $catalog = $this->userService->rightsCatalogForUser($userId);

        $content = View::render(base_path('modules/Users/views/rights.php'), [
            'title' => 'Manage Rights',
            'activeMenu' => 'users',
            'rightsUser' => $catalog['user'],
            'rightsGroups' => $catalog['groups'],
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function saveRights(Request $request): void
    {
        $this->guardRightsManagementAccess();
        $userId = (int) $request->input('id', 0);

        try {
            $this->userService->updateGrantedRights($userId, (array) $request->input('granted_permissions', []), Auth::user() ?? []);
            Session::flash('success', 'User rights updated successfully.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/users/rights?id=' . $userId);
    }

    private function guardRightsManagementAccess(): void
    {
        if (Auth::can('users.manage.rights') || Auth::hasRole('SUPER_ADMIN')) {
            return;
        }

        Response::abort(403, 'Only Super Admin can manage user rights.');
    }

    private function findAccessibleUser(int $userId): array
    {
        $user = $this->users->findDetailedById($userId);
        if ($user === null) {
            Response::abort(404, 'User not found.');
        }

        $this->guardAccessForType($this->inferUserType($user));

        return $user;
    }

    private function inferUserType(array $user): string
    {
        return !empty($user['client_contact_id']) ? 'PORTAL' : 'INTERNAL';
    }

    private function guardAccessForType(string $userType): void
    {
        if ($userType === 'PORTAL') {
            if (Auth::can('users.manage.portal')) {
                return;
            }
        } elseif (Auth::can('users.manage.internal')) {
            return;
        }

        Response::abort(403, 'You are not allowed to manage this user type.');
    }
}

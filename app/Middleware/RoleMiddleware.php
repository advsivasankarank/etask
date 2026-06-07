<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

final class RoleMiddleware
{
    private const ROLE_PERMISSION_MAP = [
        'SUPER_ADMIN' => ['users.manage.internal'],
        'ADMIN' => ['users.manage.internal'],
        'CRM' => ['clients.view'],
        'ASSISTANT_CRM' => ['clients.view'],
        'BACKEND_STAFF' => ['service_orders.view'],
        'DEO' => ['service_orders.view'],
        'ACCOUNTS' => ['billing.view'],
        'CONSULTANT' => ['consultants.deliverables.upload', 'consultants.bills.create', 'consultants.payments.record'],
        'CLIENT' => ['portal.self_access'],
    ];

    public function handle(Request $request, array $params = []): void
    {
        if ($params === [] || $this->authorized($params)) {
            return;
        }

        Response::html(
            View::render(base_path('app/Views/errors/403.php'), ['title' => 'Access Denied'], null),
            403
        );
        exit;
    }

    private function authorized(array $params): bool
    {
        foreach ($params as $param) {
            $normalized = strtoupper(trim((string) $param));

            if ($normalized === 'CLIENT' && Auth::isPortalUser()) {
                return true;
            }

            if ($normalized === 'CONSULTANT' && Auth::isConsultantUser()) {
                return true;
            }

            $permissions = self::ROLE_PERMISSION_MAP[$normalized] ?? [];
            if ($permissions !== [] && Auth::canAny(...$permissions)) {
                return true;
            }
        }

        return false;
    }
}

<?php

declare(strict_types=1);

namespace Modules\Dashboard;

use App\Core\Auth;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\DashboardService;

final class DashboardController
{
    private DashboardService $dashboard;

    public function __construct()
    {
        $this->dashboard = new DashboardService();
    }

    public function index(): void
    {
        $content = View::render(base_path('modules/Dashboard/views/index.php'), [
            'title' => 'Dashboard',
            'activeMenu' => 'dashboard',
            'user' => Auth::user(),
            'dashboard' => $this->dashboard->buildForCurrentUser(),
            'success' => Session::pullFlash('success'),
        ]);

        Response::html($content);
    }
}

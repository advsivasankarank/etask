<?php

declare(strict_types=1);

use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use Modules\Attendance\AttendanceController;
use Modules\Reports\ReportController;
use Modules\Search\SearchController;
use Modules\Users\UserController;

require dirname(__DIR__, 2) . '/bootstrap/app.php';

$connection = Database::connection();
$user = $connection->query(
    "SELECT id, username, full_name, email, client_contact_id
     FROM users
     WHERE client_contact_id IS NULL AND is_active = 1
     ORDER BY id ASC
     LIMIT 1"
)->fetch(PDO::FETCH_ASSOC);

if ($user === false) {
    fwrite(STDERR, 'No active internal user is available for the Phase 1 route smoke test.' . PHP_EOL);
    exit(1);
}

$permissions = array_column(
    $connection->query("SELECT code FROM permissions WHERE is_active = 1 ORDER BY code")->fetchAll(PDO::FETCH_ASSOC),
    'code'
);

Session::put('auth_user', [
    'id' => (int) $user['id'],
    'username' => (string) $user['username'],
    'full_name' => (string) $user['full_name'],
    'email' => (string) $user['email'],
    'client_contact_id' => $user['client_contact_id'],
    'client_id' => null,
    'actor_type' => 'INTERNAL',
    'roles' => ['SUPER_ADMIN'],
    'permissions' => $permissions,
]);

$request = new Request();
$checks = [
    '/reports' => static fn (): mixed => (new ReportController())->index(),
    '/reports/clients' => static fn (): mixed => (new ReportController())->clients($request),
    '/reports/service-orders' => static fn (): mixed => (new ReportController())->serviceOrders($request),
    '/reports/pso' => static fn (): mixed => (new ReportController())->pso($request),
    '/reports/invoices' => static fn (): mixed => (new ReportController())->invoices($request),
    '/reports/receipts' => static fn (): mixed => (new ReportController())->receipts($request),
    '/reports/outstanding' => static fn (): mixed => (new ReportController())->outstanding($request),
    '/reports/gst-summary' => static fn (): mixed => (new ReportController())->gstSummary($request),
    '/reports/revenue' => static fn (): mixed => (new ReportController())->revenue($request),
    '/reports/consultants' => static fn (): mixed => (new ReportController())->consultants($request),
    '/search' => static fn (): mixed => (new SearchController())->index($request),
    '/search/quick' => static fn (): mixed => (new SearchController())->quick($request),
    '/search/advanced' => static fn (): mixed => (new SearchController())->advanced($request),
    '/search/history' => static fn (): mixed => (new SearchController())->history($request),
    '/attendance/admin' => static fn (): mixed => (new AttendanceController())->adminReports(),
    '/attendance/productivity' => static fn (): mixed => (new AttendanceController())->productivity(),
    '/users/rights' => static fn (): mixed => (new UserController())->rights($request),
];

$failed = 0;
foreach ($checks as $route => $check) {
    $_GET = [];
    http_response_code(200);
    ob_start();

    try {
        $check();
        $html = (string) ob_get_clean();
        $status = http_response_code();

        if ($status >= 400 || $html === '' || str_contains($html, 'Something Went Wrong')) {
            throw new RuntimeException("Unexpected response status/content ({$status}).");
        }

        echo "PASS {$route}" . PHP_EOL;
    } catch (Throwable $throwable) {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        $failed++;
        echo "FAIL {$route}: " . $throwable->getMessage() . PHP_EOL;
    }
}

exit($failed === 0 ? 0 : 1);

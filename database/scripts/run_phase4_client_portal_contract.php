<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$files = [
    'layout' => $root . '/layouts/main.php',
    'routes' => $root . '/routes/web.php',
    'billing' => $root . '/modules/Billing/BillingController.php',
    'documents' => $root . '/app/Services/DocumentAccessService.php',
    'service_orders' => $root . '/modules/ServiceOrders/ServiceOrderController.php',
];

$source = [];
foreach ($files as $key => $path) {
    $source[$key] = file_get_contents($path);
    if ($source[$key] === false) {
        fwrite(STDERR, 'Unable to read Phase 4 contract source: ' . $path . PHP_EOL);
        exit(1);
    }
}

$checks = [
    'portal shell uses a shrink-safe content track' => str_contains($source['layout'], 'grid-template-columns: 260px minmax(0, 1fr)'),
    'portal content can shrink without page overflow' => preg_match('/\.portal-content\s*\{[^}]*min-width:\s*0;/s', $source['layout']) === 1,
    'mobile portal toggle controls the sidebar' => str_contains($source['layout'], 'id="portalMobileToggle"') && str_contains($source['layout'], 'aria-controls="portalSidebar"'),
    'mobile portal navigation includes a dismissible overlay' => str_contains($source['layout'], 'id="portalSidebarOverlay"') && str_contains($source['layout'], "event.key === 'Escape'"),
    'account navigation is active only on the account route' => str_contains($source['layout'], "\$requestUri === '/client-portal/account' ? 'active' : ''"),
    'service navigation covers PSO and service-order routes' => str_contains($source['layout'], "str_starts_with(\$requestUri, '/client-portal/pso')") && str_contains($source['layout'], "str_starts_with(\$requestUri, '/service-orders')"),
    'portal users can reach ownership-scoped document routes' => substr_count($source['routes'], 'documents.download,portal.self_access') >= 2 && str_contains($source['routes'], 'documents.view,documents.download,portal.self_access'),
    'portal users can reach ownership-scoped billing records' => str_contains($source['routes'], "'/billing/invoice'") && substr_count($source['routes'], 'billing.view,portal.self_access') === 2,
    'document and billing registers remain internally restricted' => str_contains($source['routes'], "'/documents', [DocumentController::class, 'index'], ['auth', 'permission:documents.view,documents.download']") && str_contains($source['routes'], "'/billing', [BillingController::class, 'index'], ['auth', 'permission:billing.view']"),
    'invoice ownership is enforced for portal users' => str_contains($source['billing'], "Auth::isPortalUser() && Auth::clientId() !== (int) (\$invoice['client_id'] ?? 0)"),
    'receipt ownership is enforced for portal users' => str_contains($source['billing'], "Auth::isPortalUser() && Auth::clientId() !== (int) (\$receipt['client_id'] ?? 0)"),
    'document access service enforces portal client ownership' => str_contains($source['documents'], "(int) (\$user['client_id'] ?? 0) !== \$documentClientId"),
    'service-order uploads enforce portal client ownership' => str_contains($source['service_orders'], "Auth::isPortalUser() && (int) (\$order['client_id'] ?? 0) !== (int) Auth::clientId()"),
];

$failed = 0;
foreach ($checks as $label => $passed) {
    echo ($passed ? 'PASS ' : 'FAIL ') . $label . PHP_EOL;
    if (!$passed) {
        $failed++;
    }
}

exit($failed === 0 ? 0 : 1);

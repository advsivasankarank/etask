<?php
$searchValue = trim((string) ($search ?? ''));

$statusMap = [
    'DOCUMENT_PENDING' => ['Awaiting Information', 'We are waiting for required information or supporting files from you.'],
    'REVIEW' => ['Under Review', 'Our team is reviewing the submitted information.'],
    'E_VERIFICATION_PENDING' => ['Awaiting Information', 'A final confirmation or verification is still pending.'],
    'E_VERIFICATION_DONE' => ['Completed', 'The service has been completed successfully.'],
    'PROCEDURALLY_CLOSED' => ['Completed', 'The service has been completed successfully.'],
];

function portalServiceStatusLabel(array $order, array $statusMap): array
{
    $stageCode = (string) ($order['current_stage_code'] ?? '');

    if (!empty($order['final_closed_at']) || !empty($order['accounting_closed_at']) || $stageCode === 'PROCEDURALLY_CLOSED') {
        return ['Completed', 'Your service has been completed and closed.'];
    }

    if (isset($statusMap[$stageCode])) {
        return $statusMap[$stageCode];
    }

    if (str_contains($stageCode, 'REVIEW') || str_contains($stageCode, 'CHECK')) {
        return ['Under Review', 'Our team is checking the current work before the next step.'];
    }

    if (str_contains($stageCode, 'ACKNOWLEDGEMENT')) {
        return ['Final Review', 'Final filing references are being confirmed and recorded.'];
    }

    if (str_contains($stageCode, 'FILING')) {
        return ['In Progress', 'The filing stage is currently being handled by our team.'];
    }

    return ['In Progress', 'Your service is currently being worked on by the E Tax Advisors team.'];
}
?>
<section class="panel">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="hero-card" style="display:grid;gap:18px;">
        <div class="eyebrow">Service Tracking</div>
        <h3 style="margin:0;font-size:2rem;">My Services</h3>
        <p class="subtle" style="margin:0;">Track the services being handled for your organization, review current status, and open the dedicated service workspace for updates and documents.</p>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="<?= e(url('/client-portal/account')) ?>" class="button button-secondary">Back to Dashboard</a>
            <a href="<?= e(url('/client-portal/pso')) ?>" class="button">Open Service Requests</a>
            <a href="<?= e(url('/client-portal/support')) ?>" class="button button-secondary">Support</a>
        </div>
    </div>

    <form method="get" action="<?= e(url('/service-orders')) ?>" class="search-bar">
        <input type="text" name="search" value="<?= e($searchValue) ?>" placeholder="Search by service, reference number, PAN, TAN, or client name" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
        <button type="submit" class="button">Search Services</button>
    </form>

    <?php if ($orders === []): ?>
        <div class="data-card"><span class="subtle">No active services are visible in your portal yet.</span></div>
    <?php else: ?>
        <div class="card-grid">
            <?php foreach ($orders as $order): ?>
                <?php [$clientStatus, $statusText] = portalServiceStatusLabel($order, $statusMap); ?>
                <article class="data-card" style="display:grid;gap:12px;">
                    <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                        <div>
                            <div class="eyebrow"><?= e($order['so_no']) ?></div>
                            <h4 style="margin:4px 0 0;"><?= e($order['service_type_name']) ?></h4>
                        </div>
                        <span class="chip chip-strong"><?= e($clientStatus) ?></span>
                    </div>

                    <div class="stat-line"><span>Service Number</span><strong><?= e($order['so_no']) ?></strong></div>
                    <div class="stat-line"><span>Service Type</span><strong><?= e($order['service_type_name']) ?></strong></div>
                    <div class="stat-line"><span>Service Period</span><strong><?= e($order['period_label'] ?: ($order['assessment_year'] ?: '-')) ?></strong></div>
                    <div class="stat-line"><span>Current Update</span><strong><?= e($statusText) ?></strong></div>
                    <div class="stat-line"><span>Created On</span><strong><?= e($order['created_at']) ?></strong></div>

                    <div style="display:flex;justify-content:flex-end;margin-top:4px;">
                        <a href="<?= e(url('/service-orders/show?id=' . $order['id'])) ?>" class="button">Track Service</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?= \App\Core\View::render(base_path('app/Views/partials/pagination.php'), [
            'pagination' => $pagination ?? null,
            'path' => '/service-orders',
            'query' => ['search' => $searchValue],
        ], null) ?>
    <?php endif; ?>
</section>

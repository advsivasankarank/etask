<?php
$historyByStage = [];
foreach ($workflowHistory as $historyRow) {
    $historyByStage[(string) $historyRow['stage_code']] = $historyRow;
}

$milestoneByStage = [];
foreach ($workflowMilestones as $milestoneRow) {
    $milestoneByStage[(string) $milestoneRow['stage_code']] = $milestoneRow;
}
$hasMilestoneTracking = $workflowMilestones !== [];

$linkedDocuments = $linkedDocuments ?? [];
$payments = $billing['payments'] ?? [];
$invoices = $billing['invoices'] ?? [];
$currentStageCode = (string) ($order['current_stage_code'] ?? '');

$statusLabel = 'In Progress';
$statusMeaning = 'Your service is currently being worked on by the E Tax Advisors team.';

if (!empty($order['final_closed_at']) || !empty($order['accounting_closed_at']) || !empty($order['procedural_closed_at']) || $currentStageCode === 'PROCEDURALLY_CLOSED') {
    $statusLabel = 'Completed';
    $statusMeaning = 'The service has been completed and all core work is closed.';
} elseif ((int) ($order['is_document_pending'] ?? 0) === 1 || $currentStageCode === 'DOCUMENT_PENDING') {
    $statusLabel = 'Awaiting Information';
    $statusMeaning = 'We are waiting for information or supporting documents from you before proceeding further.';
} elseif ($currentStageCode === 'REVIEW' || str_contains($currentStageCode, 'CHECK') || str_contains($currentStageCode, 'ACKNOWLEDGEMENT')) {
    $statusLabel = 'Under Review';
    $statusMeaning = 'Our team is reviewing the current work before moving to the next step.';
} elseif (in_array($currentStageCode, ['FILING_DONE', 'ITR_FILING_DONE', 'FORM_3CB_FILED'], true)) {
    $statusLabel = 'Final Review';
    $statusMeaning = 'Final filing and acknowledgement steps are being completed.';
}

$genericSteps = [
    ['code' => 'submitted', 'label' => 'Request Submitted', 'description' => 'Your service request has been accepted into our service journey.'],
    ['code' => 'review', 'label' => 'Under Review', 'description' => 'Our team is checking the information available for this service.'],
    ['code' => 'documents', 'label' => 'Documents Verified', 'description' => 'Required information has been reviewed for the next work stage.'],
    ['code' => 'progress', 'label' => 'In Progress', 'description' => 'The service work is actively being handled by our team.'],
    ['code' => 'final', 'label' => 'Final Review', 'description' => 'Final filing, confirmation, or completion steps are being wrapped up.'],
    ['code' => 'complete', 'label' => 'Completed', 'description' => 'The service has been completed successfully.'],
];

$clientStepIndex = 3;
if ($statusLabel === 'Awaiting Information') {
    $clientStepIndex = 1;
} elseif ($statusLabel === 'Under Review') {
    $clientStepIndex = 2;
} elseif ($statusLabel === 'In Progress') {
    $clientStepIndex = 3;
} elseif ($statusLabel === 'Final Review') {
    $clientStepIndex = 4;
} elseif ($statusLabel === 'Completed') {
    $clientStepIndex = 5;
}

$progressPercent = (int) round((($clientStepIndex + 1) / count($genericSteps)) * 100);

$nextStepLabel = $genericSteps[min($clientStepIndex + 1, count($genericSteps) - 1)]['label'];
if ($statusLabel === 'Completed') {
    $nextStepLabel = 'No further action is currently required';
}

$relationshipManager = $order['assigned_crm_name'] ?: ($order['assistant_crm_name'] ?: 'E Tax Advisors Team');
$supportingTeam = $order['assigned_backend_name'] ?? null;
$expectedCompletion = (string) ($order['sla_due_at'] ?: ($order['e_verification_due_date'] ?? ''));

$requiredDocumentLabels = [];
$pendingClarifications = [];
$pendingApprovals = [];

if ((int) ($order['is_document_pending'] ?? 0) === 1) {
    $requiredDocumentLabels[] = 'Supporting documents requested for this service';
}

foreach ($workflowStages as $stage) {
    $stageCode = (string) $stage['stage_code'];
    $milestone = $milestoneByStage[$stageCode] ?? null;
    $trackingStatus = strtoupper((string) ($milestone['tracking_status'] ?? ''));
    if ($trackingStatus === 'QUERY_PENDING') {
        $pendingClarifications[] = trim((string) ($milestone['remarks'] ?? 'Clarification is pending for the current work stage.'));
    }
}

foreach ($workflowReminders as $reminder) {
    $label = trim((string) ($reminder['title'] ?? $reminder['reminder_type'] ?? 'Pending update'));
    if ($label !== '') {
        $requiredDocumentLabels[] = $label;
    }
}

$requiredDocumentLabels = array_values(array_unique(array_filter($requiredDocumentLabels)));
$pendingClarifications = array_values(array_unique(array_filter($pendingClarifications)));

if ($currentStageCode === 'E_VERIFICATION_PENDING') {
    $pendingApprovals[] = 'Please complete the final confirmation or verification requested for this filing.';
}

$uploadedByYou = [];
$generatedForYou = [];
foreach ($linkedDocuments as $document) {
    if ((int) ($document['uploaded_by'] ?? 0) === (int) \App\Core\Auth::id()) {
        $uploadedByYou[] = $document;
    } else {
        $generatedForYou[] = $document;
    }
}

$invoiceStatus = 'No invoice raised yet';
$outstandingAmount = 0.0;
foreach ($invoices as $invoice) {
    $outstandingAmount += (float) ($invoice['outstanding_amount'] ?? 0);
}
if ($invoices !== []) {
    $invoiceStatus = $outstandingAmount > 0 ? 'Payment Pending' : 'Paid';
}

$receiptAvailable = false;
foreach ($payments as $payment) {
    if (!empty($payment['receipt_id'])) {
        $receiptAvailable = true;
        break;
    }
}

$activityFeed = [];
foreach ($activityTimeline as $entry) {
    $activityFeed[] = [
        'date' => (string) ($entry['created_at'] ?? ''),
        'title' => (string) ($entry['description'] ?? $entry['action_code'] ?? 'Service update'),
        'status' => (string) ($entry['action_code'] ?? 'UPDATE'),
    ];
}
foreach ($workflowHistory as $history) {
    $activityFeed[] = [
        'date' => (string) ($history['entered_at'] ?? ''),
        'title' => 'Service moved to ' . (string) ($history['stage_name'] ?? 'next stage'),
        'status' => 'STATUS CHANGE',
    ];
}
usort($activityFeed, static function (array $left, array $right): int {
    return strcmp((string) ($right['date'] ?? ''), (string) ($left['date'] ?? ''));
});
$activityFeed = array_slice($activityFeed, 0, 8);

$canUploadDocuments = \App\Core\Auth::canAny('service_orders.create', 'workflow.advance');
?>
<style>
    .portal-service-shell { display:grid; gap:20px; }
    .portal-service-hero {
        display:grid; gap:22px; padding:28px; border-radius:28px; color:#f8fbfc;
        background:linear-gradient(145deg, #0f4c5c 0%, #0f766e 56%, #ea8a2f 100%);
        box-shadow:0 24px 44px rgba(15,76,92,0.18);
    }
    .portal-service-hero-top { display:flex; justify-content:space-between; gap:18px; align-items:flex-start; flex-wrap:wrap; }
    .portal-service-kicker {
        font-size:0.78rem; letter-spacing:0.14em; text-transform:uppercase; color:rgba(242,247,249,0.84); font-weight:800;
    }
    .portal-service-hero h2 { margin:10px 0 8px; color:#ffffff; font-size:clamp(1.9rem, 3vw, 2.65rem); line-height:1.06; }
    .portal-service-subtitle { margin:0; max-width:760px; color:rgba(241,248,250,0.88); line-height:1.65; font-size:1rem; }
    .portal-status-badge {
        display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:999px;
        background:rgba(255,255,255,0.14); border:1px solid rgba(255,255,255,0.18); color:#ffffff; font-size:0.86rem; font-weight:800;
    }
    .portal-status-badge::before { content:""; width:8px; height:8px; border-radius:50%; background:#ffffff; }
    .portal-service-meta { display:grid; grid-template-columns:repeat(auto-fit, minmax(170px, 1fr)); gap:14px; }
    .portal-service-card {
        padding:16px 18px; border-radius:18px; background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.14);
    }
    .portal-service-label { font-size:0.75rem; letter-spacing:0.12em; text-transform:uppercase; color:rgba(241,247,250,0.76); font-weight:800; }
    .portal-service-value { margin-top:8px; color:#ffffff; font-size:1.04rem; line-height:1.45; font-weight:700; }
    .portal-next-action {
        display:grid; grid-template-columns:minmax(0, 1.15fr) auto; gap:16px; align-items:center; padding:18px 20px;
        border-radius:20px; background:rgba(255,255,255,0.14); border:1px solid rgba(255,255,255,0.14);
    }
    .portal-next-label { font-size:0.76rem; letter-spacing:0.12em; text-transform:uppercase; color:rgba(241,247,250,0.76); font-weight:800; }
    .portal-next-title { margin-top:8px; color:#ffffff; font-size:1.08rem; font-weight:800; }
    .portal-next-text { margin-top:6px; color:rgba(243,248,250,0.86); line-height:1.6; }
    .portal-grid-two { display:grid; grid-template-columns:minmax(0, 1.1fr) minmax(300px, 0.9fr); gap:18px; }
    .portal-panel {
        padding:22px; border-radius:24px; background:#ffffff; border:1px solid rgba(15,118,110,0.08); box-shadow:0 16px 34px rgba(15,76,92,0.08);
    }
    .portal-panel-header { display:flex; justify-content:space-between; align-items:flex-start; gap:14px; flex-wrap:wrap; margin-bottom:16px; }
    .portal-panel-title { margin:0; font-size:1.14rem; color:#17313b; }
    .portal-panel-text { margin:6px 0 0; color:#607b86; line-height:1.65; font-size:0.95rem; }
    .portal-progress-list { display:grid; gap:10px; }
    .portal-progress-step {
        display:grid; grid-template-columns:auto minmax(0, 1fr); gap:12px; padding:14px 16px; border-radius:18px;
        border:1px solid rgba(15,118,110,0.09); background:#ffffff;
    }
    .portal-progress-step.complete { background:#f0fdf4; }
    .portal-progress-step.current { background:#fff7ed; }
    .portal-progress-icon {
        width:28px; height:28px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center;
        background:#eaf7f6; color:#0f766e; font-size:0.82rem; font-weight:800; flex:0 0 28px;
    }
    .portal-progress-step.current .portal-progress-icon { background:#ffedd5; color:#c2410c; }
    .portal-progress-step.complete .portal-progress-icon { background:#dcfce7; color:#15803d; }
    .portal-detail-grid, .portal-action-grid, .portal-doc-groups, .portal-billing-grid, .portal-team-grid {
        display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:14px;
    }
    .portal-tile, .portal-action-card, .portal-document-card, .portal-update-card {
        padding:16px 18px; border-radius:18px; background:linear-gradient(180deg, #ffffff 0%, #f8fbfc 100%); border:1px solid rgba(15,118,110,0.08);
    }
    .portal-tile strong, .portal-action-card strong, .portal-document-card strong, .portal-update-card strong { display:block; color:#17313b; margin-bottom:8px; }
    .portal-muted { color:#62748a; line-height:1.6; }
    .portal-chip {
        display:inline-flex; align-items:center; gap:8px; padding:6px 10px; border-radius:999px; background:#eef8fa; color:#0d7987; font-weight:700; font-size:0.82rem;
    }
    .portal-chip.warning { background:#fff7ed; color:#c2410c; }
    .portal-chip.good { background:#f0fdf4; color:#15803d; }
    .portal-actions, .portal-link-row { display:flex; gap:8px; flex-wrap:wrap; margin-top:12px; }
    .portal-empty { padding:18px; border-radius:18px; background:#f8fbfc; border:1px dashed rgba(15,118,110,0.18); color:#607b86; }
    .portal-stack { display:grid; gap:12px; }
    .portal-update-card { display:grid; gap:8px; }
    @media (max-width: 980px) {
        .portal-grid-two, .portal-next-action { grid-template-columns:1fr; }
    }
    @media (max-width: 640px) {
        .portal-service-hero, .portal-panel { padding:20px; }
        .portal-actions, .portal-link-row { display:grid; grid-template-columns:1fr; }
        .portal-actions .button, .portal-link-row .button { width:100%; justify-content:center; }
    }
</style>

<section class="portal-service-shell">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <section class="portal-service-hero">
        <div class="portal-service-hero-top">
            <div>
                <div class="portal-service-kicker">Service Tracking</div>
                <h2><?= e($order['service_type_name']) ?></h2>
                <p class="portal-service-subtitle">
                    Reference <?= e($order['so_no']) ?>. This workspace gives you a clear view of service progress, pending information, and recent updates without exposing internal processing details.
                </p>
            </div>
            <div style="display:grid;gap:10px;justify-items:end;">
                <span class="portal-status-badge"><?= e($statusLabel) ?></span>
                <a href="<?= e(url('/client-portal/documents')) ?>" class="button button-secondary">Open Document Centre</a>
            </div>
        </div>

        <div class="portal-service-meta">
            <div class="portal-service-card">
                <div class="portal-service-label">Reference Number</div>
                <div class="portal-service-value"><?= e($order['so_no']) ?></div>
            </div>
            <div class="portal-service-card">
                <div class="portal-service-label">Service Type</div>
                <div class="portal-service-value"><?= e($order['service_type_name']) ?></div>
            </div>
            <div class="portal-service-card">
                <div class="portal-service-label">Progress</div>
                <div class="portal-service-value"><?= e((string) $progressPercent) ?>%</div>
            </div>
            <div class="portal-service-card">
                <div class="portal-service-label">Expected Completion</div>
                <div class="portal-service-value"><?= e($expectedCompletion !== '' ? $expectedCompletion : '-') ?></div>
            </div>
        </div>

        <div class="portal-next-action">
            <div>
                <div class="portal-next-label">Next Action Required</div>
                <div class="portal-next-title"><?= e($statusLabel === 'Awaiting Information' ? 'Please review and complete pending information requests' : 'No immediate client action is blocking progress right now') ?></div>
                <div class="portal-next-text">
                    <?= e($statusLabel === 'Awaiting Information'
                        ? 'Check the pending-from-you section below for documents, clarifications, or confirmations still needed from your side.'
                        : 'Our team is currently progressing the service. Review recent updates below for the latest movement on your case.') ?>
                </div>
            </div>
            <a href="#pending-client" class="button"><?= e($statusLabel === 'Awaiting Information' ? 'Review Pending Items' : 'View Recent Updates') ?></a>
        </div>
    </section>

    <section class="portal-grid-two">
        <div class="portal-panel">
            <div class="portal-panel-header">
                <div>
                    <h3 class="portal-panel-title">Progress Tracker</h3>
                    <p class="portal-panel-text">A simplified service journey that shows where your service stands right now.</p>
                </div>
            </div>

            <?php if (!$hasMilestoneTracking): ?>
                <div class="portal-empty" style="margin-bottom:14px;">
                    Detailed progress updates are not available for this service yet. Your current status and recent updates are still shown below.
                </div>
            <?php endif; ?>

            <div class="portal-progress-list">
                <?php foreach ($genericSteps as $index => $step): ?>
                    <?php
                    $stepClass = $index < $clientStepIndex ? ' complete' : ($index === $clientStepIndex ? ' current' : '');
                    $stepIcon = $index < $clientStepIndex ? 'OK' : ($index === $clientStepIndex ? 'NOW' : (string) ($index + 1));
                    ?>
                    <div class="portal-progress-step<?= $stepClass ?>">
                        <span class="portal-progress-icon"><?= e($stepIcon) ?></span>
                        <div>
                            <strong><?= e($step['label']) ?></strong>
                            <div class="portal-muted"><?= e($step['description']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="portal-panel">
            <div class="portal-panel-header">
                <div>
                    <h3 class="portal-panel-title">Status Details</h3>
                    <p class="portal-panel-text">What the current stage means and what is expected next.</p>
                </div>
            </div>

            <div class="portal-detail-grid">
                <div class="portal-tile">
                    <strong>Current Stage</strong>
                    <div class="portal-muted"><?= e($statusLabel) ?></div>
                </div>
                <div class="portal-tile">
                    <strong>What This Means</strong>
                    <div class="portal-muted"><?= e($statusMeaning) ?></div>
                </div>
                <div class="portal-tile">
                    <strong>Expected Next Step</strong>
                    <div class="portal-muted"><?= e($nextStepLabel) ?></div>
                </div>
                <div class="portal-tile">
                    <strong>Expected Completion</strong>
                    <div class="portal-muted"><?= e($expectedCompletion !== '' ? $expectedCompletion : '-') ?></div>
                </div>
            </div>
        </div>
    </section>

    <section class="portal-grid-two" id="pending-client">
        <div class="portal-panel">
            <div class="portal-panel-header">
                <div>
                    <h3 class="portal-panel-title">Pending From You</h3>
                    <p class="portal-panel-text">Action cards for information, clarifications, or confirmations still required from your side.</p>
                </div>
            </div>

            <div class="portal-action-grid">
                <div class="portal-action-card">
                    <strong>Required Documents</strong>
                    <?php if ($requiredDocumentLabels === []): ?>
                        <div class="portal-muted">No document request is currently visible in this workspace.</div>
                    <?php else: ?>
                        <div class="portal-stack">
                            <?php foreach ($requiredDocumentLabels as $item): ?>
                                <div class="portal-muted"><?= e($item) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="portal-action-card">
                    <strong>Pending Clarifications</strong>
                    <?php if ($pendingClarifications === []): ?>
                        <div class="portal-muted">No clarification request is currently open.</div>
                    <?php else: ?>
                        <div class="portal-stack">
                            <?php foreach ($pendingClarifications as $item): ?>
                                <div class="portal-muted"><?= e($item) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="portal-action-card">
                    <strong>Pending Approvals</strong>
                    <?php if ($pendingApprovals === []): ?>
                        <div class="portal-muted">No confirmation or approval is waiting from you right now.</div>
                    <?php else: ?>
                        <div class="portal-stack">
                            <?php foreach ($pendingApprovals as $item): ?>
                                <div class="portal-muted"><?= e($item) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($canUploadDocuments): ?>
                <div class="portal-panel" style="padding:0;border:none;box-shadow:none;background:transparent;margin-top:18px;">
                    <div class="portal-panel-header">
                        <div>
                            <h3 class="portal-panel-title">Upload Additional Documents</h3>
                            <p class="portal-panel-text">Share supporting files directly against this service.</p>
                        </div>
                    </div>
                    <form method="post" enctype="multipart/form-data" action="<?= e(url('/service-orders/documents')) ?>" style="display:grid;gap:10px;">
                        <?= \App\Core\Csrf::inputField() ?>
                        <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                        <select name="document_category">
                            <option value="SERVICE_ORDER_DOC">Service Document</option>
                            <option value="COMPLIANCE_PROOF">Supporting Proof</option>
                            <option value="WORKING_PAPER">Working Paper</option>
                        </select>
                        <input type="file" name="documents[]" multiple required>
                        <button type="submit" class="button">Upload Documents</button>
                    </form>
                    <div class="portal-link-row" style="margin-top:12px;">
                        <a href="<?= e(url('/client-portal/support')) ?>" class="button button-secondary">Need Help?</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="portal-empty" style="margin-top:18px;">If our team has requested additional files, please share them through your assigned service contact or your original service request channel.</div>
            <?php endif; ?>
        </div>

        <div class="portal-panel">
            <div class="portal-panel-header">
                <div>
                    <h3 class="portal-panel-title">Team and Billing</h3>
                    <p class="portal-panel-text">The people and billing summary relevant to this service.</p>
                </div>
            </div>

            <div class="portal-team-grid">
                <div class="portal-tile">
                    <strong>Handled By</strong>
                    <div class="portal-muted">E Tax Advisors Team</div>
                </div>
                <div class="portal-tile">
                    <strong>Relationship Manager</strong>
                    <div class="portal-muted"><?= e($relationshipManager) ?></div>
                </div>
                <div class="portal-tile">
                    <strong>Invoice Status</strong>
                    <div class="portal-muted"><?= e($invoiceStatus) ?></div>
                </div>
                <div class="portal-tile">
                    <strong>Outstanding Amount</strong>
                    <div class="portal-muted">INR <?= e(number_format($outstandingAmount, 2)) ?></div>
                </div>
                <div class="portal-tile">
                    <strong>Receipt Availability</strong>
                    <div class="portal-muted"><?= $receiptAvailable ? 'Receipt available for download' : 'No receipt available yet' ?></div>
                </div>
            </div>

            <?php if ($invoices !== []): ?>
                <div class="portal-actions">
                    <a href="<?= e(url('/client-portal/account')) ?>" class="button button-secondary">Open Account &amp; Billing</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="portal-grid-two">
        <div class="portal-panel">
            <div class="portal-panel-header">
                <div>
                    <h3 class="portal-panel-title">Service Documents</h3>
                    <p class="portal-panel-text">Documents are grouped by what you uploaded, what our team shared, and what is still being requested.</p>
                </div>
                <a href="<?= e(url('/client-portal/documents')) ?>" class="button button-secondary">Document Centre</a>
            </div>

            <div class="portal-doc-groups">
                <div class="portal-document-card">
                    <strong>Uploaded By You</strong>
                    <?php if ($uploadedByYou === []): ?>
                        <div class="portal-muted">No client-uploaded documents are visible for this service yet.</div>
                    <?php else: ?>
                        <div class="portal-stack">
                            <?php foreach ($uploadedByYou as $document): ?>
                                <div>
                                    <div class="portal-muted"><strong><?= e($document['document_name']) ?></strong></div>
                                    <div class="portal-muted">Uploaded: <?= e($document['uploaded_at'] ?: '-') ?></div>
                                    <div class="portal-link-row">
                                        <a href="<?= e(url('/documents/show?id=' . $document['id'])) ?>" class="button button-secondary">View</a>
                                        <a href="<?= e(url('/documents/' . $document['id'] . '/download')) ?>" class="button button-secondary">Download</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="portal-document-card">
                    <strong>Generated For You</strong>
                    <?php if ($generatedForYou === []): ?>
                        <div class="portal-muted">No service documents have been generated for you yet.</div>
                    <?php else: ?>
                        <div class="portal-stack">
                            <?php foreach ($generatedForYou as $document): ?>
                                <div>
                                    <div class="portal-muted"><strong><?= e($document['document_name']) ?></strong></div>
                                    <div class="portal-muted">Shared: <?= e($document['uploaded_at'] ?: '-') ?></div>
                                    <div class="portal-link-row">
                                        <a href="<?= e(url('/documents/show?id=' . $document['id'])) ?>" class="button button-secondary">View</a>
                                        <a href="<?= e(url('/documents/' . $document['id'] . '/download')) ?>" class="button button-secondary">Download</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="portal-document-card">
                    <strong>Requested From You</strong>
                    <?php if ($requiredDocumentLabels === []): ?>
                        <div class="portal-muted">There is no visible pending document request at the moment.</div>
                    <?php else: ?>
                        <div class="portal-stack">
                            <?php foreach ($requiredDocumentLabels as $item): ?>
                                <div class="portal-muted"><?= e($item) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="portal-panel">
            <div class="portal-panel-header">
                <div>
                    <h3 class="portal-panel-title">Recent Updates</h3>
                    <p class="portal-panel-text">The latest visible service updates and status changes, newest first.</p>
                </div>
            </div>

            <?php if ($activityFeed === []): ?>
                <div class="portal-empty">No recent service updates are available yet.</div>
            <?php else: ?>
                <div class="portal-stack">
                    <?php foreach ($activityFeed as $item): ?>
                        <article class="portal-update-card">
                            <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;">
                                <strong><?= e($item['title']) ?></strong>
                                <span class="portal-chip"><?= e(label_case((string) $item['status'])) ?></span>
                            </div>
                            <div class="portal-muted"><?= e($item['date'] ?: '-') ?></div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</section>

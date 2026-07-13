<?php
$historyByStage = [];
foreach ($workflowHistory as $historyRow) {
    $historyByStage[(string) $historyRow['stage_code']] = $historyRow;
}

$milestoneByStage = [];
foreach ($workflowMilestones as $milestoneRow) {
    $milestoneByStage[(string) $milestoneRow['stage_code']] = $milestoneRow;
}

$closureByType = [];
foreach ($workflowClosures as $closureRow) {
    $closureByType[(string) $closureRow['closure_type']] = $closureRow;
}

$currentStageCode = (string) ($order['current_stage_code'] ?? '');
$currentStageIndex = 0;
foreach ($workflowStages as $stageIndex => $stage) {
    if ((string) $stage['stage_code'] === $currentStageCode) {
        $currentStageIndex = $stageIndex;
        break;
    }
}

$canManageExpenses = \App\Core\Auth::can('billing.disbursements.manage');
$canViewExpenseSection = \App\Core\Auth::canAny('billing.disbursements.manage', 'billing.view');
$canViewFinancialSnapshot = \App\Core\Auth::canAny('billing.view', 'billing.invoices.manage', 'billing.payments.manage')
    || \App\Core\Auth::canAll('workflow.close.procedural', 'workflow.close.accounting', 'workflow.close.final');
$canReopenWorkflow = \App\Core\Auth::can('workflow.reopen');
$canUploadDocuments = \App\Core\Auth::canAny('service_orders.create', 'workflow.advance');
$canAssignConsultant = \App\Core\Auth::can('consultants.assign');
$canOpenConsultantWorkspace = \App\Core\Auth::can('consultants.view');

$disbursements = $billing['disbursements'] ?? [];
$invoices = $billing['invoices'] ?? [];
$payments = $billing['payments'] ?? [];
$linkedDocuments = $linkedDocuments ?? [];
$consultantAssignments = $consultantAssignments ?? [];
$activityTimeline = $activityTimeline ?? [];

$disbursementTotal = array_reduce($disbursements, static fn (float $carry, array $row): float => $carry + (float) ($row['amount'] ?? 0), 0.0);
$recoverableTotal = array_reduce($disbursements, static fn (float $carry, array $row): float => $carry + ((int) ($row['is_recoverable'] ?? 0) === 1 ? (float) ($row['amount'] ?? 0) : 0.0), 0.0);
$invoiceTotal = array_reduce($invoices, static fn (float $carry, array $row): float => $carry + (float) ($row['net_payable'] ?? 0), 0.0);
$paymentTotal = array_reduce($payments, static fn (float $carry, array $row): float => $carry + ((string) ($row['status'] ?? '') === 'SUCCESS' ? (float) ($row['amount'] ?? 0) : 0.0), 0.0);
$outstandingTotal = max($invoiceTotal - $paymentTotal, 0);

$statusOptions = [
    'PENDING' => 'Pending',
    'DOCS_RECD' => 'Docs Recd',
    'QUERY_PENDING' => 'Query Pending',
    'QUERY_COMPLIED' => 'Query Complied',
    'DONE' => 'Done',
];

$stageNameMap = [];
foreach ($workflowStages as $stage) {
    $stageNameMap[(string) $stage['stage_code']] = (string) $stage['stage_name'];
}

$closureProgressSteps = 2;
$completedClosureSteps = 0;
if (!empty($order['accounting_closed_at'])) {
    $completedClosureSteps++;
}
if (!empty($order['final_closed_at'])) {
    $completedClosureSteps++;
}

$totalLifecycleSteps = max(count($workflowStages) + $closureProgressSteps, 1);
$completedLifecycleSteps = min($currentStageIndex, max(count($workflowStages) - 1, 0)) + $completedClosureSteps;
if (!empty($order['procedural_closed_at'])) {
    $completedLifecycleSteps = max($completedLifecycleSteps, max(count($workflowStages) - 1, 0));
}
$progressPercent = (int) round(($completedLifecycleSteps / $totalLifecycleSteps) * 100);

$pendingTasks = 0;
$openQueries = 0;
foreach ($workflowStages as $index => $stage) {
    $stageCode = (string) $stage['stage_code'];
    $tracker = $milestoneByStage[$stageCode] ?? null;
    $status = (string) ($tracker['tracking_status'] ?? '');

    if ($status === 'QUERY_PENDING') {
        $openQueries++;
    }

    if ($index >= $currentStageIndex && $stageCode !== 'PROCEDURALLY_CLOSED') {
        $pendingTasks++;
    }
}

if (empty($order['procedural_closed_at'])) {
    $pendingTasks++;
}
if (empty($order['accounting_closed_at'])) {
    $pendingTasks++;
}
if (empty($order['final_closed_at'])) {
    $pendingTasks++;
}

$currentStatusLabel = $stageNameMap[$currentStageCode] ?? str_replace('_', ' ', $currentStageCode);
if (!empty($order['final_closed_at'])) {
    $currentStatusLabel = 'Final Closure Completed';
} elseif (!empty($order['accounting_closed_at'])) {
    $currentStatusLabel = 'Accounting Closure Completed';
} elseif (!empty($order['procedural_closed_at'])) {
    $currentStatusLabel = 'Procedurally Closed';
}

$billingStatusLabel = 'No Billing Started';
if ($invoiceTotal > 0 && $outstandingTotal > 0 && $paymentTotal > 0) {
    $billingStatusLabel = 'Partially Collected';
} elseif ($invoiceTotal > 0 && $outstandingTotal <= 0) {
    $billingStatusLabel = 'Fully Collected';
} elseif ($invoiceTotal > 0) {
    $billingStatusLabel = 'Invoice Raised';
}

$assignedStaff = array_values(array_filter([
    $order['assigned_crm_name'] ?? null,
    $order['assistant_crm_name'] ?? null,
    $order['backend_name'] ?? null,
    $order['deo_name'] ?? null,
]));
$assignedStaffLabel = $assignedStaff === [] ? 'Unassigned' : implode(', ', $assignedStaff);
$latestConsultant = $consultantAssignments[0] ?? null;

$timelineEntries = [];
foreach ($activityTimeline as $activity) {
    $timelineEntries[] = [
        'occurred_at' => (string) ($activity['created_at'] ?? ''),
        'title' => (string) ($activity['action_code'] ?? 'ACTIVITY'),
        'user_name' => (string) ($activity['user_name'] ?? '-'),
        'remarks' => (string) ($activity['description'] ?? ''),
        'tag' => (string) ($activity['module_code'] ?? 'SYSTEM'),
    ];
}
foreach ($workflowHistory as $history) {
    $timelineEntries[] = [
        'occurred_at' => (string) ($history['entered_at'] ?? ''),
        'title' => 'Stage Entered: ' . (string) ($history['stage_name'] ?? $history['stage_code']),
        'user_name' => (string) ($history['entered_by_name'] ?? '-'),
        'remarks' => (string) ($history['remarks'] ?? ''),
        'tag' => 'WORKFLOW',
    ];
}
foreach ($workflowClosures as $closure) {
    if (empty($closure['closure_at'])) {
        continue;
    }

    $timelineEntries[] = [
        'occurred_at' => (string) $closure['closure_at'],
        'title' => (string) $closure['closure_type'] . ' Closure ' . (string) $closure['closure_status'],
        'user_name' => (string) ($closure['closed_by_name'] ?? '-'),
        'remarks' => (string) ($closure['notes'] ?? $closure['block_reason'] ?? ''),
        'tag' => 'CLOSURE',
    ];
}

usort($timelineEntries, static function (array $left, array $right): int {
    return strcmp((string) ($right['occurred_at'] ?? ''), (string) ($left['occurred_at'] ?? ''));
});

$recentDocuments = [];
foreach ($linkedDocuments as $document) {
    if (count($recentDocuments) >= 3) {
        break;
    }
    $recentDocuments[] = $document;
}

$recentDocumentCount = 0;
foreach ($linkedDocuments as $document) {
    $uploadedAt = strtotime((string) ($document['uploaded_at'] ?? ''));
    if ($uploadedAt !== false && $uploadedAt >= strtotime('-7 days')) {
        $recentDocumentCount++;
    }
}

$documentExpectationLabels = [
    'WORKING_PAPER' => 'Working Papers',
    'ACKNOWLEDGEMENT' => 'Acknowledgement / ARN',
    'COMPLIANCE_PROOF' => 'Compliance Proof',
];

$requiredDocumentKeys = ['WORKING_PAPER', 'COMPLIANCE_PROOF'];
if (!empty($workflowRules['can_capture_ack']) || $currentStageIndex >= max(count($workflowStages) - 2, 0)) {
    $requiredDocumentKeys[] = 'ACKNOWLEDGEMENT';
}

$uploadedDocumentCategories = [];
foreach ($linkedDocuments as $document) {
    $category = (string) ($document['document_category'] ?? '');
    if ($category === '') {
        continue;
    }
    $uploadedDocumentCategories[$category] = true;
}

$requiredDocumentLabels = [];
$missingDocumentLabels = [];
foreach ($requiredDocumentKeys as $key) {
    $label = $documentExpectationLabels[$key] ?? $key;
    $requiredDocumentLabels[] = $label;
    if (!isset($uploadedDocumentCategories[$key])) {
        $missingDocumentLabels[] = $label;
    }
}

$nextActionLabel = 'Advance the active workflow milestone';
if ($openQueries > 0) {
    $nextActionLabel = 'Resolve pending queries and update milestone remarks';
} elseif ($linkedDocuments === [] && $canUploadDocuments) {
    $nextActionLabel = 'Upload working papers or supporting documents';
} elseif ($invoiceTotal <= 0 && \App\Core\Auth::can('billing.view')) {
    $nextActionLabel = 'Generate the first invoice for this service order';
} elseif (!empty($workflowRules['can_final_close'])) {
    $nextActionLabel = 'Final closure is ready for submission';
} 

$attentionItems = [];
if ($openQueries > 0) {
    $attentionItems[] = [
        'label' => 'Workflow Pending',
        'message' => $openQueries . ' milestone query item(s) require response.',
        'tone' => 'workflow',
    ];
}
if ($linkedDocuments === []) {
    $attentionItems[] = [
        'label' => 'Documents Required',
        'message' => 'No service-order documents are uploaded yet.',
        'tone' => 'documents',
    ];
}
if ($outstandingTotal > 0) {
    $attentionItems[] = [
        'label' => 'Billing Blocker',
        'message' => 'Outstanding billing of INR ' . number_format($outstandingTotal, 2) . ' remains open.',
        'tone' => 'billing',
    ];
}
if (empty($order['procedural_closed_at'])) {
    $attentionItems[] = [
        'label' => 'Closure Pending',
        'message' => 'Procedural closure is still pending.',
        'tone' => 'closure',
    ];
}
if ((int) ($order['is_consultant_payment_pending'] ?? 0) === 1) {
    $attentionItems[] = [
        'label' => 'Finance Dependency',
        'message' => 'Consultant payment is pending and will block final closure.',
        'tone' => 'billing',
    ];
}
if ($attentionItems === []) {
    $attentionItems[] = [
        'label' => 'Clear',
        'message' => 'No immediate blockers are visible. Continue with the next workflow action.',
        'tone' => 'clear',
    ];
}

$documentWorkspaceStatus = $linkedDocuments === []
    ? 'Missing workspace documents'
    : ($recentDocumentCount > 0 ? 'Recently updated' : 'Repository available');

$nextActionSupportingText = 'Current owner: ' . $assignedStaffLabel . ' | Billing: ' . $billingStatusLabel;

$billingWorkspaceTone = $outstandingTotal > 0 ? 'warning' : ($invoiceTotal > 0 ? 'good' : 'neutral');

$closureCards = [
    [
        'type' => 'PROCEDURAL',
        'title' => 'Procedural Closure',
        'completed_at' => $order['procedural_closed_at'] ?? null,
        'allowed' => \App\Core\Auth::can('workflow.close.procedural'),
        'can_submit' => !empty($workflowRules['can_procedural_close']),
        'path' => '/workflow/close-procedural',
        'button' => 'Complete Procedural Closure',
    ],
    [
        'type' => 'ACCOUNTING',
        'title' => 'Accounting Closure',
        'completed_at' => $order['accounting_closed_at'] ?? null,
        'allowed' => \App\Core\Auth::can('workflow.close.accounting'),
        'can_submit' => !empty($workflowRules['can_accounting_close']),
        'path' => '/workflow/close-accounting',
        'button' => 'Complete Accounting Closure',
    ],
    [
        'type' => 'FINAL',
        'title' => 'Final Closure',
        'completed_at' => $order['final_closed_at'] ?? null,
        'allowed' => \App\Core\Auth::can('workflow.close.final'),
        'can_submit' => !empty($workflowRules['can_final_close']),
        'path' => '/workflow/close-final',
        'button' => 'Complete Final Closure',
    ],
];
?>
<style>
    .so-workspace {
        display: grid;
        gap: 20px;
    }
    .so-hero {
        padding: 26px 28px;
        border-radius: 28px;
        background: linear-gradient(145deg, #0f4c5c 0%, #0f766e 54%, #ea8a2f 100%);
        color: #f8fbfc;
        box-shadow: 0 22px 40px rgba(15, 76, 92, 0.18);
        display: grid;
        gap: 22px;
    }
    .so-hero-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        flex-wrap: wrap;
    }
    .so-hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.78rem;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: rgba(245, 248, 250, 0.84);
        font-weight: 800;
    }
    .so-hero h1 {
        margin: 10px 0 6px;
        font-size: clamp(1.9rem, 3vw, 2.6rem);
        line-height: 1.05;
        color: #ffffff;
    }
    .so-hero-subtitle {
        margin: 0;
        max-width: 820px;
        color: rgba(244, 249, 252, 0.86);
        font-size: 1rem;
        line-height: 1.65;
    }
    .so-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.18);
        color: #ffffff;
        font-weight: 800;
        font-size: 0.85rem;
    }
    .so-badge::before {
        content: "";
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #f8fafc;
    }
    .so-hero-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(165px, 1fr));
        gap: 14px;
    }
    .so-hero-next {
        display: grid;
        gap: 8px;
        padding: 16px 18px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.16);
    }
    .so-hero-next-label {
        font-size: 0.74rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: rgba(243, 247, 250, 0.78);
        font-weight: 800;
    }
    .so-hero-next-title {
        color: #ffffff;
        font-size: 1.02rem;
        line-height: 1.5;
        font-weight: 800;
    }
    .so-hero-next-text {
        color: rgba(244, 249, 252, 0.84);
        line-height: 1.55;
        font-size: 0.92rem;
    }
    .so-hero-stat {
        padding: 16px 18px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.12);
    }
    .so-hero-stat-label {
        font-size: 0.74rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: rgba(243, 247, 250, 0.75);
        font-weight: 800;
    }
    .so-hero-stat-value {
        margin-top: 7px;
        font-size: 1.02rem;
        line-height: 1.45;
        color: #ffffff;
        font-weight: 700;
    }
    .so-progressbar {
        margin-top: 10px;
        height: 10px;
        border-radius: 999px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.18);
    }
    .so-progressbar > span {
        display: block;
        height: 100%;
        background: linear-gradient(90deg, #fde7c5 0%, #ffffff 100%);
        border-radius: inherit;
    }
    .so-attention-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(280px, 0.85fr);
        gap: 18px;
    }
    .so-panel {
        padding: 22px;
        border-radius: 24px;
        background: #ffffff;
        border: 1px solid rgba(15, 118, 110, 0.09);
        box-shadow: 0 16px 34px rgba(15, 76, 92, 0.08);
    }
    .so-panel-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .so-panel-title {
        margin: 0;
        color: #17313b;
        font-size: 1.1rem;
    }
    .so-panel-text {
        margin: 6px 0 0;
        color: #607b86;
        line-height: 1.65;
        font-size: 0.95rem;
    }
    .so-attention-list {
        display: grid;
        gap: 10px;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .so-attention-item {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 12px 14px;
        border-radius: 16px;
        background: #f8fbfc;
        border: 1px solid rgba(15, 118, 110, 0.09);
        color: #36505d;
    }
    .so-attention-copy {
        display: grid;
        gap: 4px;
    }
    .so-attention-label {
        color: #17313b;
        font-size: 0.82rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        font-weight: 800;
    }
    .so-attention-item::before {
        content: "!";
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #fff2e7;
        color: #ea580c;
        font-size: 0.76rem;
        font-weight: 800;
        flex: 0 0 22px;
        margin-top: 1px;
    }
    .so-next-step {
        display: grid;
        gap: 12px;
    }
    .so-next-step-card {
        padding: 16px 18px;
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #f4fbfb 100%);
        border: 1px solid rgba(15, 118, 110, 0.1);
    }
    .so-next-step-card.is-primary {
        background: linear-gradient(180deg, #f4fbfb 0%, #ebf8f7 100%);
        border-color: rgba(15, 118, 110, 0.18);
        box-shadow: inset 0 0 0 1px rgba(15, 118, 110, 0.05);
    }
    .so-next-step-label {
        font-size: 0.74rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #6b7d87;
        font-weight: 800;
    }
    .so-next-step-value {
        margin-top: 8px;
        color: #15303a;
        font-size: 1rem;
        line-height: 1.55;
        font-weight: 700;
    }
    .so-toolbar {
        position: sticky;
        top: 118px;
        z-index: 30;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        padding: 14px 18px;
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(15, 118, 110, 0.1);
        box-shadow: 0 16px 30px rgba(15, 76, 92, 0.08);
    }
    .so-toolbar-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .so-toolbar-actions .button {
        white-space: nowrap;
    }
    .so-toolbar-note {
        font-size: 0.92rem;
        font-weight: 600;
        color: #607b86;
    }
    .so-health-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }
    .so-health-card {
        display: grid;
        gap: 12px;
        padding: 20px 22px;
        border-radius: 22px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbfc 100%);
        border: 1px solid rgba(15, 118, 110, 0.09);
        box-shadow: 0 16px 28px rgba(15, 76, 92, 0.06);
    }
    .so-health-head {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: flex-start;
    }
    .so-health-label {
        font-size: 0.76rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #71838d;
        font-weight: 800;
    }
    .so-health-value {
        margin-top: 8px;
        font-size: 1.9rem;
        line-height: 1;
        color: #0f172a;
        font-weight: 800;
    }
    .so-health-detail {
        color: #607b86;
        line-height: 1.65;
        font-size: 0.94rem;
    }
    .so-card-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #0f766e;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.92rem;
        padding: 8px 12px;
        border-radius: 999px;
        background: #eef8fa;
        width: fit-content;
    }
    .so-card-link:hover {
        text-decoration: underline;
    }
    .so-tabs {
        display: grid;
        gap: 16px;
    }
    .so-tab-nav {
        position: sticky;
        top: 200px;
        z-index: 25;
        display: flex;
        gap: 10px;
        flex-wrap: nowrap;
        overflow-x: auto;
        scrollbar-width: none;
        padding: 12px 14px;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.94);
        border: 1px solid rgba(15, 118, 110, 0.09);
        box-shadow: 0 14px 26px rgba(15, 76, 92, 0.07);
    }
    .so-tab-nav::-webkit-scrollbar {
        display: none;
    }
    .so-tab-button {
        border: 1px solid rgba(15, 118, 110, 0.1);
        border-radius: 999px;
        background: #f7fbfc;
        color: #21404a;
        padding: 10px 16px;
        font: inherit;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.18s ease;
        flex: 0 0 auto;
    }
    .so-tab-button:hover,
    .so-tab-button.active {
        background: #eaf7f6;
        border-color: rgba(15, 118, 110, 0.2);
        color: #0f172a;
    }
    .so-tab-panel {
        display: none;
        gap: 18px;
    }
    .so-tab-panel.active {
        display: grid;
    }
    .so-split {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(300px, 0.9fr);
        gap: 18px;
    }
    .so-overview-grid,
    .so-summary-grid,
    .so-doc-summary,
    .so-billing-summary,
    .so-closure-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 14px;
    }
    .so-info-tile,
    .so-summary-tile,
    .so-doc-tile,
    .so-billing-tile {
        padding: 16px 18px;
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #f7fbfc 100%);
        border: 1px solid rgba(15, 118, 110, 0.08);
    }
    .so-info-tile strong,
    .so-summary-tile strong,
    .so-doc-tile strong,
    .so-billing-tile strong {
        display: block;
        color: #15303a;
        margin-bottom: 8px;
    }
    .so-info-tile div,
    .so-summary-tile div,
    .so-doc-tile div,
    .so-billing-tile div {
        color: #607b86;
        line-height: 1.6;
    }
    .so-empty {
        padding: 18px;
        border-radius: 18px;
        background: #f8fbfc;
        border: 1px dashed rgba(15, 118, 110, 0.18);
        color: #607b86;
    }
    .so-note {
        color: #607b86;
        font-size: 0.93rem;
        line-height: 1.65;
    }
    .so-stage-layout {
        display: grid;
        grid-template-columns: minmax(280px, 330px) minmax(0, 1fr);
        gap: 18px;
    }
    .so-stage-list {
        display: grid;
        gap: 10px;
    }
    .so-stage-button {
        width: 100%;
        text-align: left;
        padding: 14px 16px;
        border-radius: 18px;
        border: 1px solid rgba(15, 118, 110, 0.09);
        background: #ffffff;
        cursor: pointer;
        transition: all 0.18s ease;
    }
    .so-stage-button:hover,
    .so-stage-button.active {
        transform: translateY(-1px);
        box-shadow: 0 12px 24px rgba(15, 76, 92, 0.08);
        border-color: rgba(15, 118, 110, 0.18);
    }
    .so-stage-button.current {
        background: #fff7ed;
    }
    .so-stage-button.completed {
        background: #f0fdf4;
    }
    .so-stage-head {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .so-stage-icon {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.82rem;
        font-weight: 800;
        background: #eaf7f6;
        color: #0f766e;
        flex: 0 0 28px;
    }
    .so-stage-button.current .so-stage-icon {
        background: #ffedd5;
        color: #ea580c;
    }
    .so-stage-button.completed .so-stage-icon {
        background: #dcfce7;
        color: #15803d;
    }
    .so-stage-name {
        color: #15303a;
        font-weight: 700;
    }
    .so-stage-code {
        color: #71838d;
        font-size: 0.83rem;
        margin-top: 4px;
    }
    .so-stage-meta {
        margin-top: 10px;
        display: flex;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        color: #71838d;
        font-size: 0.84rem;
    }
    .so-stage-panel {
        display: none;
    }
    .so-stage-panel.active {
        display: block;
    }
    .so-stage-shell {
        border-radius: 24px;
        background: #ffffff;
        border: 1px solid rgba(15, 118, 110, 0.09);
        box-shadow: 0 16px 28px rgba(15, 76, 92, 0.06);
        padding: 22px;
        display: grid;
        gap: 18px;
    }
    .so-stage-detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
    }
    .so-stage-detail {
        padding: 14px 16px;
        border-radius: 16px;
        background: #f8fbfc;
        border: 1px solid rgba(15, 118, 110, 0.08);
    }
    .so-stage-detail label {
        display: block;
        margin-bottom: 6px;
        color: #667b87;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        font-weight: 800;
    }
    .so-stage-detail strong,
    .so-stage-detail div {
        color: #15303a;
        line-height: 1.55;
    }
    .so-stage-actions {
        display: grid;
        gap: 12px;
    }
    .so-stage-actions form,
    .so-closure-form,
    .so-upload-form,
    .so-followup-form,
    .so-consultant-form {
        display: grid;
        gap: 10px;
    }
    .so-stage-actions textarea,
    .so-stage-actions input,
    .so-stage-actions select,
    .so-closure-form input,
    .so-upload-form select,
    .so-consultant-form select,
    .so-upload-form input,
    .so-consultant-form textarea,
    .so-followup-form input,
    .so-upload-form textarea {
        padding: 12px 13px;
        border-radius: 14px;
    }
    .so-doc-grid,
    .so-payment-grid,
    .so-timeline {
        display: grid;
        gap: 12px;
    }
    .so-doc-card,
    .so-payment-card,
    .so-team-card,
    .so-closure-card {
        padding: 16px 18px;
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbfc 100%);
        border: 1px solid rgba(15, 118, 110, 0.08);
    }
    .so-doc-actions,
    .so-inline-actions,
    .so-closure-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 12px;
    }
    .so-billing-spotlight {
        display: grid;
        gap: 14px;
    }
    .so-billing-panel {
        padding: 18px;
        border-radius: 20px;
        border: 1px solid rgba(15, 118, 110, 0.08);
        background: #ffffff;
    }
    .so-billing-panel.warning {
        background: #fff7ed;
        border-color: rgba(234, 88, 12, 0.15);
    }
    .so-billing-panel.good {
        background: #f0fdf4;
        border-color: rgba(22, 163, 74, 0.14);
    }
    .so-timeline {
        position: relative;
        padding-left: 18px;
    }
    .so-timeline::before {
        content: "";
        position: absolute;
        left: 6px;
        top: 6px;
        bottom: 6px;
        width: 2px;
        background: #d7e8ea;
    }
    .so-timeline-item {
        position: relative;
        display: grid;
        gap: 8px;
        padding: 16px 18px 16px 20px;
        border-radius: 18px;
        background: #ffffff;
        border: 1px solid rgba(15, 118, 110, 0.08);
        box-shadow: 0 12px 24px rgba(15, 76, 92, 0.04);
    }
    .so-timeline-item::before {
        content: "";
        position: absolute;
        left: -17px;
        top: 22px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #0f766e;
        border: 3px solid #e8f7f6;
    }
    .so-timeline-top {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
    }
    .so-timeline-tag {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #eef8fa;
        color: #0d7987;
        font-weight: 700;
        font-size: 0.82rem;
    }
    .so-timeline-meta {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
        color: #607b86;
        font-size: 0.92rem;
    }
    .so-closure-summary {
        margin-bottom: 18px;
    }
    .so-closure-card.status-complete {
        background: #f0fdf4;
        border-color: rgba(22, 163, 74, 0.16);
    }
    .so-closure-card.status-pending {
        background: #fff7ed;
        border-color: rgba(234, 88, 12, 0.15);
    }
    .so-closure-grid,
    .so-team-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 14px;
    }
    .so-table-wrap {
        overflow-x: auto;
    }
    @media (max-width: 1180px) {
        .so-health-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .so-attention-grid,
        .so-split,
        .so-stage-layout {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 920px) {
        .so-toolbar {
            top: 102px;
        }
        .so-tab-nav {
            top: 184px;
        }
    }
    @media (max-width: 680px) {
        .so-health-grid {
            grid-template-columns: 1fr;
        }
        .so-toolbar,
        .so-tab-nav {
            position: static;
        }
        .so-toolbar {
            padding: 14px;
        }
        .so-toolbar-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
        }
        .so-toolbar-actions .button {
            width: 100%;
            justify-content: center;
            padding: 10px 12px;
        }
        .so-toolbar-note {
            width: 100%;
            font-size: 0.88rem;
        }
        .so-tab-nav {
            gap: 8px;
            padding: 10px 12px;
        }
        .so-tab-button {
            padding: 9px 14px;
        }
        .so-hero,
        .so-panel {
            padding: 20px;
        }
    }
</style>

<section class="so-workspace">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <section class="so-hero">
        <div class="so-hero-top">
            <div>
                <div class="so-hero-kicker">Service Order Workspace</div>
                <h2><?= e($order['so_no']) ?></h2>
                <p class="so-hero-subtitle">
                    <?= e($order['service_type_name']) ?> for <?= e($order['client_name']) ?>.
                    Current workflow stage is <?= e($currentStatusLabel) ?> with <?= e((string) $progressPercent) ?>% progress
                    and <?= e((string) $pendingTasks) ?> active item(s) still open.
                </p>
            </div>
            <div class="so-badge"><?= e($currentStatusLabel) ?></div>
        </div>

        <div class="so-hero-meta">
            <div class="so-hero-stat">
                <div class="so-hero-stat-label">Client</div>
                <div class="so-hero-stat-value"><?= e($order['client_name']) ?></div>
            </div>
            <div class="so-hero-stat">
                <div class="so-hero-stat-label">Progress</div>
                <div class="so-hero-stat-value"><?= e((string) $progressPercent) ?>%</div>
                <div class="so-progressbar"><span style="width:<?= e((string) $progressPercent) ?>%;"></span></div>
            </div>
            <div class="so-hero-stat">
                <div class="so-hero-stat-label">Due Date</div>
                <div class="so-hero-stat-value"><?= e($order['sla_due_at'] ?: '-') ?></div>
            </div>
            <div class="so-hero-stat">
                <div class="so-hero-stat-label">Assigned Staff</div>
                <div class="so-hero-stat-value"><?= e($assignedStaffLabel) ?></div>
            </div>
            <div class="so-hero-stat">
                <div class="so-hero-stat-label">Billing Status</div>
                <div class="so-hero-stat-value"><?= e($billingStatusLabel) ?></div>
            </div>
        </div>

        <div class="so-hero-next">
            <div class="so-hero-next-label">Next Action</div>
            <div class="so-hero-next-title"><?= e($nextActionLabel) ?></div>
            <div class="so-hero-next-text"><?= e($nextActionSupportingText) ?></div>
        </div>
    </section>

    <section class="so-attention-grid">
        <article class="so-panel">
            <div class="so-panel-header">
                <div>
                    <h3 class="so-panel-title">Attention Required</h3>
                    <p class="so-panel-text">The fastest way to understand what is blocking this assignment right now.</p>
                </div>
            </div>
            <ul class="so-attention-list">
                <?php foreach ($attentionItems as $item): ?>
                    <li class="so-attention-item">
                        <div class="so-attention-copy">
                            <div class="so-attention-label"><?= e($item['label']) ?></div>
                            <div><?= e($item['message']) ?></div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </article>

        <article class="so-panel">
            <div class="so-panel-header">
                <div>
                    <h3 class="so-panel-title">Next Action</h3>
                    <p class="so-panel-text">Operational summary for ownership, billing position, and closure readiness.</p>
                </div>
            </div>
            <div class="so-next-step">
                <div class="so-next-step-card is-primary">
                    <div class="so-next-step-label">Recommended Action</div>
                    <div class="so-next-step-value"><?= e($nextActionLabel) ?></div>
                </div>
                <div class="so-next-step-card">
                    <div class="so-next-step-label">Current Owner View</div>
                    <div class="so-next-step-value"><?= e($assignedStaffLabel) ?></div>
                </div>
                <div class="so-next-step-card">
                    <div class="so-next-step-label">Closure Readiness</div>
                    <div class="so-next-step-value"><?= !empty($workflowRules['can_final_close']) ? 'Final closure ready' : 'Further actions still required' ?></div>
                </div>
            </div>
        </article>
    </section>

    <div class="so-toolbar">
        <div class="so-toolbar-actions">
            <a href="#documents" class="button" data-so-tab-trigger="documents">Upload Document</a>
            <a href="#team" class="button button-secondary" data-so-tab-trigger="team">Assign Staff</a>
            <?php if (\App\Core\Auth::can('billing.view')): ?>
                <a href="<?= e(url('/billing/show?service_order_id=' . $order['id'])) ?>" class="button button-secondary">Generate Invoice</a>
            <?php else: ?>
                <a href="#billing" class="button button-secondary" data-so-tab-trigger="billing">Generate Invoice</a>
            <?php endif; ?>
            <a href="#overview" class="button button-secondary" data-so-tab-trigger="overview">Send Reminder</a>
            <a href="#workflow" class="button button-secondary" data-so-tab-trigger="workflow">Create Query</a>
            <a href="#closure" class="button button-secondary" data-so-tab-trigger="closure">Close Service Order</a>
        </div>
        <div class="so-toolbar-note">
            <?= e($order['company_name']) ?> | FY <?= e($order['financial_year_label']) ?> | <?= e($order['period_label'] ?: 'No period captured') ?>
        </div>
    </div>

    <section class="so-health-grid">
        <article class="so-health-card">
            <div class="so-health-head">
                <div>
                    <div class="so-health-label">Pending Tasks</div>
                    <div class="so-health-value"><?= e((string) $pendingTasks) ?></div>
                </div>
            </div>
            <div class="so-health-detail">Open workflow milestones and closure steps still pending in this service lifecycle.</div>
            <a href="#workflow" class="so-card-link" data-so-tab-trigger="workflow">Open workflow</a>
        </article>
        <article class="so-health-card">
            <div class="so-health-head">
                <div>
                    <div class="so-health-label">Open Queries</div>
                    <div class="so-health-value"><?= e((string) $openQueries) ?></div>
                </div>
            </div>
            <div class="so-health-detail">Milestones marked with query status or awaiting follow-up attention.</div>
            <a href="#workflow" class="so-card-link" data-so-tab-trigger="workflow">Resolve queries</a>
        </article>
        <article class="so-health-card">
            <div class="so-health-head">
                <div>
                    <div class="so-health-label">Documents</div>
                    <div class="so-health-value"><?= e((string) count($linkedDocuments)) ?></div>
                </div>
            </div>
            <div class="so-health-detail"><?= e($documentWorkspaceStatus) ?> with <?= e((string) $recentDocumentCount) ?> upload(s) in the last 7 days.</div>
            <a href="#documents" class="so-card-link" data-so-tab-trigger="documents">Open documents</a>
        </article>
        <article class="so-health-card">
            <div class="so-health-head">
                <div>
                    <div class="so-health-label">Billing Status</div>
                    <div class="so-health-value" style="font-size:1.28rem;line-height:1.15;"><?= e($billingStatusLabel) ?></div>
                </div>
            </div>
            <div class="so-health-detail">Invoices INR <?= e(number_format($invoiceTotal, 2)) ?> | Outstanding INR <?= e(number_format($outstandingTotal, 2)) ?></div>
            <a href="#billing" class="so-card-link" data-so-tab-trigger="billing">Open billing</a>
        </article>
    </section>

    <section class="so-tabs">
        <div class="so-tab-nav">
            <button type="button" class="so-tab-button active" data-so-tab="overview">Overview</button>
            <button type="button" class="so-tab-button" data-so-tab="workflow">Workflow</button>
            <button type="button" class="so-tab-button" data-so-tab="documents">Documents</button>
            <button type="button" class="so-tab-button" data-so-tab="billing">Billing</button>
            <button type="button" class="so-tab-button" data-so-tab="expenses">Expenses</button>
            <button type="button" class="so-tab-button" data-so-tab="team">Team</button>
            <button type="button" class="so-tab-button" data-so-tab="timeline">Timeline</button>
            <button type="button" class="so-tab-button" data-so-tab="closure">Closure</button>
        </div>

        <div class="so-tab-panel active" id="overview">
            <div class="so-split">
                <section class="so-panel">
                    <div class="so-panel-header">
                        <div>
                            <h3 class="so-panel-title">Executive Summary</h3>
                            <p class="so-panel-text">Core client, service, compliance, and operational context for this assignment.</p>
                        </div>
                    </div>
                    <div class="so-overview-grid">
                        <div class="so-info-tile">
                            <strong>Client Details</strong>
                            <div>Client: <?= e($order['client_name']) ?></div>
                            <div>PAN: <?= e($order['pan'] ?: '-') ?></div>
                            <div>TAN: <?= e($order['tan'] ?: '-') ?></div>
                        </div>
                        <div class="so-info-tile">
                            <strong>Service Details</strong>
                            <div>Service: <?= e($order['service_type_name']) ?></div>
                            <div>Priority: <?= e($order['priority_level'] ?: '-') ?></div>
                            <div>Company: <?= e($order['company_name']) ?></div>
                        </div>
                        <div class="so-info-tile">
                            <strong>Compliance Window</strong>
                            <div>Work Basis: <?= e($order['work_basis'] ?: '-') ?></div>
                            <div>Period: <?= e($order['period_label'] ?: '-') ?></div>
                            <div>Due Date: <?= e($order['sla_due_at'] ?: '-') ?></div>
                        </div>
                        <div class="so-info-tile">
                            <strong>ITR Context</strong>
                            <div>Case Nature: <?= e(!empty($order['itr_case_nature']) ? str_replace('_', ' ', (string) $order['itr_case_nature']) : '-') ?></div>
                            <div>Tax Audit: <?= e(!empty($order['itr_case_nature']) ? ((int) ($order['itr_tax_audit_applicable'] ?? 0) === 1 ? 'Applicable' : 'Not Applicable') : '-') ?></div>
                            <div>Assessment Year: <?= e($order['assessment_year'] ?: '-') ?></div>
                        </div>
                    </div>

                    <div class="so-panel" style="padding:0;border:none;box-shadow:none;background:transparent;margin-top:18px;">
                        <div class="so-panel-header">
                            <div>
                                <h3 class="so-panel-title">Pending Actions</h3>
                                <p class="so-panel-text">Immediate operational checklist pulled from the current case state.</p>
                            </div>
                        </div>
                        <div class="so-summary-grid">
                            <div class="so-summary-tile">
                                <strong>Current Stage</strong>
                                <div><?= e($currentStatusLabel) ?></div>
                            </div>
                            <div class="so-summary-tile">
                                <strong>Documents</strong>
                                <div><?= $linkedDocuments === [] ? 'Upload required' : 'Repository available' ?></div>
                            </div>
                            <div class="so-summary-tile">
                                <strong>Billing Position</strong>
                                <div><?= e($billingStatusLabel) ?></div>
                            </div>
                            <div class="so-summary-tile">
                                <strong>Closure Position</strong>
                                <div><?= !empty($order['final_closed_at']) ? 'Final closure completed' : 'Closure still open' ?></div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="so-panel">
                    <div class="so-panel-header">
                        <div>
                            <h3 class="so-panel-title">Reminder Center</h3>
                            <p class="so-panel-text">Follow-up obligations and due-date communications tied to this service order.</p>
                        </div>
                    </div>

                    <?php if ($workflowReminders === []): ?>
                        <div class="so-empty">No reminders are scheduled for this service order right now.</div>
                    <?php else: ?>
                        <div class="so-doc-grid">
                            <?php foreach ($workflowReminders as $reminder): ?>
                                <article class="so-doc-card">
                                    <strong><?= e(label_case((string) $reminder['reminder_type'])) ?></strong>
                                    <div class="so-note" style="margin-top:6px;">Day <?= e((string) $reminder['schedule_day_no']) ?> reminder | Status: <?= e(label_case((string) $reminder['status'])) ?></div>
                                    <div class="so-note">Due at: <?= e($reminder['due_at']) ?></div>
                                    <?php if (\App\Core\Auth::can('workflow.followup.log')): ?>
                                        <form method="post" action="<?= e(url('/workflow/follow-up')) ?>" class="so-followup-form">
                                            <?= \App\Core\Csrf::inputField() ?>
                                            <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                                            <input type="hidden" name="reminder_id" value="<?= e($reminder['id']) ?>">
                                            <input type="text" name="follow_up_note" placeholder="Log follow-up note">
                                            <button type="submit" class="button button-secondary">Log Follow-Up</button>
                                        </form>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>

        <div class="so-tab-panel" id="workflow">
            <section class="so-panel">
                <div class="so-panel-header">
                    <div>
                        <h3 class="so-panel-title">Workflow Tracker</h3>
                        <p class="so-panel-text">Select a milestone to inspect completion details, remarks, and allowed updates without opening every action at once.</p>
                    </div>
                </div>

                <div class="so-stage-layout">
                    <div class="so-stage-list">
                        <?php foreach ($workflowStages as $index => $stage): ?>
                            <?php
                            $stageCode = (string) $stage['stage_code'];
                            $tracker = $milestoneByStage[$stageCode] ?? null;
                            $isCurrent = $currentStageCode === $stageCode;
                            $isCompleted = $index < $currentStageIndex;
                            $status = (string) ($tracker['tracking_status'] ?? ($isCompleted ? 'DONE' : 'PENDING'));
                            $buttonClass = $isCurrent ? ' current' : ($isCompleted ? ' completed' : '');
                            $stageStateLabel = $isCurrent ? 'Current' : ($isCompleted ? 'Completed' : 'Upcoming');
                            $stageIcon = $isCompleted ? 'OK' : ($isCurrent ? 'NOW' : (string) ($index + 1));
                            ?>
                            <button
                                type="button"
                                class="so-stage-button<?= $buttonClass ?><?= $index === $currentStageIndex ? ' active' : '' ?>"
                                data-stage-panel="<?= e('stage-panel-' . $index) ?>"
                            >
                                <div class="so-stage-head">
                                    <span class="so-stage-icon"><?= e($stageIcon) ?></span>
                                    <div>
                                        <div class="so-stage-name"><?= e($stage['stage_name']) ?></div>
                                        <div class="so-stage-code"><?= e($stageCode) ?></div>
                                    </div>
                                </div>
                                <div class="so-stage-meta">
                                    <span><?= e($statusOptions[$status] ?? 'Pending') ?></span>
                                    <span><?= e($stageStateLabel) ?></span>
                                </div>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <div>
                        <?php foreach ($workflowStages as $index => $stage): ?>
                            <?php
                            $stageCode = (string) $stage['stage_code'];
                            $historyRow = $historyByStage[$stageCode] ?? null;
                            $milestoneRow = $milestoneByStage[$stageCode] ?? null;
                            $isCurrent = $currentStageCode === $stageCode;
                            $isCompleted = $index < $currentStageIndex;
                            $isPending = $index > $currentStageIndex;
                            $isEditable = $isCurrent || $isCompleted;
                            $selectedStatus = (string) ($milestoneRow['tracking_status'] ?? ($isCompleted ? 'DONE' : 'PENDING'));
                            if (!$isCurrent && $isCompleted) {
                                $selectedStatus = 'DONE';
                            }
                            if (!$isCurrent && $isPending) {
                                $selectedStatus = 'PENDING';
                            }

                            $remarksValue = (string) ($milestoneRow['remarks'] ?? ($historyRow['remarks'] ?? ''));
                            $completedOn = (string) ($milestoneRow['completed_at'] ?? '');
                            $completedBy = (string) ($milestoneRow['completed_by_name'] ?? '');

                            if ($completedOn === '' && $isCompleted && isset($workflowStages[$index + 1])) {
                                $nextStageCode = (string) $workflowStages[$index + 1]['stage_code'];
                                $nextHistory = $historyByStage[$nextStageCode] ?? null;
                                $completedOn = (string) ($nextHistory['entered_at'] ?? '');
                                $completedBy = (string) ($nextHistory['entered_by_name'] ?? '');
                            }

                            if ($stageCode === 'PROCEDURALLY_CLOSED' && $completedOn === '') {
                                $completedOn = (string) ($order['procedural_closed_at'] ?? '');
                            }

                            $formId = 'milestone-form-' . $index;
                            $statusColor = $isCurrent ? '#ea580c' : ($isCompleted ? '#15803d' : '#64748b');
                            ?>
                            <section class="so-stage-panel<?= $index === $currentStageIndex ? ' active' : '' ?>" id="<?= e('stage-panel-' . $index) ?>">
                                <div class="so-stage-shell">
                                    <div class="so-panel-header" style="margin-bottom:0;">
                                        <div>
                                            <h3 class="so-panel-title"><?= e($stage['stage_name']) ?></h3>
                                            <p class="so-panel-text"><?= e($stageCode) ?></p>
                                        </div>
                                        <span class="chip" style="border-color:<?= e($statusColor) ?>;color:<?= e($statusColor) ?>;">
                                            <?= e($statusOptions[$selectedStatus] ?? 'Pending') ?>
                                        </span>
                                    </div>

                                    <div class="so-stage-detail-grid">
                                        <div class="so-stage-detail">
                                            <label>Completed On</label>
                                            <strong><?= e($completedOn !== '' ? $completedOn : '-') ?></strong>
                                        </div>
                                        <div class="so-stage-detail">
                                            <label>Completed By</label>
                                            <strong><?= e($completedBy !== '' ? $completedBy : '-') ?></strong>
                                        </div>
                                        <div class="so-stage-detail">
                                            <label>Stage Position</label>
                                            <strong><?= $isCurrent ? 'Current' : ($isCompleted ? 'Completed' : 'Upcoming') ?></strong>
                                        </div>
                                        <div class="so-stage-detail">
                                            <label>Remarks</label>
                                            <div><?= e($remarksValue !== '' ? $remarksValue : '-') ?></div>
                                        </div>
                                    </div>

                                    <div class="so-stage-actions">
                                        <?php if ($isEditable): ?>
                                            <form id="<?= e($formId) ?>" method="post" action="<?= e(url('/workflow/milestone-update')) ?>">
                                                <?= \App\Core\Csrf::inputField() ?>
                                                <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                                                <input type="hidden" name="stage_code" value="<?= e($stageCode) ?>">
                                                <label>
                                                    <span class="so-note" style="display:block;margin-bottom:6px;">Status</span>
                                                    <select name="tracking_status">
                                                        <?php foreach ($statusOptions as $statusCode => $statusName): ?>
                                                            <?php if (!$isCurrent && $statusCode !== 'DONE') { continue; } ?>
                                                            <option value="<?= e($statusCode) ?>" <?= $selectedStatus === $statusCode ? 'selected' : '' ?>><?= e($statusName) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </label>
                                                <label>
                                                    <span class="so-note" style="display:block;margin-bottom:6px;">Remarks</span>
                                                    <textarea name="remarks" rows="4" placeholder="Enter milestone remarks"><?= e($remarksValue) ?></textarea>
                                                </label>
                                                <?php if ($isCurrent && !empty($workflowRules['can_record_payment'])): ?>
                                                    <input type="text" name="payment_reference_no" placeholder="Tax challan / payment reference">
                                                <?php endif; ?>
                                                <?php if ($isCurrent && !empty($workflowRules['can_capture_ack'])): ?>
                                                    <input type="text" name="acknowledgement_no" placeholder="<?= e($stageCode === 'FORM_3CB_FILED' ? '3CB acknowledgement number' : ((string) ($order['service_type_code'] ?? '') === 'ITR' ? 'ITR ARN / acknowledgement number' : 'Acknowledgement / ARN number')) ?>">
                                                <?php endif; ?>
                                                <button type="submit" class="button"><?= $isCurrent ? 'Save Milestone Update' : 'Save Stage Remarks' ?></button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($isCompleted && $canReopenWorkflow): ?>
                                            <form method="post" action="<?= e(url('/workflow/reopen')) ?>">
                                                <?= \App\Core\Csrf::inputField() ?>
                                                <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                                                <input type="hidden" name="stage_code" value="<?= e($stageCode) ?>">
                                                <input type="text" name="reopen_reason" placeholder="Reason to re-open this stage" required>
                                                <button type="submit" class="button button-secondary">Re-Open Stage</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </section>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </div>

        <div class="so-tab-panel" id="documents">
            <div class="so-split">
                <section class="so-panel">
                    <div class="so-panel-header">
                        <div>
                            <h3 class="so-panel-title">Document Workspace</h3>
                            <p class="so-panel-text">Review uploaded files, identify gaps, and work from a secure repository linked to this service order.</p>
                        </div>
                    </div>

                    <div class="so-doc-summary">
                        <div class="so-doc-tile">
                            <strong>Uploaded Documents</strong>
                            <div><?= e((string) count($linkedDocuments)) ?> file(s) linked to this workspace.</div>
                        </div>
                        <div class="so-doc-tile">
                            <strong>Required Documents</strong>
                            <div><?= e($requiredDocumentLabels !== [] ? implode(', ', $requiredDocumentLabels) : 'No explicit document expectations yet.') ?></div>
                        </div>
                        <div class="so-doc-tile">
                            <strong>Missing Documents</strong>
                            <div><?= e($missingDocumentLabels !== [] ? implode(', ', $missingDocumentLabels) : 'No current document gap detected.') ?></div>
                        </div>
                        <div class="so-doc-tile">
                            <strong>Recent Uploads</strong>
                            <div><?= e((string) $recentDocumentCount) ?> file(s) added in the last 7 days.</div>
                        </div>
                    </div>

                    <div class="so-panel" style="padding:0;border:none;box-shadow:none;background:transparent;margin-top:18px;">
                        <div class="so-panel-header">
                            <div>
                                <h3 class="so-panel-title">Uploaded Repository</h3>
                                <p class="so-panel-text">Open or download the latest documents without leaving the service workspace.</p>
                            </div>
                        </div>
                        <?php if ($linkedDocuments === []): ?>
                            <div class="so-empty">No service-order documents have been uploaded yet.</div>
                        <?php else: ?>
                            <div class="so-doc-grid">
                                <?php foreach ($linkedDocuments as $document): ?>
                                    <article class="so-doc-card">
                                        <strong><?= e($document['document_name']) ?></strong>
                                        <div class="so-note">Category: <?= e($document['document_category']) ?> | Version: V<?= e((string) ($document['current_version_no'] ?? 1)) ?></div>
                                        <div class="so-note">Uploaded: <?= e($document['uploaded_at'] ?: '-') ?> by <?= e($document['uploaded_by_name'] ?: '-') ?></div>
                                        <div class="so-doc-actions">
                                            <a href="<?= e(url('/documents/show?id=' . $document['id'])) ?>" class="button button-secondary">View</a>
                                            <a href="<?= e(url('/documents/' . $document['id'] . '/download')) ?>" class="button button-secondary">Download</a>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="so-panel" id="document-uploader">
                    <div class="so-panel-header">
                        <div>
                            <h3 class="so-panel-title">Upload and Recent Activity</h3>
                            <p class="so-panel-text">Add working papers, proofs, acknowledgements, and execution files from the same workspace.</p>
                        </div>
                    </div>

                    <?php if ($canUploadDocuments): ?>
                        <form method="post" enctype="multipart/form-data" action="<?= e(url('/service-orders/documents')) ?>" class="so-upload-form">
                            <?= \App\Core\Csrf::inputField() ?>
                            <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                            <select name="document_category">
                                <option value="SERVICE_ORDER_DOC">Service Order Document</option>
                                <option value="WORKING_PAPER">Working Paper</option>
                                <option value="ACKNOWLEDGEMENT">Acknowledgement</option>
                                <option value="COMPLIANCE_PROOF">Compliance Proof</option>
                            </select>
                            <input type="file" name="documents[]" accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx" multiple required>
                            <button type="submit" class="button">Upload to Workspace</button>
                        </form>
                    <?php else: ?>
                        <div class="so-empty">You can view linked documents here, but upload access is restricted by current permissions.</div>
                    <?php endif; ?>

                    <div class="so-panel" style="padding:0;border:none;box-shadow:none;background:transparent;margin-top:18px;">
                        <div class="so-panel-header">
                            <div>
                                <h3 class="so-panel-title">Recent Uploads</h3>
                                <p class="so-panel-text">Quick access to the newest document activity in this service order.</p>
                            </div>
                        </div>
                        <?php if ($recentDocuments === []): ?>
                            <div class="so-empty">No recent uploads are available yet.</div>
                        <?php else: ?>
                            <div class="so-doc-grid">
                                <?php foreach ($recentDocuments as $document): ?>
                                    <article class="so-doc-card">
                                        <strong><?= e($document['document_name']) ?></strong>
                                        <div class="so-note">Uploaded: <?= e($document['uploaded_at'] ?: '-') ?></div>
                                        <div class="so-note">Uploader: <?= e($document['uploaded_by_name'] ?: '-') ?></div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>

        <div class="so-tab-panel" id="billing">
            <div class="so-split">
                <section class="so-panel">
                    <div class="so-panel-header">
                        <div>
                            <h3 class="so-panel-title">Billing Summary</h3>
                            <p class="so-panel-text">Financial position, invoice coverage, and collection status for this service order.</p>
                        </div>
                        <?php if (\App\Core\Auth::can('billing.view')): ?>
                            <a href="<?= e(url('/billing/show?service_order_id=' . $order['id'])) ?>" class="button button-secondary">Open Service Order Billing</a>
                        <?php endif; ?>
                    </div>

                    <div class="so-billing-summary">
                        <div class="so-billing-tile">
                            <strong>Invoices Raised</strong>
                            <div>INR <?= e(number_format($invoiceTotal, 2)) ?></div>
                        </div>
                        <div class="so-billing-tile">
                            <strong>Payments Collected</strong>
                            <div>INR <?= e(number_format($paymentTotal, 2)) ?></div>
                        </div>
                        <div class="so-billing-tile">
                            <strong>Outstanding</strong>
                            <div>INR <?= e(number_format($outstandingTotal, 2)) ?></div>
                        </div>
                        <div class="so-billing-tile">
                            <strong>Advance Balance</strong>
                            <div>INR <?= e(number_format((float) ($billing['advance_balance'] ?? 0), 2)) ?></div>
                        </div>
                    </div>

                    <div class="so-panel" style="padding:0;border:none;box-shadow:none;background:transparent;margin-top:18px;">
                        <div class="so-panel-header">
                            <div>
                                <h3 class="so-panel-title">Invoices</h3>
                                <p class="so-panel-text">Generated invoices and their current realization status.</p>
                            </div>
                        </div>
                        <?php if ($invoices === []): ?>
                            <div class="so-empty">No invoices have been created yet.</div>
                        <?php else: ?>
                            <div class="so-table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Invoice</th>
                                            <th>Type</th>
                                            <th>Net Payable</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($invoices as $invoice): ?>
                                            <tr>
                                                <td><?= e($invoice['invoice_no']) ?></td>
                                                <td><?= e($invoice['invoice_type']) ?></td>
                                                <td>INR <?= e(number_format((float) $invoice['net_payable'], 2)) ?></td>
                                                <td><?= e(label_case((string) $invoice['payment_status'])) ?></td>
                                                <td><a href="<?= e(url('/billing/invoice?id=' . $invoice['id'])) ?>" class="chip">Open Invoice</a></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="so-panel">
                    <div class="so-panel-header">
                        <div>
                            <h3 class="so-panel-title">Collections and Readiness</h3>
                            <p class="so-panel-text">Payment movement and finance-linked readiness indicators for closure.</p>
                        </div>
                    </div>

                    <div class="so-billing-spotlight">
                        <div class="so-billing-panel <?= e($billingWorkspaceTone) ?>">
                            <strong><?= e($billingStatusLabel) ?></strong>
                            <div class="so-note" style="margin-top:8px;">Outstanding amount: INR <?= e(number_format($outstandingTotal, 2)) ?></div>
                            <div class="so-note">Recoverable expenses: INR <?= e(number_format($recoverableTotal, 2)) ?></div>
                        </div>
                    </div>

                    <?php if ($payments === []): ?>
                        <div class="so-empty" style="margin-top:18px;">No payments have been recorded yet.</div>
                    <?php else: ?>
                        <div class="so-payment-grid" style="margin-top:18px;">
                            <?php foreach ($payments as $payment): ?>
                                <article class="so-payment-card">
                                    <strong><?= e(label_case((string) $payment['transaction_type'])) ?></strong>
                                    <div class="so-note">Amount: INR <?= e(number_format((float) $payment['amount'], 2)) ?></div>
                                    <div class="so-note">Mode: <?= e(label_case((string) $payment['payment_mode'])) ?> | Status: <?= e(label_case((string) $payment['status'])) ?></div>
                                    <div class="so-note">Receipt: <?= e($payment['receipt_no'] ?: '-') ?> | Ref: <?= e($payment['reference_no'] ?: '-') ?></div>
                                    <?php if (!empty($payment['receipt_id'])): ?>
                                        <div class="so-inline-actions">
                                            <a href="<?= e(url('/billing/receipt?id=' . $payment['receipt_id'])) ?>" class="button button-secondary">Open Receipt</a>
                                        </div>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($canViewFinancialSnapshot): ?>
                        <div class="so-panel" style="padding:0;border:none;box-shadow:none;background:transparent;margin-top:18px;">
                            <div class="so-panel-header">
                                <div>
                                    <h3 class="so-panel-title">Closure Dependencies</h3>
                                    <p class="so-panel-text">These indicators affect accounting and final closure readiness.</p>
                                </div>
                            </div>
                            <div class="so-summary-grid">
                                <div class="so-summary-tile">
                                    <strong>Client Paid</strong>
                                    <div><?= (int) ($order['is_client_paid'] ?? 0) === 1 ? 'Yes' : 'No' ?></div>
                                </div>
                                <div class="so-summary-tile">
                                    <strong>Consultant Payment</strong>
                                    <div><?= (int) ($order['is_consultant_payment_pending'] ?? 0) === 1 ? 'Pending' : 'Clear' ?></div>
                                </div>
                                <div class="so-summary-tile">
                                    <strong>Procedural Closure</strong>
                                    <div><?= e($order['procedural_closed_at'] ?: 'Pending') ?></div>
                                </div>
                                <div class="so-summary-tile">
                                    <strong>Accounting Closure</strong>
                                    <div><?= e($order['accounting_closed_at'] ?: 'Pending') ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>

        <div class="so-tab-panel" id="expenses">
            <section class="so-panel">
                <div class="so-panel-header">
                    <div>
                        <h3 class="so-panel-title">Expenses and Disbursements</h3>
                        <p class="so-panel-text">Client-incurred expenses, recoverability, and linked proof management.</p>
                    </div>
                </div>

                <div class="so-summary-grid" style="margin-bottom:18px;">
                    <div class="so-summary-tile">
                        <strong>Total Disbursements</strong>
                        <div>INR <?= e(number_format($disbursementTotal, 2)) ?></div>
                    </div>
                    <div class="so-summary-tile">
                        <strong>Recoverable</strong>
                        <div>INR <?= e(number_format($recoverableTotal, 2)) ?></div>
                    </div>
                </div>

                <?php if ($canManageExpenses): ?>
                    <details style="margin-bottom:18px;" open>
                        <summary class="chip" style="cursor:pointer;">Add New Expense</summary>
                        <form method="post" enctype="multipart/form-data" action="<?= e(url('/billing/disbursements')) ?>" class="so-upload-form" style="margin-top:14px;">
                            <?= \App\Core\Csrf::inputField() ?>
                            <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                            <div class="so-summary-grid">
                                <input type="date" name="expense_date" value="<?= e(date('Y-m-d')) ?>" required>
                                <input type="text" name="expense_type" placeholder="Expense type" required>
                                <input type="number" name="amount" step="0.01" placeholder="Amount" required>
                                <input type="text" name="paid_to" placeholder="Paid to">
                            </div>
                            <label style="display:flex;gap:8px;align-items:center;">
                                <input type="checkbox" name="is_recoverable" value="1">
                                <span class="so-note">Recoverable from client</span>
                            </label>
                            <input type="file" name="proof_document" accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx">
                            <textarea name="notes" rows="3" placeholder="Expense notes"></textarea>
                            <button type="submit" class="button">Add Expense</button>
                        </form>
                    </details>
                <?php endif; ?>

                <?php if ($disbursements === []): ?>
                    <div class="so-empty">No expenses have been recorded for this service order.</div>
                <?php else: ?>
                    <div class="so-table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Recoverable</th>
                                    <th>Proof</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($disbursements as $item): ?>
                                    <tr>
                                        <td><?= e($item['expense_date']) ?></td>
                                        <td><?= e($item['expense_type']) ?></td>
                                        <td>INR <?= e(number_format((float) $item['amount'], 2)) ?></td>
                                        <td><?= (int) ($item['is_recoverable'] ?? 0) === 1 ? 'Yes' : 'No' ?></td>
                                        <td>
                                            <?php if (!empty($item['proof_document_id'])): ?>
                                                <a href="<?= e(url('/documents/show?id=' . $item['proof_document_id'])) ?>" class="chip">Open Proof</a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?= e($item['notes'] ?: '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <div class="so-tab-panel" id="team">
            <div class="so-split">
                <section class="so-panel">
                    <div class="so-panel-header">
                        <div>
                            <h3 class="so-panel-title">Internal Team</h3>
                            <p class="so-panel-text">Operational ownership and role distribution for this service order.</p>
                        </div>
                    </div>
                    <div class="so-team-grid">
                        <article class="so-team-card">
                            <strong>CRM</strong>
                            <div class="so-note" style="margin-top:8px;"><?= e($order['assigned_crm_name'] ?: 'Not assigned') ?></div>
                        </article>
                        <article class="so-team-card">
                            <strong>Assistant CRM</strong>
                            <div class="so-note" style="margin-top:8px;"><?= e($order['assistant_crm_name'] ?: 'Not assigned') ?></div>
                        </article>
                        <article class="so-team-card">
                            <strong>Backend</strong>
                            <div class="so-note" style="margin-top:8px;"><?= e($order['backend_name'] ?: 'Not assigned') ?></div>
                        </article>
                        <article class="so-team-card">
                            <strong>DEO</strong>
                            <div class="so-note" style="margin-top:8px;"><?= e($order['deo_name'] ?: 'Not assigned') ?></div>
                        </article>
                    </div>
                </section>

                <section class="so-panel">
                    <div class="so-panel-header">
                        <div>
                            <h3 class="so-panel-title">Consultant Workspace</h3>
                            <p class="so-panel-text">Consultant assignment, reviewer ownership, and payment dependency tracking.</p>
                        </div>
                        <?php if ($canOpenConsultantWorkspace): ?>
                            <a href="<?= e(url('/consultants/show?service_order_id=' . $order['id'])) ?>" class="button button-secondary">Open Consultant Workspace</a>
                        <?php endif; ?>
                    </div>

                    <?php if ($latestConsultant !== null): ?>
                        <div class="so-team-card" style="margin-bottom:14px;">
                            <strong>Latest Consultant Assignment</strong>
                            <div class="so-note" style="margin-top:8px;">Consultant: <?= e($latestConsultant['consultant_name'] ?: '-') ?></div>
                            <div class="so-note">Reviewer: <?= e($latestConsultant['reviewer_name'] ?: '-') ?></div>
                            <div class="so-note">Status: <?= e(label_case((string) ($latestConsultant['status'] ?: '-'))) ?></div>
                            <div class="so-note">Assigned at: <?= e($latestConsultant['assigned_at'] ?: '-') ?></div>
                        </div>
                    <?php else: ?>
                        <div class="so-empty" style="margin-bottom:14px;">No consultant assignment has been created for this case yet.</div>
                    <?php endif; ?>

                    <?php if ($canAssignConsultant): ?>
                        <form method="post" action="<?= e(url('/consultants/assign')) ?>" class="so-consultant-form">
                            <?= \App\Core\Csrf::inputField() ?>
                            <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                            <select name="consultant_user_id" required>
                                <option value="">Select consultant</option>
                                <?php foreach (($consultants ?? []) as $consultant): ?>
                                    <option value="<?= e($consultant['id']) ?>"><?= e($consultant['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="internal_reviewer_id">
                                <option value="">Select internal reviewer</option>
                                <?php foreach (($reviewers ?? []) as $reviewer): ?>
                                    <option value="<?= e($reviewer['id']) ?>"><?= e($reviewer['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <textarea name="remarks" rows="3" placeholder="Assignment remarks"></textarea>
                            <button type="submit" class="button">Assign Consultant</button>
                        </form>
                    <?php endif; ?>
                </section>
            </div>
        </div>

        <div class="so-tab-panel" id="timeline">
            <section class="so-panel">
                <div class="so-panel-header">
                    <div>
                        <h3 class="so-panel-title">Activity Feed</h3>
                        <p class="so-panel-text">Chronological history of workflow movement, closure events, and recorded operational actions. Newest items appear first.</p>
                    </div>
                </div>

                <?php if ($timelineEntries === []): ?>
                    <div class="so-empty">No timeline activity is available yet for this service order.</div>
                <?php else: ?>
                    <div class="so-timeline">
                        <?php foreach ($timelineEntries as $entry): ?>
                            <article class="so-timeline-item">
                                <div class="so-timeline-top">
                                    <strong><?= e($entry['title']) ?></strong>
                                    <span class="so-timeline-tag"><?= e($entry['tag']) ?></span>
                                </div>
                                <div class="so-timeline-meta">
                                    <span>Date: <?= e($entry['occurred_at'] ?: '-') ?></span>
                                    <span>User: <?= e($entry['user_name'] ?: '-') ?></span>
                                </div>
                                <div class="so-note"><?= e($entry['remarks'] !== '' ? $entry['remarks'] : '-') ?></div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <div class="so-tab-panel" id="closure">
            <section class="so-panel">
                <div class="so-panel-header">
                    <div>
                        <h3 class="so-panel-title">Closure Readiness Dashboard</h3>
                        <p class="so-panel-text">Track procedural, accounting, and final closure with visible status, blockers, and completion ownership.</p>
                    </div>
                </div>

                <div class="so-closure-summary">
                    <div class="so-summary-tile">
                        <strong>Procedural Status</strong>
                        <div><?= e($order['procedural_closed_at'] ? 'Completed' : 'Pending') ?></div>
                    </div>
                    <div class="so-summary-tile">
                        <strong>Accounting Status</strong>
                        <div><?= e($order['accounting_closed_at'] ? 'Completed' : 'Pending') ?></div>
                    </div>
                    <div class="so-summary-tile">
                        <strong>Final Status</strong>
                        <div><?= e($order['final_closed_at'] ? 'Completed' : 'Pending') ?></div>
                    </div>
                    <div class="so-summary-tile">
                        <strong>Blocking Condition</strong>
                        <div><?= (int) ($order['is_consultant_payment_pending'] ?? 0) === 1 ? 'Consultant payment pending' : 'No consultant payment block' ?></div>
                    </div>
                </div>

                <div class="so-closure-grid">
                    <?php foreach ($closureCards as $closureCard): ?>
                        <?php $closureData = $closureByType[$closureCard['type']] ?? null; ?>
                        <article class="so-closure-card <?= !empty($closureCard['completed_at']) ? 'status-complete' : 'status-pending' ?>">
                            <strong><?= e($closureCard['title']) ?></strong>
                            <div class="so-note" style="margin-top:8px;">Status: <?= e(label_case((string) ($closureData['closure_status'] ?? (!empty($closureCard['completed_at']) ? 'COMPLETED' : 'PENDING')))) ?></div>
                            <div class="so-note">Completed On: <?= e($closureCard['completed_at'] ?: '-') ?></div>
                            <div class="so-note">Completed By: <?= e($closureData['closed_by_name'] ?? '-') ?></div>
                            <div class="so-note">Blocking Reason: <?= e($closureData['block_reason'] ?? '-') ?></div>

                            <?php if ($closureCard['allowed']): ?>
                                <form method="post" action="<?= e(url($closureCard['path'])) ?>" class="so-closure-form" style="margin-top:12px;">
                                    <?= \App\Core\Csrf::inputField() ?>
                                    <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                                    <input type="text" name="note" placeholder="Optional closure note">
                                    <button type="submit" class="button" <?= $closureCard['can_submit'] ? '' : 'disabled' ?>><?= e($closureCard['button']) ?></button>
                                </form>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </section>
</section>

<script>
    (function () {
        const tabButtons = Array.from(document.querySelectorAll('[data-so-tab]'));
        const tabPanels = Array.from(document.querySelectorAll('.so-tab-panel'));
        const triggerButtons = Array.from(document.querySelectorAll('[data-so-tab-trigger]'));
        const stageButtons = Array.from(document.querySelectorAll('.so-stage-button'));
        const stagePanels = Array.from(document.querySelectorAll('.so-stage-panel'));

        function activateTab(tabId) {
            tabButtons.forEach((button) => {
                button.classList.toggle('active', button.dataset.soTab === tabId);
            });

            tabPanels.forEach((panel) => {
                panel.classList.toggle('active', panel.id === tabId);
            });
        }

        function activateStage(panelId) {
            stageButtons.forEach((button) => {
                button.classList.toggle('active', button.dataset.stagePanel === panelId);
            });

            stagePanels.forEach((panel) => {
                panel.classList.toggle('active', panel.id === panelId);
            });
        }

        tabButtons.forEach((button) => {
            button.addEventListener('click', function () {
                activateTab(button.dataset.soTab);
                history.replaceState(null, '', '#' + button.dataset.soTab);
            });
        });

        triggerButtons.forEach((button) => {
            button.addEventListener('click', function (event) {
                const target = button.dataset.soTabTrigger;
                if (!target) {
                    return;
                }

                event.preventDefault();
                activateTab(target);
                history.replaceState(null, '', '#' + target);

                const targetPanel = document.getElementById(target);
                if (targetPanel) {
                    targetPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        stageButtons.forEach((button) => {
            button.addEventListener('click', function () {
                activateStage(button.dataset.stagePanel);
            });
        });

        const hashTarget = window.location.hash.replace('#', '');
        if (hashTarget) {
            const validTab = tabButtons.find((button) => button.dataset.soTab === hashTarget);
            if (validTab) {
                activateTab(hashTarget);
            }
        }
    }());
</script>

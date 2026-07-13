<?php
$invoices = is_array($invoices ?? null) ? $invoices : [];
$payments = is_array($payments ?? null) ? $payments : [];
$notifications = is_array($notifications ?? null) ? $notifications : [];

$activeServices = [];
$outstandingInvoices = [];
$paidInvoiceCount = 0;
$dueSoonCount = 0;
$totalOutstanding = 0.0;
$totalPaid = 0.0;
$receiptCount = 0;
$recentInvoiceList = array_slice($invoices, 0, 5);

foreach ($invoices as $invoice) {
    $serviceKey = trim((string) ($invoice['so_no'] ?? ''));
    if ($serviceKey !== '') {
        $activeServices[$serviceKey] = true;
    }

    $outstandingAmount = (float) ($invoice['outstanding_amount'] ?? 0);
    $totalOutstanding += $outstandingAmount;

    if ($outstandingAmount > 0) {
        $outstandingInvoices[] = $invoice;
        $dueDate = (string) ($invoice['due_date'] ?? '');
        if ($dueDate !== '') {
            $dueTimestamp = strtotime($dueDate);
            if ($dueTimestamp !== false && $dueTimestamp <= strtotime('+7 days') && $dueTimestamp >= strtotime('today')) {
                $dueSoonCount++;
            }
        }
    } else {
        $paidInvoiceCount++;
    }
}

foreach ($payments as $payment) {
    $totalPaid += (float) ($payment['amount'] ?? 0);
    if (!empty($payment['receipt_no'])) {
        $receiptCount++;
    }

    $serviceKey = trim((string) ($payment['so_no'] ?? ''));
    if ($serviceKey !== '') {
        $activeServices[$serviceKey] = true;
    }
}

$activeServiceCount = count($activeServices);
$notificationCount = count($notifications);
$actionNotificationCount = 0;
$recentNotifications = array_slice($notifications, 0, 5);

foreach ($notifications as $notification) {
    $deliveryStatus = strtoupper((string) ($notification['delivery_status'] ?? ''));
    $message = strtolower((string) ($notification['message'] ?? ''));
    $subject = strtolower((string) ($notification['subject'] ?? ''));
    if ($deliveryStatus !== 'READ' || str_contains($message, 'pending') || str_contains($message, 'due') || str_contains($subject, 'action')) {
        $actionNotificationCount++;
    }
}

$nextActionTitle = 'Your workspace is up to date';
$nextActionText = 'There are no urgent billing or notification actions visible right now.';
$nextActionLink = url('/client-portal/pso');
$nextActionLinkLabel = 'View My Requests';

if ($dueSoonCount > 0) {
    $nextActionTitle = 'Invoice due soon';
    $nextActionText = $dueSoonCount . ' invoice(s) are due within the next 7 days. Please review and complete payment to avoid follow-up delays.';
    $nextActionLinkLabel = 'Review Due Invoices';
} elseif ($outstandingInvoices !== []) {
    $nextActionTitle = 'Outstanding payment requires attention';
    $nextActionText = 'You have ' . count($outstandingInvoices) . ' invoice(s) with an outstanding total of INR ' . number_format($totalOutstanding, 2) . '.';
    $nextActionLinkLabel = 'Open Billing';
} elseif ($actionNotificationCount > 0) {
    $nextActionTitle = 'A recent update is available';
    $nextActionText = 'Please review your latest portal notifications for service or billing updates.';
    $nextActionLinkLabel = 'Review Notifications';
    $nextActionLink = '#portal-notifications';
} elseif ($activeServiceCount > 0) {
    $nextActionTitle = 'Track your active services';
    $nextActionText = 'You currently have ' . $activeServiceCount . ' active service(s) visible in the portal.';
    $nextActionLink = url('/service-orders');
    $nextActionLinkLabel = 'Open Service Tracking';
}

$outstandingByState = [
    'due_soon' => [],
    'outstanding' => [],
    'paid' => [],
];

foreach ($invoices as $invoice) {
    $outstandingAmount = (float) ($invoice['outstanding_amount'] ?? 0);
    if ($outstandingAmount <= 0) {
        $outstandingByState['paid'][] = $invoice;
        continue;
    }

    $dueDate = (string) ($invoice['due_date'] ?? '');
    $dueTimestamp = $dueDate !== '' ? strtotime($dueDate) : false;
    if ($dueTimestamp !== false && $dueTimestamp <= strtotime('+7 days') && $dueTimestamp >= strtotime('today')) {
        $outstandingByState['due_soon'][] = $invoice;
    } else {
        $outstandingByState['outstanding'][] = $invoice;
    }
}

function portalUpdateAreaLabel(string $linkedModule): string
{
    return match (strtoupper(trim($linkedModule))) {
        'SO' => 'Service',
        'PSO' => 'Request',
        'BILLING' => 'Billing',
        'DOCUMENTS', 'DOCUMENT' => 'Documents',
        'CLIENT' => 'Profile',
        default => 'General',
    };
}
?>
<style>
    .portal-account-shell {
        display: grid;
        gap: 20px;
    }
    .portal-hero {
        display: grid;
        gap: 22px;
        padding: 28px;
        border-radius: 28px;
        color: #f8fbfc;
        background: linear-gradient(145deg, #0f4c5c 0%, #0f766e 55%, #ea8a2f 100%);
        box-shadow: 0 24px 44px rgba(15, 76, 92, 0.18);
    }
    .portal-hero-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        flex-wrap: wrap;
    }
    .portal-kicker {
        font-size: 0.78rem;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: rgba(242, 247, 249, 0.84);
        font-weight: 800;
    }
    .portal-hero h2 {
        margin: 10px 0 8px;
        color: #ffffff;
        font-size: clamp(1.9rem, 3vw, 2.65rem);
        line-height: 1.06;
    }
    .portal-subtitle {
        margin: 0;
        max-width: 760px;
        color: rgba(241, 248, 250, 0.88);
        line-height: 1.65;
        font-size: 1rem;
    }
    .portal-hero-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .portal-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
    }
    .portal-summary-card {
        padding: 16px 18px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.14);
    }
    .portal-summary-label {
        font-size: 0.75rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: rgba(241, 247, 250, 0.76);
        font-weight: 800;
    }
    .portal-summary-value {
        margin-top: 8px;
        color: #ffffff;
        font-size: 1.08rem;
        line-height: 1.45;
        font-weight: 700;
    }
    .portal-next-action {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) auto;
        gap: 16px;
        align-items: center;
        padding: 18px 20px;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.14);
    }
    .portal-next-action-label {
        font-size: 0.76rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: rgba(241, 247, 250, 0.76);
        font-weight: 800;
    }
    .portal-next-action-title {
        margin-top: 8px;
        color: #ffffff;
        font-size: 1.08rem;
        font-weight: 800;
    }
    .portal-next-action-text {
        margin-top: 6px;
        color: rgba(243, 248, 250, 0.86);
        line-height: 1.6;
    }
    .portal-kpis {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 16px;
    }
    .portal-kpi {
        display: grid;
        gap: 10px;
        padding: 20px 22px;
        border-radius: 22px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbfc 100%);
        border: 1px solid rgba(15, 118, 110, 0.09);
        box-shadow: 0 16px 28px rgba(15, 76, 92, 0.06);
    }
    .portal-kpi-label {
        font-size: 0.76rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #71838d;
        font-weight: 800;
    }
    .portal-kpi-value {
        color: #0f172a;
        font-size: 1.9rem;
        line-height: 1;
        font-weight: 800;
    }
    .portal-kpi-detail {
        color: #607b86;
        font-size: 0.94rem;
        line-height: 1.6;
    }
    .portal-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(290px, 0.8fr);
        gap: 18px;
    }
    .portal-panel {
        padding: 22px;
        border-radius: 24px;
        background: #ffffff;
        border: 1px solid rgba(15, 118, 110, 0.08);
        box-shadow: 0 16px 34px rgba(15, 76, 92, 0.08);
    }
    .portal-panel-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .portal-panel-title {
        margin: 0;
        font-size: 1.14rem;
        color: #17313b;
    }
    .portal-panel-text {
        margin: 6px 0 0;
        color: #607b86;
        line-height: 1.65;
        font-size: 0.95rem;
    }
    .portal-quick-grid,
    .portal-client-grid,
    .portal-billing-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
    }
    .portal-support-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
    }
    .portal-tile,
    .portal-status-card,
    .portal-invoice-card,
    .portal-payment-card,
    .portal-notification-card {
        padding: 16px 18px;
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbfc 100%);
        border: 1px solid rgba(15, 118, 110, 0.08);
    }
    .portal-tile strong,
    .portal-status-card strong,
    .portal-invoice-card strong,
    .portal-payment-card strong,
    .portal-notification-card strong {
        display: block;
        color: #17313b;
        margin-bottom: 8px;
    }
    .portal-muted {
        color: #62748a;
        line-height: 1.6;
    }
    .portal-chip {
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
    .portal-chip.warning {
        background: #fff7ed;
        color: #c2410c;
    }
    .portal-chip.good {
        background: #f0fdf4;
        color: #15803d;
    }
    .portal-stack {
        display: grid;
        gap: 12px;
    }
    .portal-invoice-card.alert {
        border-color: rgba(234, 88, 12, 0.16);
        background: linear-gradient(180deg, #ffffff 0%, #fff7ed 100%);
    }
    .portal-payment-form {
        display: grid;
        gap: 10px;
        margin-top: 12px;
    }
    .portal-payment-form .grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }
    .portal-payment-form input,
    .portal-payment-form select {
        padding: 12px 13px;
        border-radius: 14px;
    }
    .portal-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 12px;
    }
    .portal-empty {
        padding: 18px;
        border-radius: 18px;
        background: #f8fbfc;
        border: 1px dashed rgba(15, 118, 110, 0.18);
        color: #607b86;
    }
    .portal-support {
        display: grid;
        gap: 14px;
    }
    .portal-support-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 14px;
    }
    .portal-link-row {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 12px;
    }
    @media (max-width: 1180px) {
        .portal-kpis {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .portal-layout {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 820px) {
        .portal-kpis {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .portal-next-action {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 640px) {
        .portal-kpis {
            grid-template-columns: 1fr;
        }
        .portal-hero,
        .portal-panel {
            padding: 20px;
        }
        .portal-hero-actions,
        .portal-actions,
        .portal-link-row {
            display: grid;
            grid-template-columns: 1fr;
        }
        .portal-hero-actions .button,
        .portal-actions .button,
        .portal-link-row .button {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<section class="portal-account-shell">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <section class="portal-hero">
        <div class="portal-hero-top">
            <div>
                <div class="portal-kicker">Client Workspace</div>
                <h2><?= e($client['legal_name']) ?></h2>
                <p class="portal-subtitle">
                    Track your invoices, receipts, and active services from one secure workspace. Review what needs your attention and act on billing items without calling the office.
                </p>
            </div>
            <div class="portal-hero-actions">
                <a href="<?= e(url('/client-portal/pso')) ?>" class="button">My Requests</a>
                <a href="<?= e(url('/client-portal/documents')) ?>" class="button button-secondary">Document Centre</a>
                <a href="<?= e(url('/service-orders')) ?>" class="button button-secondary">Track Services</a>
                <a href="<?= e(url('/client-portal/support')) ?>" class="button button-secondary">Support</a>
            </div>
        </div>

        <div class="portal-summary-grid">
            <div class="portal-summary-card">
                <div class="portal-summary-label">Primary Contact</div>
                <div class="portal-summary-value"><?= e($contact['contact_name'] ?? '-') ?></div>
            </div>
            <div class="portal-summary-card">
                <div class="portal-summary-label">Email</div>
                <div class="portal-summary-value"><?= e($contact['email'] ?? '-') ?></div>
            </div>
            <div class="portal-summary-card">
                <div class="portal-summary-label">PAN / GSTIN</div>
                <div class="portal-summary-value"><?= e(($client['pan'] ?: '-') . ' / ' . ($client['gstin'] ?: '-')) ?></div>
            </div>
            <div class="portal-summary-card">
                <div class="portal-summary-label">Active Services</div>
                <div class="portal-summary-value"><?= e((string) $activeServiceCount) ?> service(s)</div>
            </div>
        </div>

        <div class="portal-next-action">
            <div>
                <div class="portal-next-action-label">Next Action Required</div>
                <div class="portal-next-action-title"><?= e($nextActionTitle) ?></div>
                <div class="portal-next-action-text"><?= e($nextActionText) ?></div>
            </div>
            <a href="<?= e($nextActionLink) ?>" class="button"><?= e($nextActionLinkLabel) ?></a>
        </div>
    </section>

    <section class="portal-kpis">
        <article class="portal-kpi">
            <div class="portal-kpi-label">Active Services</div>
            <div class="portal-kpi-value"><?= e((string) $activeServiceCount) ?></div>
            <div class="portal-kpi-detail">Services currently visible through invoices and payment history.</div>
        </article>
        <article class="portal-kpi">
            <div class="portal-kpi-label">Invoices Due</div>
            <div class="portal-kpi-value"><?= e((string) count($outstandingInvoices)) ?></div>
            <div class="portal-kpi-detail">Invoices with unpaid balance that may need your review or payment action.</div>
        </article>
        <article class="portal-kpi">
            <div class="portal-kpi-label">Outstanding</div>
            <div class="portal-kpi-value" style="font-size:1.3rem;line-height:1.2;">INR <?= e(number_format($totalOutstanding, 2)) ?></div>
            <div class="portal-kpi-detail">Total pending client payment across all currently visible invoices.</div>
        </article>
        <article class="portal-kpi">
            <div class="portal-kpi-label">Receipts</div>
            <div class="portal-kpi-value"><?= e((string) $receiptCount) ?></div>
            <div class="portal-kpi-detail">Receipts available for payments already recorded in the portal.</div>
        </article>
        <article class="portal-kpi">
            <div class="portal-kpi-label">Notifications</div>
            <div class="portal-kpi-value"><?= e((string) $notificationCount) ?></div>
            <div class="portal-kpi-detail"><?= e((string) $actionNotificationCount) ?> item(s) may require attention or review.</div>
        </article>
    </section>

    <section class="portal-layout">
        <div class="portal-panel">
            <div class="portal-panel-header">
                <div>
                    <h3 class="portal-panel-title">Billing Overview</h3>
                    <p class="portal-panel-text">See what is due, what has already been paid, and which receipts are available for download.</p>
                </div>
            </div>

            <div class="portal-billing-summary">
                <div class="portal-tile">
                    <strong>Outstanding Amount</strong>
                    <div class="portal-muted">INR <?= e(number_format($totalOutstanding, 2)) ?></div>
                </div>
                <div class="portal-tile">
                    <strong>Due Soon</strong>
                    <div class="portal-muted"><?= e((string) $dueSoonCount) ?> invoice(s) due within 7 days</div>
                </div>
                <div class="portal-tile">
                    <strong>Total Paid</strong>
                    <div class="portal-muted">INR <?= e(number_format($totalPaid, 2)) ?></div>
                </div>
                <div class="portal-tile">
                    <strong>Paid Invoices</strong>
                    <div class="portal-muted"><?= e((string) $paidInvoiceCount) ?> invoice(s) fully settled</div>
                </div>
                <div class="portal-tile">
                    <strong>Receipts Available</strong>
                    <div class="portal-muted"><?= e((string) $receiptCount) ?> receipt(s) ready to open or download</div>
                </div>
            </div>

            <div class="portal-panel" style="padding:0;border:none;box-shadow:none;background:transparent;margin-top:18px;">
                <div class="portal-panel-header">
                    <div>
                        <h3 class="portal-panel-title">Invoices Requiring Action</h3>
                        <p class="portal-panel-text">Pay due invoices, submit transfer references, and open invoice copies from the same place.</p>
                    </div>
                </div>

                <?php if ($outstandingInvoices === []): ?>
                    <div class="portal-empty">No invoice is currently awaiting payment action.</div>
                <?php else: ?>
                    <div class="portal-stack">
                        <?php foreach ($outstandingInvoices as $invoice): ?>
                            <?php
                            $outstandingAmount = (float) ($invoice['outstanding_amount'] ?? 0);
                            $dueDate = (string) ($invoice['due_date'] ?? '');
                            $isDueSoon = $dueDate !== '' && strtotime($dueDate) !== false && strtotime($dueDate) <= strtotime('+7 days') && strtotime($dueDate) >= strtotime('today');
                            ?>
                            <article class="portal-invoice-card<?= $isDueSoon ? ' alert' : '' ?>">
                                <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;">
                                    <div>
                                        <strong><?= e($invoice['invoice_no']) ?></strong>
                                        <div class="portal-muted"><?= e($invoice['so_no']) ?> | <?= e($invoice['service_type_name']) ?> | <?= e($invoice['company_name']) ?></div>
                                    </div>
                                    <span class="portal-chip<?= $isDueSoon ? ' warning' : '' ?>">
                                        <?= e($isDueSoon ? 'Due Soon' : 'Payment Pending') ?>
                                    </span>
                                </div>
                                <div class="portal-muted" style="margin-top:8px;">Invoice Date: <?= e($invoice['invoice_date']) ?> | Due Date: <?= e($dueDate ?: '-') ?></div>
                                <div class="portal-muted">Net Payable: INR <?= e(number_format((float) $invoice['net_payable'], 2)) ?> | Outstanding: INR <?= e(number_format($outstandingAmount, 2)) ?></div>

                                <div class="portal-actions">
                                    <a href="<?= e(url('/billing/invoice?id=' . $invoice['id'])) ?>" class="button button-secondary">Open Invoice</a>
                                </div>

                                <form method="post" action="<?= e(url('/client-portal/payments')) ?>" class="portal-payment-form">
                                    <?= \App\Core\Csrf::inputField() ?>
                                    <input type="hidden" name="invoice_id" value="<?= e((string) $invoice['id']) ?>">
                                    <div class="grid">
                                        <input type="number" step="0.01" name="amount" value="<?= e(number_format($outstandingAmount, 2, '.', '')) ?>" placeholder="Payment amount">
                                        <select name="payment_mode">
                                            <option value="BANK_TRANSFER">Bank Transfer</option>
                                            <option value="UPI">UPI</option>
                                            <option value="RAZORPAY">Razorpay</option>
                                        </select>
                                        <input type="date" name="payment_date" value="<?= e(date('Y-m-d')) ?>">
                                        <input type="text" name="reference_no" placeholder="UTR / Reference">
                                    </div>
                                    <button type="submit" class="button">Submit Payment</button>
                                </form>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="portal-panel" style="padding:0;border:none;box-shadow:none;background:transparent;margin-top:18px;">
                <div class="portal-panel-header">
                    <div>
                        <h3 class="portal-panel-title">Available Invoices</h3>
                        <p class="portal-panel-text">Open invoice copies whenever they are available in your account, even when no payment action is pending.</p>
                    </div>
                </div>

                <?php if ($recentInvoiceList === []): ?>
                    <div class="portal-empty">No invoices are visible in your account yet.</div>
                <?php else: ?>
                    <div class="portal-stack">
                        <?php foreach ($recentInvoiceList as $invoice): ?>
                            <?php $invoiceOutstanding = (float) ($invoice['outstanding_amount'] ?? 0); ?>
                            <article class="portal-invoice-card">
                                <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;">
                                    <div>
                                        <strong><?= e($invoice['invoice_no']) ?></strong>
                                        <div class="portal-muted"><?= e($invoice['service_type_name']) ?> | <?= e($invoice['so_no'] ?: '-') ?></div>
                                    </div>
                                    <span class="portal-chip <?= $invoiceOutstanding > 0 ? 'warning' : 'good' ?>">
                                        <?= e($invoiceOutstanding > 0 ? 'Payment Pending' : 'Paid') ?>
                                    </span>
                                </div>
                                <div class="portal-muted" style="margin-top:8px;">Invoice Date: <?= e($invoice['invoice_date'] ?: '-') ?> | Due Date: <?= e($invoice['due_date'] ?: '-') ?></div>
                                <div class="portal-muted">Net Payable: INR <?= e(number_format((float) ($invoice['net_payable'] ?? 0), 2)) ?> | Outstanding: INR <?= e(number_format($invoiceOutstanding, 2)) ?></div>
                                <div class="portal-actions">
                                    <a href="<?= e(url('/billing/invoice?id=' . $invoice['id'])) ?>" class="button button-secondary">Open Invoice</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="portal-panel">
            <div class="portal-panel-header">
                <div>
                    <h3 class="portal-panel-title">Workspace Snapshot</h3>
                    <p class="portal-panel-text">A quick view of activity, contact details, and recent communication from the firm.</p>
                </div>
            </div>

            <div class="portal-quick-grid">
                <div class="portal-status-card">
                    <strong>Pending Billing Actions</strong>
                    <div class="portal-muted"><?= e((string) count($outstandingInvoices)) ?> invoice(s) still need payment attention.</div>
                </div>
                <div class="portal-status-card">
                    <strong>Recent Updates</strong>
                    <div class="portal-muted"><?= e((string) $notificationCount) ?> notification(s) posted to your portal workspace.</div>
                </div>
                <div class="portal-status-card">
                    <strong>Receipts Available</strong>
                    <div class="portal-muted"><?= e((string) $receiptCount) ?> receipt(s) can be opened or downloaded.</div>
                </div>
                <div class="portal-status-card">
                    <strong>Primary Contact</strong>
                    <div class="portal-muted"><?= e($contact['contact_name'] ?? '-') ?> | <?= e($contact['mobile'] ?? '-') ?></div>
                </div>
            </div>

            <div class="portal-panel" style="padding:0;border:none;box-shadow:none;background:transparent;margin-top:18px;" id="portal-notifications">
                <div class="portal-panel-header">
                    <div>
                        <h3 class="portal-panel-title">Notifications</h3>
                        <p class="portal-panel-text">Recent messages and reminders relevant to your requests, services, or billing items.</p>
                    </div>
                </div>

                <?php if ($recentNotifications === []): ?>
                    <div class="portal-empty">No notifications are available right now.</div>
                <?php else: ?>
                    <div class="portal-stack">
                        <?php foreach ($recentNotifications as $notification): ?>
                            <?php
                            $deliveryStatus = strtoupper((string) ($notification['delivery_status'] ?? ''));
                            $tagClass = $deliveryStatus === 'READ' ? 'good' : '';
                            ?>
                            <article class="portal-notification-card">
                                <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;">
                                    <div>
                                        <strong><?= e($notification['subject'] ?: 'Notification') ?></strong>
                                        <div class="portal-muted"><?= e($notification['message']) ?></div>
                                    </div>
                                    <span class="portal-chip <?= e($tagClass) ?>"><?= e($deliveryStatus ?: 'NEW') ?></span>
                                </div>
                                <div class="portal-muted" style="margin-top:8px;">Update Area: <?= e(portalUpdateAreaLabel((string) ($notification['linked_module'] ?? ''))) ?> | Posted: <?= e($notification['created_at']) ?></div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="portal-layout">
        <div class="portal-panel">
            <div class="portal-panel-header">
                <div>
                    <h3 class="portal-panel-title">Payments and Receipts</h3>
                    <p class="portal-panel-text">Review past payments, payment modes, and receipt references already recorded in your account.</p>
                </div>
            </div>

            <?php if ($payments === []): ?>
                <div class="portal-empty">No payments have been recorded yet.</div>
            <?php else: ?>
                <div class="portal-stack">
                    <?php foreach ($payments as $payment): ?>
                        <article class="portal-payment-card">
                            <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;">
                                <div>
                                    <strong><?= e(label_case((string) $payment['transaction_type'])) ?> | <?= e(money_inr($payment['amount'])) ?></strong>
                                    <div class="portal-muted"><?= e($payment['so_no'] ?: '-') ?> | <?= e($payment['payment_mode']) ?> | <?= e($payment['payment_date']) ?></div>
                                </div>
                                <span class="portal-chip good"><?= e($payment['receipt_no'] ? 'Receipt Ready' : 'Receipt Pending') ?></span>
                            </div>
                            <div class="portal-muted" style="margin-top:8px;">Receipt: <?= e($payment['receipt_no'] ?: '-') ?> | Reference: <?= e($payment['reference_no'] ?: '-') ?></div>
                            <?php if (!empty($payment['receipt_id'])): ?>
                                <div class="portal-actions">
                                    <a href="<?= e(url('/billing/receipt?id=' . $payment['receipt_id'])) ?>" class="button button-secondary">Open Receipt</a>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="portal-panel">
            <div class="portal-panel-header">
                <div>
                    <h3 class="portal-panel-title">Profile and Support</h3>
                    <p class="portal-panel-text">Your firm identity, contact information, and quick ways to continue service collaboration.</p>
                </div>
            </div>

            <div class="portal-client-grid">
                <div class="portal-tile">
                    <strong>Client Identity</strong>
                    <div class="portal-muted">PAN: <?= e($client['pan'] ?: '-') ?></div>
                    <div class="portal-muted">TAN: <?= e($client['tan'] ?: '-') ?></div>
                    <div class="portal-muted">GSTIN: <?= e($client['gstin'] ?: '-') ?></div>
                </div>
                <div class="portal-tile">
                    <strong>Primary Contact</strong>
                    <div class="portal-muted"><?= e($contact['contact_name'] ?? '-') ?></div>
                    <div class="portal-muted"><?= e($contact['email'] ?? '-') ?></div>
                    <div class="portal-muted"><?= e($contact['mobile'] ?? '-') ?></div>
                </div>
                <div class="portal-tile">
                    <strong>Account Security</strong>
                    <div class="portal-muted">Your Aadhaar is masked in the portal and billing documents are served through secure document access.</div>
                </div>
            </div>

            <div class="portal-support" style="margin-top:18px;">
                <div class="portal-panel-header" style="margin-bottom:8px;">
                    <div>
                        <h3 class="portal-panel-title">Need Help?</h3>
                        <p class="portal-panel-text">Use the existing portal workspaces to continue your request, track services, or review billing items.</p>
                    </div>
                </div>

                <div class="portal-support-grid">
                    <div class="portal-tile">
                        <strong>Service Requests</strong>
                        <div class="portal-muted">Create a new request or review the progress of existing requests in the client portal.</div>
                        <div class="portal-link-row">
                            <a href="<?= e(url('/client-portal/pso')) ?>" class="button button-secondary">Open Requests</a>
                        </div>
                    </div>
                    <div class="portal-tile">
                        <strong>Service Tracking</strong>
                        <div class="portal-muted">Open your services to review work completed and current progress.</div>
                        <div class="portal-link-row">
                            <a href="<?= e(url('/service-orders')) ?>" class="button button-secondary">Track Services</a>
                        </div>
                    </div>
                    <div class="portal-tile">
                        <strong>Document Centre</strong>
                        <div class="portal-muted">Review uploaded files, shared documents, and pending document requests in one secure place.</div>
                        <div class="portal-link-row">
                            <a href="<?= e(url('/client-portal/documents')) ?>" class="button button-secondary">Open Documents</a>
                        </div>
                    </div>
                    <div class="portal-tile">
                        <strong>Billing Support</strong>
                        <div class="portal-muted">Open invoices, submit payment details, and access receipts from this workspace.</div>
                        <div class="portal-link-row">
                            <a href="<?= e(url('/client-portal/support')) ?>" class="button button-secondary">Open Support</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>

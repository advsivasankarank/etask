<?php
$historyByStage = [];
foreach ($workflowHistory as $historyRow) {
    $historyByStage[(string) $historyRow['stage_code']] = $historyRow;
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

$disbursements = $billing['disbursements'] ?? [];
$invoices = $billing['invoices'] ?? [];
$payments = $billing['payments'] ?? [];
$disbursementTotal = array_reduce($disbursements, static fn (float $carry, array $row): float => $carry + (float) ($row['amount'] ?? 0), 0.0);
$recoverableTotal = array_reduce($disbursements, static fn (float $carry, array $row): float => $carry + ((int) ($row['is_recoverable'] ?? 0) === 1 ? (float) ($row['amount'] ?? 0) : 0.0), 0.0);
$invoiceTotal = array_reduce($invoices, static fn (float $carry, array $row): float => $carry + (float) ($row['net_payable'] ?? 0), 0.0);
$paymentTotal = array_reduce($payments, static fn (float $carry, array $row): float => $carry + ((string) ($row['status'] ?? '') === 'SUCCESS' ? (float) ($row['amount'] ?? 0) : 0.0), 0.0);
$currentAckLabel = match ($currentStageCode) {
    'FORM_3CB_FILED' => 'Capture 3CB Acknowledgement',
    'FILING_DONE', 'ITR_FILING_DONE' => (string) ($order['service_type_code'] ?? '') === 'ITR' ? 'Capture ITR Acknowledgement' : 'Capture Acknowledgement',
    default => 'Capture Acknowledgement',
};
$currentAckPlaceholder = match ($currentStageCode) {
    'FORM_3CB_FILED' => '3CB acknowledgement number',
    'FILING_DONE', 'ITR_FILING_DONE' => (string) ($order['service_type_code'] ?? '') === 'ITR' ? 'ITR ARN / acknowledgement number' : 'Acknowledgement / ARN number',
    default => 'Acknowledgement number',
};
?>
<section class="panel">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <div>
            <div class="eyebrow">Service Order</div>
            <h3 style="margin:0 0 6px;"><?= e($order['so_no']) ?></h3>
            <div class="subtle"><?= e($order['title']) ?></div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <?php if (\App\Core\Auth::can('billing.view')): ?>
                <a href="<?= e(url('/billing/show?service_order_id=' . $order['id'])) ?>" class="button button-secondary">Open Billing Workspace</a>
            <?php endif; ?>
            <a href="<?= e(url('/service-orders')) ?>" class="button button-secondary">Back to Register</a>
        </div>
    </div>

    <div class="grid">
        <div class="metric">
            <strong>Client</strong>
            <div style="margin-top:8px;"><?= e($order['client_name']) ?></div>
            <div style="margin-top:4px;color:#62748a;">PAN: <?= e($order['pan'] ?: '-') ?></div>
            <div style="margin-top:4px;color:#62748a;">TAN: <?= e($order['tan'] ?: '-') ?></div>
        </div>
        <div class="metric">
            <strong>Company / FY</strong>
            <div style="margin-top:8px;"><?= e($order['company_name']) ?></div>
            <div style="margin-top:4px;color:#62748a;"><?= e($order['financial_year_label']) ?></div>
        </div>
        <div class="metric">
            <strong>Workflow / Stage</strong>
            <div style="margin-top:8px;"><?= e($order['service_type_name']) ?></div>
            <div style="margin-top:4px;color:#62748a;"><?= e($historyByStage[$order['current_stage_code']]['stage_name'] ?? str_replace('_', ' ', (string) $order['current_stage_code'])) ?></div>
        </div>
        <div class="metric">
            <strong>Work Period</strong>
            <div style="margin-top:8px;"><?= e($order['period_label'] ?: '-') ?></div>
            <div style="margin-top:4px;color:#62748a;">Basis: <?= e($order['work_basis'] ?: '-') ?></div>
            <?php if (!empty($order['assessment_year'])): ?>
                <div style="margin-top:4px;color:#62748a;">AY: <?= e($order['assessment_year']) ?></div>
            <?php endif; ?>
        </div>
        <div class="metric">
            <strong>ITR Case</strong>
            <div style="margin-top:8px;"><?= e(!empty($order['itr_case_nature'] ?? null) ? str_replace('_', ' ', (string) $order['itr_case_nature']) : '-') ?></div>
            <div style="margin-top:4px;color:#62748a;">Tax Audit: <?= !empty($order['itr_case_nature'] ?? null) ? ((int) ($order['itr_tax_audit_applicable'] ?? 0) === 1 ? 'Applicable' : 'Not Applicable') : '-' ?></div>
        </div>
        <div class="metric">
            <strong>SLA / Lock</strong>
            <div style="margin-top:8px;"><?= e($order['sla_due_at'] ?: '-') ?></div>
            <div style="margin-top:4px;color:#62748a;"><?= (int) $order['is_locked'] === 1 ? 'Locked after final closure' : 'Open for workflow actions' ?></div>
        </div>
    </div>

    <div class="grid" style="margin-top:18px;">
        <div class="metric">
            <strong>Tax Payment</strong>
            <div style="margin-top:8px;"><?= e($order['payment_reference_no'] ?: 'Pending') ?></div>
            <div style="margin-top:4px;color:#62748a;"><?= (int) $order['is_paid'] === 1 ? 'Tax Paid' : 'Tax Payment Pending' ?></div>
        </div>
        <div class="metric">
            <strong>ITR Acknowledgement</strong>
            <div style="margin-top:8px;"><?= e($order['acknowledgement_no'] ?: '-') ?></div>
            <div style="margin-top:4px;color:#62748a;">Captured: <?= e($order['acknowledgement_captured_at'] ?: '-') ?></div>
        </div>
        <div class="metric">
            <strong>3CB Acknowledgement</strong>
            <div style="margin-top:8px;"><?= e(($order['form_3cb_acknowledgement_no'] ?? '') ?: '-') ?></div>
            <div style="margin-top:4px;color:#62748a;">Captured: <?= e(($order['form_3cb_acknowledgement_captured_at'] ?? '') ?: '-') ?></div>
        </div>
        <div class="metric">
            <strong>E-Verification</strong>
            <div style="margin-top:8px;"><?= e($order['e_verification_due_date'] ?: '-') ?></div>
            <div style="margin-top:4px;color:#62748a;"><?= (int) $order['is_e_verification_done'] === 1 ? 'Completed' : ((int) $order['is_overdue'] === 1 ? 'Overdue' : 'Pending') ?></div>
        </div>
        <?php if ($canViewFinancialSnapshot): ?>
            <div class="metric">
                <strong>Client Billing</strong>
                <div style="margin-top:8px;">INR <?= e(number_format($invoiceTotal, 2)) ?></div>
                <div style="margin-top:4px;color:#62748a;">Collected: INR <?= e(number_format($paymentTotal, 2)) ?></div>
            </div>
        <?php endif; ?>
        <?php if ($canViewExpenseSection): ?>
            <div class="metric">
                <strong>Disbursements</strong>
                <div style="margin-top:8px;">INR <?= e(number_format($disbursementTotal, 2)) ?></div>
                <div style="margin-top:4px;color:#62748a;">Recoverable: INR <?= e(number_format($recoverableTotal, 2)) ?></div>
            </div>
        <?php endif; ?>
    </div>

    <div class="panel" style="box-shadow:none;margin-top:18px;background:linear-gradient(180deg,#fff,#f6faf7);">
        <details open>
            <summary style="cursor:pointer;font-weight:700;color:#0f172a;">Milestone</summary>
            <div style="overflow:auto;margin-top:14px;">
                <table>
                    <thead>
                        <tr>
                            <th>Seq. No.</th>
                            <th>Milestone Name</th>
                            <th>Completion Date</th>
                            <th>Completed By</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($workflowStages as $index => $stage): ?>
                            <?php
                            $stageCode = (string) $stage['stage_code'];
                            $historyRow = $historyByStage[$stageCode] ?? null;
                            $isCurrent = $currentStageCode === $stageCode;
                            $isCompleted = $index < $currentStageIndex;
                            $isPending = $index > $currentStageIndex;
                            $rowBackground = $isCurrent ? '#fff7ed' : ($isCompleted ? '#f0fdf4' : '#ffffff');
                            $statusLabel = $isCurrent ? 'In Progress' : ($isCompleted ? 'Completed' : 'Pending');
                            $statusColor = $isCurrent ? '#f97316' : ($isCompleted ? '#15803d' : '#64748b');
                            ?>
                            <tr style="background:<?= e($rowBackground) ?>;">
                                <td><?= e((string) ($index + 1)) ?></td>
                                <td>
                                    <strong><?= e($stage['stage_name']) ?></strong><br>
                                    <span class="subtle"><?= e($stageCode) ?></span>
                                </td>
                                <td><?= e($historyRow['entered_at'] ?? '-') ?></td>
                                <td><?= e($historyRow['entered_by_name'] ?? '-') ?></td>
                                <td><span class="chip" style="border-color:<?= e($statusColor) ?>;color:<?= e($statusColor) ?>;"><?= e($statusLabel) ?></span></td>
                                <td>
                                    <div style="display:grid;gap:8px;min-width:240px;">
                                        <?php if ($isCurrent && $workflowRules['can_record_payment']): ?>
                                            <form method="post" action="<?= e(url('/workflow/payment')) ?>" style="display:grid;gap:8px;">
                                                <?= \App\Core\Csrf::inputField() ?>
                                                <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                                                <input type="text" name="payment_reference_no" placeholder="Tax challan / payment reference" style="padding:10px 12px;border:1px solid #d8e1eb;border-radius:10px;" required>
                                                <button type="submit" class="button">Mark Done</button>
                                            </form>
                                        <?php elseif ($isCurrent && $workflowRules['can_capture_ack']): ?>
                                            <form method="post" action="<?= e(url('/workflow/acknowledgement')) ?>" style="display:grid;gap:8px;">
                                                <?= \App\Core\Csrf::inputField() ?>
                                                <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                                                <input type="text" name="acknowledgement_no" placeholder="<?= e($currentAckPlaceholder) ?>" style="padding:10px 12px;border:1px solid #d8e1eb;border-radius:10px;" required>
                                                <button type="submit" class="button"><?= e($currentAckLabel) ?></button>
                                            </form>
                                        <?php elseif ($isCurrent && $workflowRules['can_mark_everification_done']): ?>
                                            <form method="post" action="<?= e(url('/workflow/e-verification-done')) ?>" style="display:grid;gap:8px;">
                                                <?= \App\Core\Csrf::inputField() ?>
                                                <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                                                <input type="text" name="note" placeholder="Optional e-verification note" style="padding:10px 12px;border:1px solid #d8e1eb;border-radius:10px;">
                                                <button type="submit" class="button">Mark Done</button>
                                            </form>
                                        <?php elseif ($isCurrent && $workflowRules['can_advance']): ?>
                                            <form method="post" action="<?= e(url('/workflow/advance')) ?>">
                                                <?= \App\Core\Csrf::inputField() ?>
                                                <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                                                <button type="submit" class="button">Mark Done</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="chip"><?= $isCompleted ? 'Done' : ($isPending ? 'Waiting' : 'Current') ?></span>
                                        <?php endif; ?>

                                        <?php if ($isCompleted && $canReopenWorkflow): ?>
                                            <form method="post" action="<?= e(url('/workflow/reopen')) ?>" style="display:grid;gap:8px;">
                                                <?= \App\Core\Csrf::inputField() ?>
                                                <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                                                <input type="hidden" name="stage_code" value="<?= e($stageCode) ?>">
                                                <input type="text" name="reopen_reason" placeholder="Reason to re-open this milestone" style="padding:10px 12px;border:1px solid #d8e1eb;border-radius:10px;" required>
                                                <button type="submit" class="button button-secondary">Re-Open</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </details>
    </div>

    <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin-top:18px;">
            <h4 style="margin-top:0;">Closure Controls</h4>
            <div style="display:grid;gap:14px;">
                <?php if (\App\Core\Auth::can('workflow.close.procedural')): ?>
                    <form method="post" action="<?= e(url('/workflow/close-procedural')) ?>" style="display:grid;gap:10px;">
                        <?= \App\Core\Csrf::inputField() ?>
                        <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                        <input type="text" name="note" placeholder="Optional procedural closure note" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
                        <button type="submit" class="button" <?= $workflowRules['can_procedural_close'] ? '' : 'disabled' ?>>Complete Procedural Closure</button>
                    </form>
                <?php endif; ?>

                <?php if (\App\Core\Auth::can('workflow.close.accounting')): ?>
                    <form method="post" action="<?= e(url('/workflow/close-accounting')) ?>" style="display:grid;gap:10px;">
                        <?= \App\Core\Csrf::inputField() ?>
                        <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                        <input type="text" name="note" placeholder="Optional accounting closure note" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
                        <button type="submit" class="button" <?= $workflowRules['can_accounting_close'] ? '' : 'disabled' ?>>Complete Accounting Closure</button>
                    </form>
                <?php endif; ?>

                <?php if (\App\Core\Auth::can('workflow.close.final')): ?>
                    <form method="post" action="<?= e(url('/workflow/close-final')) ?>" style="display:grid;gap:10px;">
                        <?= \App\Core\Csrf::inputField() ?>
                        <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                        <input type="text" name="note" placeholder="Optional final closure note" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
                        <button type="submit" class="button" <?= $workflowRules['can_final_close'] ? '' : 'disabled' ?>>Complete Final Closure</button>
                    </form>
                <?php endif; ?>
            </div>
    </div>

    <?php if ($canViewExpenseSection || $canViewFinancialSnapshot): ?>
        <div class="grid" style="margin-top:18px;">
            <?php if ($canViewExpenseSection): ?>
                <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
                    <h4 style="margin-top:0;">Expenses / Disbursements</h4>
                    <?php if ($canManageExpenses): ?>
                        <form method="post" enctype="multipart/form-data" action="<?= e(url('/billing/disbursements')) ?>" style="display:grid;gap:10px;margin-bottom:14px;">
                            <?= \App\Core\Csrf::inputField() ?>
                            <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                            <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));">
                                <input type="date" name="expense_date" value="<?= e(date('Y-m-d')) ?>" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;" required>
                                <input type="text" name="expense_type" placeholder="Expense type" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;" required>
                                <input type="number" name="amount" step="0.01" placeholder="Amount" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;" required>
                                <input type="text" name="paid_to" placeholder="Paid to" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
                            </div>
                            <label style="display:flex;gap:8px;align-items:center;">
                                <input type="checkbox" name="is_recoverable" value="1">
                                <span>Recoverable from client</span>
                            </label>
                            <input type="file" name="proof_document" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
                            <textarea name="notes" rows="3" placeholder="Expense notes" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;resize:vertical;"></textarea>
                            <button type="submit" class="button">Add Expense</button>
                        </form>
                    <?php endif; ?>

                    <?php if ($disbursements === []): ?>
                        <p class="subtle">No expenses have been recorded yet.</p>
                    <?php else: ?>
                        <div style="overflow:auto;">
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
                </div>
            <?php endif; ?>

            <?php if ($canViewFinancialSnapshot): ?>
                <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
                    <h4 style="margin-top:0;">Financial and Closure Snapshot</h4>
                    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));">
                        <div class="metric"><strong>Service Orders Billing</strong><div style="margin-top:8px;">INR <?= e(number_format($invoiceTotal, 2)) ?></div></div>
                        <div class="metric"><strong>Payments Collected</strong><div style="margin-top:8px;">INR <?= e(number_format($paymentTotal, 2)) ?></div></div>
                        <div class="metric"><strong>Procedural Closure</strong><div style="margin-top:8px;"><?= e($order['procedural_closed_at'] ?: 'Pending') ?></div></div>
                        <div class="metric"><strong>Accounting Closure</strong><div style="margin-top:8px;"><?= e($order['accounting_closed_at'] ?: 'Pending') ?></div></div>
                        <div class="metric"><strong>Final Closure</strong><div style="margin-top:8px;"><?= e($order['final_closed_at'] ?: 'Pending') ?></div></div>
                        <div class="metric"><strong>Consultant Payment</strong><div style="margin-top:8px;"><?= (int) $order['is_consultant_payment_pending'] === 1 ? 'Pending' : 'Clear' ?></div></div>
                    </div>

                    <div style="margin-top:14px;display:grid;gap:12px;">
                        <?php foreach ($workflowClosures as $closure): ?>
                            <div style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;background:#fff;">
                                <strong><?= e($closure['closure_type']) ?></strong> | <?= e($closure['closure_status']) ?>
                                <div style="margin-top:6px;color:#64748b;">Closed At: <?= e($closure['closure_at'] ?: '-') ?></div>
                                <div style="margin-top:6px;color:#64748b;">Closed By: <?= e($closure['closed_by_name'] ?: '-') ?></div>
                                <div style="margin-top:6px;color:#64748b;">Block Reason: <?= e($closure['block_reason'] ?: '-') ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="grid" style="margin-top:18px;">
        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <h4 style="margin-top:0;">Assignments</h4>
            <p>CRM: <?= e($order['assigned_crm_name'] ?: 'Not assigned') ?></p>
            <p>Assistant CRM: <?= e($order['assistant_crm_name'] ?: 'Not assigned') ?></p>
            <p>Backend: <?= e($order['backend_name'] ?: 'Not assigned') ?></p>
            <p>DEO: <?= e($order['deo_name'] ?: 'Not assigned') ?></p>
        </div>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <h4 style="margin-top:0;">E-Verification Reminders</h4>
            <?php if ($workflowReminders === []): ?>
                <p class="subtle">No reminders scheduled yet.</p>
            <?php else: ?>
                <div style="display:grid;gap:12px;">
                    <?php foreach ($workflowReminders as $reminder): ?>
                        <div style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;background:#f8fafc;">
                            <div><strong><?= e($reminder['reminder_type']) ?></strong> | Day <?= e((string) $reminder['schedule_day_no']) ?> | <?= e($reminder['status']) ?></div>
                            <div style="margin-top:6px;color:#64748b;">Due: <?= e($reminder['due_at']) ?></div>
                            <?php if (\App\Core\Auth::can('workflow.followup.log')): ?>
                                <form method="post" action="<?= e(url('/workflow/follow-up')) ?>" style="display:grid;gap:8px;margin-top:10px;">
                                    <?= \App\Core\Csrf::inputField() ?>
                                    <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                                    <input type="hidden" name="reminder_id" value="<?= e($reminder['id']) ?>">
                                    <input type="text" name="follow_up_note" placeholder="Log CRM follow-up" style="padding:10px;border:1px solid #d8e1eb;border-radius:10px;">
                                    <button type="submit" class="button button-secondary">Log Follow-Up</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel" style="box-shadow:none;margin-top:18px;background:linear-gradient(180deg,#fff,#f6faf7);">
        <h4 style="margin-top:0;">Description and History</h4>
        <p style="white-space:pre-wrap;"><?= e($order['description'] ?: 'No description provided.') ?></p>
        <?php if ($workflowHistory !== []): ?>
            <div style="display:grid;gap:12px;margin-top:14px;">
                <?php foreach ($workflowHistory as $history): ?>
                    <div style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;background:#fff;">
                        <div><strong><?= e($history['stage_name']) ?></strong></div>
                        <div style="margin-top:6px;color:#64748b;">Completed At: <?= e($history['entered_at']) ?></div>
                        <div style="margin-top:6px;color:#64748b;">Completed By: <?= e($history['entered_by_name'] ?: '-') ?></div>
                        <div style="margin-top:6px;color:#64748b;">Note: <?= e($history['remarks'] ?: '-') ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

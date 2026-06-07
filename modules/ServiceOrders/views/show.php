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
        <a href="<?= e(url('/service-orders')) ?>" class="button button-secondary">Back to Register</a>
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
            <strong>Current Workflow</strong>
            <div style="margin-top:8px;"><?= e($order['service_type_name']) ?></div>
            <div style="margin-top:4px;color:#62748a;">Stage: <?= e(str_replace('_', ' ', $order['current_stage_code'])) ?></div>
        </div>
        <div class="metric">
            <strong>Work Period</strong>
            <div style="margin-top:8px;"><?= e($order['work_basis'] ?: '-') ?></div>
            <div style="margin-top:4px;color:#62748a;"><?= e($order['period_label'] ?: '-') ?></div>
            <div style="margin-top:4px;color:#62748a;">
                <?php if (!empty($order['assessment_year'])): ?>
                    Assessment Year: <?= e($order['assessment_year']) ?>
                <?php elseif (!empty($order['compliance_subtype'])): ?>
                    Type: <?= e($order['compliance_subtype']) ?>
                <?php else: ?>
                    -
                <?php endif; ?>
            </div>
        </div>
        <div class="metric">
            <strong>SLA / Lock</strong>
            <div style="margin-top:8px;"><?= e($order['sla_due_at'] ?: '-') ?></div>
            <div style="margin-top:4px;color:#62748a;"><?= (int) $order['is_locked'] === 1 ? 'Locked after final closure' : 'Open for workflow actions' ?></div>
        </div>
    </div>

    <div class="panel" style="box-shadow:none;margin-top:18px;background:linear-gradient(180deg,#fff,#f6faf7);">
        <h4 style="margin-top:0;">Workflow Timeline</h4>
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:12px;">
            <?php foreach ($workflowStages as $stage): ?>
                <?php
                $isCurrent = $order['current_stage_code'] === $stage['stage_code'];
                $isDone = false;
                foreach ($workflowHistory as $historyRow) {
                    if ($historyRow['stage_code'] === $stage['stage_code']) {
                        $isDone = true;
                        break;
                    }
                }
                ?>
                <div style="padding:14px;border-radius:14px;border:1px solid <?= $isCurrent ? '#14b8a6' : '#d8e1eb' ?>;background:<?= $isCurrent ? '#ecfeff' : ($isDone ? '#f8fafc' : '#fff') ?>;">
                    <strong><?= e($stage['stage_name']) ?></strong>
                    <div style="margin-top:6px;font-size:0.88rem;color:#64748b;"><?= e($stage['stage_code']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="grid" style="margin-top:18px;">
        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <h4 style="margin-top:0;">Workflow Actions</h4>
            <div style="display:grid;gap:14px;">
                <?php if ($workflowRules['can_advance']): ?>
                    <form method="post" action="<?= e(url('/workflow/advance')) ?>" style="display:grid;gap:10px;">
                        <?= \App\Core\Csrf::inputField() ?>
                        <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                        <button type="submit" class="button">Advance Milestone</button>
                    </form>
                <?php endif; ?>

                <?php if ($workflowRules['can_record_payment']): ?>
                    <form method="post" action="<?= e(url('/workflow/payment')) ?>" style="display:grid;gap:10px;">
                        <?= \App\Core\Csrf::inputField() ?>
                        <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                        <input type="text" name="payment_reference_no" placeholder="Tax payment reference / challan no" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;" required>
                        <button type="submit" class="button">Record Tax Payment</button>
                    </form>
                <?php endif; ?>

                <?php if ($workflowRules['can_capture_ack']): ?>
                    <form method="post" action="<?= e(url('/workflow/acknowledgement')) ?>" style="display:grid;gap:10px;">
                        <?= \App\Core\Csrf::inputField() ?>
                        <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                        <input type="text" name="acknowledgement_no" placeholder="ARN / acknowledgement number" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;" required>
                        <button type="submit" class="button">Capture ARN / Acknowledgement</button>
                    </form>
                <?php endif; ?>

                <?php if ($workflowRules['can_mark_everification_done']): ?>
                    <form method="post" action="<?= e(url('/workflow/e-verification-done')) ?>" style="display:grid;gap:10px;">
                        <?= \App\Core\Csrf::inputField() ?>
                        <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                        <input type="text" name="note" placeholder="Optional e-verification note" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
                        <button type="submit" class="button">Mark E-Verification Done</button>
                    </form>
                <?php endif; ?>

                <div style="padding:14px;border-radius:12px;background:#f8fafc;border:1px solid #d8e1eb;">
                    <div><strong>Payment Ref:</strong> <?= e($order['payment_reference_no'] ?: '-') ?></div>
                    <div style="margin-top:6px;"><strong>ARN / Ack:</strong> <?= e($order['acknowledgement_no'] ?: '-') ?></div>
                    <div style="margin-top:6px;"><strong>E-Verification Due:</strong> <?= e($order['e_verification_due_date'] ?: '-') ?></div>
                    <div style="margin-top:6px;"><strong>Overdue:</strong> <?= (int) $order['is_overdue'] === 1 ? 'Yes' : 'No' ?></div>
                </div>
            </div>
        </div>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <h4 style="margin-top:0;">Closure Controls</h4>
            <div style="display:grid;gap:14px;">
                <form method="post" action="<?= e(url('/workflow/close-procedural')) ?>" style="display:grid;gap:10px;">
                    <?= \App\Core\Csrf::inputField() ?>
                    <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                    <input type="text" name="note" placeholder="Optional procedural closure note" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
                    <button type="submit" class="button" <?= $workflowRules['can_procedural_close'] ? '' : 'disabled' ?>>Complete Procedural Closure</button>
                </form>

                <form method="post" action="<?= e(url('/workflow/close-accounting')) ?>" style="display:grid;gap:10px;">
                    <?= \App\Core\Csrf::inputField() ?>
                    <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                    <input type="text" name="note" placeholder="Optional accounting closure note" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
                    <button type="submit" class="button" <?= $workflowRules['can_accounting_close'] ? '' : 'disabled' ?>>Complete Accounting Closure</button>
                </form>

                <form method="post" action="<?= e(url('/workflow/close-final')) ?>" style="display:grid;gap:10px;">
                    <?= \App\Core\Csrf::inputField() ?>
                    <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                    <input type="text" name="note" placeholder="Optional final closure note" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
                    <button type="submit" class="button" <?= $workflowRules['can_final_close'] ? '' : 'disabled' ?>>Complete Final Closure</button>
                </form>

                <div style="padding:14px;border-radius:12px;background:#f8fafc;border:1px solid #d8e1eb;">
                    <div><strong>Client Paid:</strong> <?= (int) $order['is_client_paid'] === 1 ? 'Yes' : 'No' ?></div>
                    <div style="margin-top:6px;"><strong>Consultant Payment Pending:</strong> <?= (int) $order['is_consultant_payment_pending'] === 1 ? 'Yes' : 'No' ?></div>
                    <div style="margin-top:6px;"><strong>Procedural Closed:</strong> <?= e($order['procedural_closed_at'] ?: 'Pending') ?></div>
                    <div style="margin-top:6px;"><strong>Accounting Closed:</strong> <?= e($order['accounting_closed_at'] ?: 'Pending') ?></div>
                    <div style="margin-top:6px;"><strong>Final Closed:</strong> <?= e($order['final_closed_at'] ?: 'Pending') ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid" style="margin-top:18px;">
        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <h4 style="margin-top:0;">E-Verification Reminders</h4>
            <?php if ($workflowReminders === []): ?>
                <p style="color:#64748b;">No reminders scheduled yet.</p>
            <?php else: ?>
                <div style="display:grid;gap:12px;">
                    <?php foreach ($workflowReminders as $reminder): ?>
                        <div style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;background:#f8fafc;">
                            <div><strong><?= e($reminder['reminder_type']) ?></strong> | Day <?= e((string) $reminder['schedule_day_no']) ?> | <?= e($reminder['status']) ?></div>
                            <div style="margin-top:6px;color:#64748b;">Due: <?= e($reminder['due_at']) ?></div>
                            <form method="post" action="<?= e(url('/workflow/follow-up')) ?>" style="display:grid;gap:8px;margin-top:10px;">
                                <?= \App\Core\Csrf::inputField() ?>
                                <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                                <input type="hidden" name="reminder_id" value="<?= e($reminder['id']) ?>">
                                <input type="text" name="follow_up_note" placeholder="Log CRM follow-up" style="padding:10px;border:1px solid #d8e1eb;border-radius:10px;">
                                <button type="submit" class="button button-secondary">Log Follow-Up</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <h4 style="margin-top:0;">Closure Status</h4>
            <div style="display:grid;gap:12px;">
                <?php foreach ($workflowClosures as $closure): ?>
                    <div style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;background:#fff;">
                        <div><strong><?= e($closure['closure_type']) ?></strong> | <?= e($closure['closure_status']) ?></div>
                        <div style="margin-top:6px;color:#64748b;">Closed At: <?= e($closure['closure_at'] ?: '-') ?></div>
                        <div style="margin-top:6px;color:#64748b;">Block Reason: <?= e($closure['block_reason'] ?: '-') ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="grid" style="margin-top:18px;">
        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <h4 style="margin-top:0;">Assignments</h4>
            <p>CRM: <?= e($order['assigned_crm_name'] ?: 'Not assigned') ?></p>
            <p>Assistant CRM: <?= e($order['assistant_crm_name'] ?: 'Not assigned') ?></p>
            <p>Backend: <?= e($order['backend_name'] ?: 'Not assigned') ?></p>
            <p>DEO: <?= e($order['deo_name'] ?: 'Not assigned') ?></p>
        </div>
        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <h4 style="margin-top:0;">History</h4>
            <?php if ($workflowHistory === []): ?>
                <p style="color:#64748b;">No workflow history yet.</p>
            <?php else: ?>
                <div style="display:grid;gap:12px;">
                    <?php foreach ($workflowHistory as $history): ?>
                        <div style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;background:#fff;">
                            <div><strong><?= e($history['stage_name']) ?></strong></div>
                            <div style="margin-top:6px;color:#64748b;">Entered: <?= e($history['entered_at']) ?></div>
                            <div style="margin-top:6px;color:#64748b;">Exited: <?= e($history['exited_at'] ?: '-') ?></div>
                            <div style="margin-top:6px;color:#64748b;">Note: <?= e($history['remarks'] ?: '-') ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel" style="box-shadow:none;margin-top:18px;background:linear-gradient(180deg,#fff,#f6faf7);">
        <h4 style="margin-top:0;">Description</h4>
        <p style="white-space:pre-wrap;"><?= e($order['description'] ?: 'No description provided.') ?></p>
    </div>
</section>

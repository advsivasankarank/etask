<?php $order = $dashboard['order']; ?>
<section class="panel">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <div>
            <div class="eyebrow">Consultant Workspace</div>
            <h3 style="margin:0 0 6px;"><?= e($order['so_no']) ?></h3>
            <div class="subtle"><?= e($order['client_name']) ?> | <?= e($order['service_type_name']) ?></div>
        </div>
        <a href="<?= e(url('/consultants')) ?>" class="button button-secondary">Back to Consultant Register</a>
    </div>

    <div class="grid">
        <div class="metric">
            <strong>Outstanding Consultant Amount</strong>
            <div style="margin-top:8px;">INR <?= e(number_format((float) $dashboard['outstanding_amount'], 2)) ?></div>
        </div>
        <div class="metric">
            <strong>Final Closure Dependency</strong>
            <div style="margin-top:8px;"><?= (int) $order['is_consultant_payment_pending'] === 1 ? 'Pending consultant settlement' : 'Clear' ?></div>
        </div>
    </div>

    <div class="grid" style="margin-top:18px;">
        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6fafb);">
            <h4 style="margin-top:0;">Assign Consultant</h4>
            <form method="post" action="<?= e(url('/consultants/assign')) ?>" style="display:grid;gap:10px;">
                <?= \App\Core\Csrf::inputField() ?>
                <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                <select name="consultant_user_id" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;" required>
                    <option value="">Select consultant</option>
                    <?php foreach ($dashboard['consultants'] as $consultant): ?>
                        <option value="<?= e($consultant['id']) ?>"><?= e($consultant['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="internal_reviewer_id" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
                    <option value="">Select internal reviewer</option>
                    <?php foreach ($dashboard['reviewers'] as $reviewer): ?>
                        <option value="<?= e($reviewer['id']) ?>"><?= e($reviewer['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <textarea name="remarks" rows="3" placeholder="Assignment remarks" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;resize:vertical;"></textarea>
                <button type="submit" class="button">Assign Consultant</button>
            </form>
        </div>
    </div>

    <?php foreach ($dashboard['assignments'] as $block): ?>
        <?php $assignment = $block['assignment']; ?>
        <div class="grid" style="margin-top:18px;">
            <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6fafb);">
                <h4 style="margin-top:0;"><?= e($assignment['consultant_name']) ?> | <?= e(label_case((string) $assignment['status'])) ?></h4>
                <p>Reviewer: <?= e($assignment['reviewer_name'] ?: 'Not assigned') ?></p>
                <p>Assigned at: <?= e($assignment['assigned_at']) ?></p>

                <form method="post" enctype="multipart/form-data" action="<?= e(url('/consultants/deliverables')) ?>" style="display:grid;gap:10px;margin-top:14px;">
                    <?= \App\Core\Csrf::inputField() ?>
                    <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                    <input type="hidden" name="consultant_assignment_id" value="<?= e($assignment['id']) ?>">
                    <input type="file" name="deliverable" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
                    <button type="submit" class="button">Upload Deliverable</button>
                </form>

                <form method="post" enctype="multipart/form-data" action="<?= e(url('/consultants/bills')) ?>" style="display:grid;gap:10px;margin-top:14px;">
                    <?= \App\Core\Csrf::inputField() ?>
                    <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                    <input type="hidden" name="consultant_assignment_id" value="<?= e($assignment['id']) ?>">
                    <input type="text" name="bill_no" placeholder="Bill number" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;" required>
                    <input type="date" name="bill_date" value="<?= e(date('Y-m-d')) ?>" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
                    <input type="number" step="0.01" name="amount" placeholder="Base amount" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;" required>
                    <input type="number" step="0.01" name="tax_amount" placeholder="Tax amount" value="0.00" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
                    <input type="number" step="0.01" name="total_amount" placeholder="Total amount" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;" required>
                    <input type="file" name="bill_document" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
                    <button type="submit" class="button">Add Consultant Bill</button>
                </form>
            </div>

            <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6fafb);">
                <h4 style="margin-top:0;">Deliverables</h4>
                <?php if ($block['deliverables'] === []): ?>
                    <p style="color:#64748b;">No deliverables uploaded.</p>
                <?php else: ?>
                    <div style="display:grid;gap:10px;">
                        <?php foreach ($block['deliverables'] as $deliverable): ?>
                            <div style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;background:#fff;">
                                <div><strong><?= e($deliverable['document_name']) ?></strong> | <?= e(label_case((string) $deliverable['review_status'])) ?></div>
                                <div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap;">
                                    <a href="<?= e(url('/documents/show?id=' . $deliverable['document_id'])) ?>" class="button button-secondary">Open Deliverable</a>
                                    <a href="<?= e(url('/documents/' . $deliverable['document_id'] . '/download')) ?>" class="button button-secondary">Download Deliverable</a>
                                </div>
                                <form method="post" action="<?= e(url('/consultants/deliverables/review')) ?>" style="display:grid;gap:8px;margin-top:10px;">
                                    <?= \App\Core\Csrf::inputField() ?>
                                    <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                                    <input type="hidden" name="consultant_assignment_id" value="<?= e($assignment['id']) ?>">
                                    <input type="hidden" name="deliverable_id" value="<?= e($deliverable['id']) ?>">
                                    <select name="review_status" style="padding:10px;border:1px solid #d8e1eb;border-radius:10px;">
                                        <option value="APPROVED">Approve</option>
                                        <option value="REJECTED">Reject</option>
                                    </select>
                                    <input type="text" name="review_notes" placeholder="Review notes" style="padding:10px;border:1px solid #d8e1eb;border-radius:10px;">
                                    <button type="submit" class="button button-secondary">Review Deliverable</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid" style="margin-top:18px;">
            <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6fafb);">
                <h4 style="margin-top:0;">Consultant Bills</h4>
                <?php if ($block['bills'] === []): ?>
                    <p style="color:#64748b;">No bills added yet.</p>
                <?php else: ?>
                    <div style="display:grid;gap:10px;">
                        <?php foreach ($block['bills'] as $bill): ?>
                            <div style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;background:#fff;">
                                <div><strong><?= e($bill['bill_no']) ?></strong> | <?= e(label_case((string) $bill['review_status'])) ?> | <?= e(money_inr($bill['total_amount'])) ?></div>
                                <?php if (!empty($bill['document_id'])): ?>
                                    <div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap;">
                                        <a href="<?= e(url('/documents/show?id=' . $bill['document_id'])) ?>" class="button button-secondary">Open Bill</a>
                                        <a href="<?= e(url('/documents/' . $bill['document_id'] . '/download')) ?>" class="button button-secondary">Download Bill</a>
                                    </div>
                                <?php endif; ?>
                                <form method="post" action="<?= e(url('/consultants/bills/review')) ?>" style="display:grid;gap:8px;margin-top:10px;">
                                    <?= \App\Core\Csrf::inputField() ?>
                                    <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                                    <input type="hidden" name="consultant_bill_id" value="<?= e($bill['id']) ?>">
                                    <select name="review_status" style="padding:10px;border:1px solid #d8e1eb;border-radius:10px;">
                                        <option value="APPROVED">Approve</option>
                                        <option value="REJECTED">Reject</option>
                                    </select>
                                    <input type="text" name="review_notes" placeholder="Bill review notes" style="padding:10px;border:1px solid #d8e1eb;border-radius:10px;">
                                    <button type="submit" class="button button-secondary">Review Bill</button>
                                </form>

                                <form method="post" action="<?= e(url('/consultants/payments')) ?>" style="display:grid;gap:8px;margin-top:10px;">
                                    <?= \App\Core\Csrf::inputField() ?>
                                    <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                                    <input type="hidden" name="consultant_bill_id" value="<?= e($bill['id']) ?>">
                                    <input type="date" name="payment_date" value="<?= e(date('Y-m-d')) ?>" style="padding:10px;border:1px solid #d8e1eb;border-radius:10px;">
                                    <input type="number" step="0.01" name="amount" placeholder="Payment amount" style="padding:10px;border:1px solid #d8e1eb;border-radius:10px;">
                                    <select name="payment_mode" style="padding:10px;border:1px solid #d8e1eb;border-radius:10px;">
                                        <option value="BANK_TRANSFER">Bank Transfer</option>
                                        <option value="UPI">UPI</option>
                                        <option value="CHEQUE">Cheque</option>
                                        <option value="CASH">Cash</option>
                                    </select>
                                    <input type="text" name="reference_no" placeholder="Reference no" style="padding:10px;border:1px solid #d8e1eb;border-radius:10px;">
                                    <button type="submit" class="button">Record Consultant Payment</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6fafb);">
                <h4 style="margin-top:0;">Consultant Payments</h4>
                <?php if ($block['payments'] === []): ?>
                    <p style="color:#64748b;">No consultant payments recorded.</p>
                <?php else: ?>
                    <div style="display:grid;gap:10px;">
                        <?php foreach ($block['payments'] as $payment): ?>
                            <div style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;background:#fff;">
                                <div><strong><?= e($payment['bill_no']) ?></strong> | INR <?= e(number_format((float) $payment['amount'], 2)) ?></div>
                                <div style="margin-top:6px;color:#64748b;"><?= e($payment['payment_mode']) ?> | <?= e($payment['payment_date']) ?> | Ref: <?= e($payment['reference_no'] ?: '-') ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</section>

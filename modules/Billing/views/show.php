<?php $order = $billing['order']; ?>
<section class="panel">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <div>
            <div class="eyebrow">Billing Workspace</div>
            <h3 style="margin:0 0 6px;"><?= e($order['so_no']) ?></h3>
            <div class="subtle"><?= e($order['client_name']) ?> | <?= e($order['service_type_name']) ?></div>
        </div>
        <a href="<?= e(url('/billing')) ?>" class="button button-secondary">Back to Billing Register</a>
    </div>

    <div class="grid">
        <div class="metric">
            <strong>Advance Available</strong>
            <div style="margin-top:8px;">INR <?= e(number_format((float) $billing['advance_balance'], 2)) ?></div>
        </div>
        <div class="metric">
            <strong>Client Paid</strong>
            <div style="margin-top:8px;"><?= (int) $order['is_client_paid'] === 1 ? 'Yes' : 'No' ?></div>
        </div>
        <div class="metric">
            <strong>Razorpay</strong>
            <div style="margin-top:8px;"><?= !empty($billing['razorpay']['enabled']) ? 'Configured' : 'Ready for configuration' ?></div>
        </div>
        <div class="metric">
            <strong>Company</strong>
            <div style="margin-top:8px;"><?= e($order['company_name']) ?></div>
        </div>
    </div>

    <div class="grid" style="margin-top:18px;">
        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6fafb);">
            <h4 style="margin-top:0;">Add Disbursement</h4>
            <form method="post" action="<?= e(url('/billing/disbursements')) ?>" style="display:grid;gap:10px;">
                <?= \App\Core\Csrf::inputField() ?>
                <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                <input type="date" name="expense_date" value="<?= e(date('Y-m-d')) ?>" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
                <input type="text" name="expense_type" placeholder="Expense type" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;" required>
                <input type="number" step="0.01" name="amount" placeholder="Amount" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;" required>
                <input type="text" name="paid_to" placeholder="Paid to" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
                <label style="display:flex;gap:8px;align-items:center;">
                    <input type="checkbox" name="is_recoverable" value="1" checked> Recoverable from client
                </label>
                <textarea name="notes" rows="3" placeholder="Notes" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;resize:vertical;"></textarea>
                <button type="submit" class="button">Add Disbursement</button>
            </form>
        </div>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6fafb);">
            <h4 style="margin-top:0;">Create Invoice</h4>
            <form method="post" action="<?= e(url('/billing/invoices')) ?>" style="display:grid;gap:10px;">
                <?= \App\Core\Csrf::inputField() ?>
                <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                <select name="invoice_type" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
                    <option value="ADVANCE">Advance Invoice</option>
                    <option value="FINAL" selected>Final Invoice</option>
                </select>
                <input type="date" name="invoice_date" value="<?= e(date('Y-m-d')) ?>" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
                <input type="date" name="due_date" value="<?= e(date('Y-m-d', strtotime('+7 days'))) ?>" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
                <input type="number" step="0.01" name="service_fee" placeholder="Service fee" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;" required>
                <input type="number" step="0.01" name="tax_total" placeholder="Tax total" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;" value="0.00">
                <textarea name="notes" rows="3" placeholder="Invoice notes" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;resize:vertical;"></textarea>
                <button type="submit" class="button">Create Invoice</button>
            </form>
        </div>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6fafb);">
            <h4 style="margin-top:0;">Record Payment / Advance</h4>
            <form method="post" action="<?= e(url('/billing/payments')) ?>" style="display:grid;gap:10px;">
                <?= \App\Core\Csrf::inputField() ?>
                <input type="hidden" name="service_order_id" value="<?= e($order['id']) ?>">
                <select name="transaction_type" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
                    <option value="INVOICE_PAYMENT">Invoice Payment</option>
                    <option value="ADVANCE">Advance</option>
                </select>
                <select name="payment_mode" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
                    <option value="RAZORPAY">Razorpay</option>
                    <option value="BANK_TRANSFER" selected>Bank Transfer</option>
                    <option value="UPI">UPI</option>
                    <option value="CHEQUE">Cheque</option>
                    <option value="CASH">Cash</option>
                </select>
                <input type="date" name="payment_date" value="<?= e(date('Y-m-d')) ?>" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
                <input type="number" step="0.01" name="amount" placeholder="Payment amount" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;" required>
                <input type="text" name="reference_no" placeholder="Reference / UTR / Razorpay ref" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
                <textarea name="notes" rows="3" placeholder="Payment notes" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;resize:vertical;"></textarea>
                <button type="submit" class="button">Record Payment and Generate Receipt</button>
            </form>
        </div>
    </div>

    <div class="grid" style="margin-top:18px;">
        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6fafb);">
            <h4 style="margin-top:0;">Disbursements</h4>
            <?php if ($billing['disbursements'] === []): ?>
                <p style="color:#64748b;">No disbursements recorded.</p>
            <?php else: ?>
                <div style="display:grid;gap:10px;">
                    <?php foreach ($billing['disbursements'] as $item): ?>
                        <div style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;background:#fff;">
                            <div><strong><?= e($item['expense_type']) ?></strong> | INR <?= e(number_format((float) $item['amount'], 2)) ?></div>
                            <div style="margin-top:6px;color:#64748b;">Recoverable: <?= (int) $item['is_recoverable'] === 1 ? 'Yes' : 'No' ?> | Invoiced: <?= e($item['invoiced_at'] ?: 'Pending') ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6fafb);">
            <h4 style="margin-top:0;">Invoices</h4>
            <?php if ($billing['invoices'] === []): ?>
                <p style="color:#64748b;">No invoices created yet.</p>
            <?php else: ?>
                <div style="display:grid;gap:10px;">
                    <?php foreach ($billing['invoices'] as $invoice): ?>
                        <div style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;background:#fff;">
                            <div><strong><?= e($invoice['invoice_no']) ?></strong> | <?= e($invoice['invoice_type']) ?></div>
                            <div style="margin-top:6px;color:#64748b;">Gross: INR <?= e(number_format((float) $invoice['gross_total'], 2)) ?> | Advance adjusted: INR <?= e(number_format((float) $invoice['advance_adjusted'], 2)) ?></div>
                            <div style="margin-top:6px;color:#64748b;">Net payable: INR <?= e(number_format((float) $invoice['net_payable'], 2)) ?> | Status: <?= e($invoice['payment_status']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6fafb);">
            <h4 style="margin-top:0;">Payments / Receipts</h4>
            <?php if ($billing['payments'] === []): ?>
                <p style="color:#64748b;">No payments recorded yet.</p>
            <?php else: ?>
                <div style="display:grid;gap:10px;">
                    <?php foreach ($billing['payments'] as $payment): ?>
                        <div style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;background:#fff;">
                            <div><strong><?= e($payment['transaction_type']) ?></strong> | INR <?= e(number_format((float) $payment['amount'], 2)) ?></div>
                            <div style="margin-top:6px;color:#64748b;">Mode: <?= e($payment['payment_mode']) ?> | Status: <?= e($payment['status']) ?></div>
                            <div style="margin-top:6px;color:#64748b;">Receipt: <?= e($payment['receipt_no'] ?: 'Pending') ?> | Ref: <?= e($payment['reference_no'] ?: '-') ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

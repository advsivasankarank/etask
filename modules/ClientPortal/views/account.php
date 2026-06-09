<section class="panel">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <div>
            <div class="eyebrow">Client Account</div>
            <h3 style="margin:0 0 6px;"><?= e($client['legal_name']) ?></h3>
            <div class="subtle">Portal workspace for invoices, payments, service orders, and notifications.</div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="<?= e(url('/client-portal/pso')) ?>" class="button button-secondary">Open PSOs</a>
            <a href="<?= e(url('/service-orders')) ?>" class="button button-secondary">Open Service Orders</a>
        </div>
    </div>

    <div class="grid">
        <div class="metric">
            <strong>PAN / TAN</strong>
            <div style="margin-top:8px;">PAN: <?= e($client['pan'] ?: '-') ?></div>
            <div style="margin-top:4px;color:#62748a;">TAN: <?= e($client['tan'] ?: '-') ?></div>
        </div>
        <div class="metric">
            <strong>GST / Aadhaar</strong>
            <div style="margin-top:8px;">GSTIN: <?= e($client['gstin'] ?: '-') ?></div>
            <div style="margin-top:4px;color:#62748a;">Aadhaar: <?= e(!empty($client['aadhaar_last4']) ? 'XXXXXXXX' . $client['aadhaar_last4'] : '-') ?></div>
        </div>
        <div class="metric">
            <strong>Primary Contact</strong>
            <div style="margin-top:8px;"><?= e($contact['contact_name'] ?? '-') ?></div>
            <div style="margin-top:4px;color:#62748a;"><?= e($contact['email'] ?? '-') ?></div>
            <div style="margin-top:4px;color:#62748a;"><?= e($contact['mobile'] ?? '-') ?></div>
        </div>
        <div class="metric">
            <strong>Alerts</strong>
            <div style="margin-top:8px;"><?= e((string) count($notifications ?? [])) ?> notification(s)</div>
        </div>
    </div>

    <div class="grid" style="margin-top:18px;">
        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6fafb);">
            <h4 style="margin-top:0;">Invoices</h4>
            <?php if (($invoices ?? []) === []): ?>
                <p class="subtle">No invoices are available yet.</p>
            <?php else: ?>
                <div style="display:grid;gap:12px;">
                    <?php foreach ($invoices as $invoice): ?>
                        <div style="padding:14px;border:1px solid #d8e1eb;border-radius:14px;background:#fff;">
                            <div><strong><?= e($invoice['invoice_no']) ?></strong> | <?= e($invoice['payment_status']) ?></div>
                            <div style="margin-top:6px;color:#62748a;"><?= e($invoice['so_no']) ?> | <?= e($invoice['service_type_name']) ?> | <?= e($invoice['company_name']) ?></div>
                            <div style="margin-top:6px;color:#62748a;">Date: <?= e($invoice['invoice_date']) ?> | Due: <?= e($invoice['due_date'] ?: '-') ?></div>
                            <div style="margin-top:6px;color:#62748a;">Net: INR <?= e(number_format((float) $invoice['net_payable'], 2)) ?> | Outstanding: INR <?= e(number_format((float) $invoice['outstanding_amount'], 2)) ?></div>
                            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px;">
                                <a href="<?= e(url('/billing/invoice?id=' . $invoice['id'])) ?>" class="button button-secondary">Open Invoice</a>
                            </div>
                            <?php if ((float) $invoice['outstanding_amount'] > 0): ?>
                                <form method="post" action="<?= e(url('/client-portal/payments')) ?>" style="display:grid;gap:8px;margin-top:10px;">
                                    <?= \App\Core\Csrf::inputField() ?>
                                    <input type="hidden" name="invoice_id" value="<?= e((string) $invoice['id']) ?>">
                                    <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));">
                                        <input type="number" step="0.01" name="amount" value="<?= e(number_format((float) $invoice['outstanding_amount'], 2, '.', '')) ?>" placeholder="Payment amount">
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
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6fafb);">
            <h4 style="margin-top:0;">Payments and Receipts</h4>
            <?php if (($payments ?? []) === []): ?>
                <p class="subtle">No payments have been recorded yet.</p>
            <?php else: ?>
                <div style="display:grid;gap:12px;">
                    <?php foreach ($payments as $payment): ?>
                        <div style="padding:14px;border:1px solid #d8e1eb;border-radius:14px;background:#fff;">
                            <div><strong><?= e($payment['transaction_type']) ?></strong> | INR <?= e(number_format((float) $payment['amount'], 2)) ?></div>
                            <div style="margin-top:6px;color:#62748a;"><?= e($payment['so_no'] ?: '-') ?> | <?= e($payment['payment_mode']) ?> | <?= e($payment['payment_date']) ?></div>
                            <div style="margin-top:6px;color:#62748a;">Receipt: <?= e($payment['receipt_no'] ?: '-') ?> | Ref: <?= e($payment['reference_no'] ?: '-') ?></div>
                            <?php if (!empty($payment['receipt_id'])): ?>
                                <div style="margin-top:10px;">
                                    <a href="<?= e(url('/billing/receipt?id=' . $payment['receipt_id'])) ?>" class="button button-secondary">Open Receipt</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="panel" style="box-shadow:none;margin-top:18px;background:linear-gradient(180deg,#fff,#f6fafb);">
        <h4 style="margin-top:0;">Notifications</h4>
        <?php if (($notifications ?? []) === []): ?>
            <p class="subtle">No notifications are available right now.</p>
        <?php else: ?>
            <div style="display:grid;gap:10px;">
                <?php foreach ($notifications as $notification): ?>
                    <div class="data-card" style="padding:14px;">
                        <div class="eyebrow"><?= e($notification['linked_module'] ?: 'GENERAL') ?></div>
                        <strong><?= e($notification['subject'] ?: 'Notification') ?></strong>
                        <div class="subtle" style="margin-top:6px;"><?= e($notification['message']) ?></div>
                        <div class="stat-line" style="margin-top:8px;">
                            <span>Status</span>
                            <strong><?= e($notification['delivery_status']) ?></strong>
                        </div>
                        <div class="stat-line">
                            <span>Created</span>
                            <strong><?= e($notification['created_at']) ?></strong>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

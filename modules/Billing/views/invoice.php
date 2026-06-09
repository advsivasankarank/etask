<section class="panel">
    <div class="toolbar">
        <div>
            <div class="eyebrow">Invoice</div>
            <h3 style="margin:0 0 6px;"><?= e($invoice['invoice_no']) ?></h3>
            <div class="subtle"><?= e($invoice['client_name']) ?> | <?= e($invoice['so_no']) ?> | <?= e($invoice['service_type_name']) ?></div>
        </div>
        <a href="<?= e(\App\Core\Auth::isPortalUser() ? url('/client-portal/account') : url('/billing/show?service_order_id=' . $invoice['service_order_id'])) ?>" class="button button-secondary">Back</a>
    </div>

    <div class="grid">
        <div class="metric">
            <strong>Company / FY</strong>
            <div style="margin-top:8px;"><?= e($invoice['company_name']) ?></div>
            <div style="margin-top:4px;color:#62748a;"><?= e($invoice['financial_year_label']) ?></div>
        </div>
        <div class="metric">
            <strong>Date / Due</strong>
            <div style="margin-top:8px;"><?= e($invoice['invoice_date']) ?></div>
            <div style="margin-top:4px;color:#62748a;">Due: <?= e($invoice['due_date'] ?: '-') ?></div>
        </div>
        <div class="metric">
            <strong>Status</strong>
            <div style="margin-top:8px;"><?= e($invoice['payment_status']) ?></div>
            <div style="margin-top:4px;color:#62748a;"><?= e($invoice['invoice_type']) ?></div>
        </div>
        <div class="metric">
            <strong>Net Payable</strong>
            <div style="margin-top:8px;">INR <?= e(number_format((float) $invoice['net_payable'], 2)) ?></div>
        </div>
    </div>

    <div class="panel" style="box-shadow:none;margin-top:18px;background:#fff;">
        <h4 style="margin-top:0;">Invoice Lines</h4>
        <?php if (($items ?? []) === []): ?>
            <p class="subtle">No invoice lines are available.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= e($item['description']) ?></td>
                            <td><?= e($item['line_type']) ?></td>
                            <td><?= e((string) $item['quantity']) ?></td>
                            <td>INR <?= e(number_format((float) $item['unit_price'], 2)) ?></td>
                            <td>INR <?= e(number_format((float) $item['line_total'], 2)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</section>

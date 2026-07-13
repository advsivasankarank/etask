<section class="panel">
    <div class="toolbar">
        <div>
            <div class="eyebrow">Receipt</div>
            <h3 style="margin:0 0 6px;"><?= e($receipt['receipt_no']) ?></h3>
            <div class="subtle"><?= e($receipt['client_name']) ?><?php if (!empty($receipt['so_no'])): ?> | <?= e($receipt['so_no']) ?><?php endif; ?></div>
        </div>
        <a href="<?= e(\App\Core\Auth::isPortalUser() ? url('/client-portal/account') : url('/billing/show?service_order_id=' . ($receipt['service_order_id'] ?? 0))) ?>" class="button button-secondary">Back</a>
    </div>

    <div class="grid">
        <div class="metric">
            <strong>Company / FY</strong>
            <div style="margin-top:8px;"><?= e($receipt['company_name']) ?></div>
            <div style="margin-top:4px;color:#62748a;"><?= e($receipt['financial_year_label']) ?></div>
        </div>
        <div class="metric">
            <strong>Receipt Date</strong>
            <div style="margin-top:8px;"><?= e($receipt['receipt_date']) ?></div>
        </div>
        <div class="metric">
            <strong>Payment Mode</strong>
            <div style="margin-top:8px;"><?= e($receipt['payment_mode'] ?: '-') ?></div>
            <div style="margin-top:4px;color:#62748a;"><?= e(label_case((string) ($receipt['transaction_type'] ?: '-'))) ?></div>
        </div>
        <div class="metric">
            <strong>Amount</strong>
            <div style="margin-top:8px;">INR <?= e(number_format((float) $receipt['receipt_amount'], 2)) ?></div>
            <div style="margin-top:4px;color:#62748a;">Ref: <?= e($receipt['reference_no'] ?: '-') ?></div>
        </div>
    </div>

    <div class="panel" style="box-shadow:none;margin-top:18px;background:#fff;">
        <h4 style="margin-top:0;">Receipt Allocation</h4>
        <?php if (($items ?? []) === []): ?>
            <p class="subtle">No allocation lines are available.</p>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Allocated Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= e($item['invoice_no'] ?: 'Advance / Unallocated') ?></td>
                                <td>INR <?= e(number_format((float) $item['allocated_amount'], 2)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

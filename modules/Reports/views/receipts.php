<section class="panel">
    <div class="toolbar">
        <div>
            <div class="eyebrow">Register</div>
            <h3 style="margin:0 0 6px;">Receipt Register</h3>
            <p class="subtle" style="margin:0;">View receipts generated against successful payments and invoice allocations.</p>
        </div>
        <a href="<?= e(url('/reports')) ?>" class="button button-secondary">Back to Reports</a>
    </div>

    <form method="get" action="<?= e(url('/reports/receipts')) ?>" class="panel" style="box-shadow:none;margin-bottom:18px;padding:18px;">
        <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(170px,1fr));">
            <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Receipt / SO / Client / Reference">
            <select name="company_id">
                <option value="0">All Companies</option>
                <?php foreach (($options['companies'] ?? []) as $company): ?>
                    <option value="<?= e((string) $company['id']) ?>" <?= (int) ($filters['company_id'] ?? 0) === (int) $company['id'] ? 'selected' : '' ?>><?= e($company['label']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="payment_mode">
                <option value="">All Payment Modes</option>
                <?php foreach (['RAZORPAY', 'CASH', 'BANK_TRANSFER', 'CHEQUE', 'UPI', 'OTHER'] as $mode): ?>
                    <option value="<?= e($mode) ?>" <?= ($filters['payment_mode'] ?? '') === $mode ? 'selected' : '' ?>><?= e($mode) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>">
            <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>">
            <button type="submit" class="button">Apply Filters</button>
        </div>
    </form>

    <?php if (($report['items'] ?? []) === []): ?>
        <div class="data-card"><span class="subtle">No receipts matched the selected filters.</span></div>
    <?php else: ?>
        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Receipt</th>
                        <th>Client / SO</th>
                        <th>Payment</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report['items'] as $row): ?>
                        <tr>
                            <td>
                                <strong><?= e($row['receipt_no']) ?></strong><br>
                                <span class="subtle"><?= e($row['receipt_date']) ?></span><br>
                                <span class="subtle"><?= e($row['company_name']) ?></span>
                            </td>
                            <td>
                                <strong><?= e($row['client_name']) ?></strong><br>
                                <span class="subtle"><?= e($row['pan'] ?: '-') ?></span><br>
                                <span class="subtle"><?= e($row['so_no'] ?: '-') ?></span>
                            </td>
                            <td>
                                <?= e($row['payment_mode']) ?> / <?= e($row['transaction_type']) ?><br>
                                <span class="subtle">Ref: <?= e($row['reference_no'] ?: '-') ?></span><br>
                                <span class="subtle">Allocations: <?= e((string) $row['allocation_count']) ?></span>
                            </td>
                            <td>
                                <strong><?= e(number_format((float) $row['receipt_amount'], 2)) ?></strong><br>
                                <span class="chip <?= ($row['payment_status'] ?? '') === 'SUCCESS' ? 'chip-strong' : '' ?>"><?= e($row['payment_status']) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= \App\Core\View::render(base_path('app/Views/partials/pagination.php'), [
            'pagination' => $report,
            'path' => '/reports/receipts',
            'query' => $filters,
        ], null) ?>
    <?php endif; ?>
</section>

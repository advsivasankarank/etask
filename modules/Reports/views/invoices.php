<section class="panel">
    <div class="toolbar">
        <div>
            <div class="eyebrow">Register</div>
            <h3 style="margin:0 0 6px;">Invoice Register</h3>
            <p class="subtle" style="margin:0;">Review invoice issue details, adjustments, payments collected, and pending balances.</p>
        </div>
        <a href="<?= e(url('/reports')) ?>" class="button button-secondary">Back to Reports</a>
    </div>

    <form method="get" action="<?= e(url('/reports/invoices')) ?>" class="panel" style="box-shadow:none;margin-bottom:18px;padding:18px;">
        <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(170px,1fr));">
            <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Invoice / SO / Client / PAN">
            <select name="company_id">
                <option value="0">All Companies</option>
                <?php foreach (($options['companies'] ?? []) as $company): ?>
                    <option value="<?= e((string) $company['id']) ?>" <?= (int) ($filters['company_id'] ?? 0) === (int) $company['id'] ? 'selected' : '' ?>><?= e($company['label']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="payment_status">
                <option value="">All Payment Status</option>
                <?php foreach (['UNPAID', 'PARTIALLY_PAID', 'PAID'] as $status): ?>
                    <option value="<?= e($status) ?>" <?= ($filters['payment_status'] ?? '') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="invoice_type">
                <option value="">All Invoice Types</option>
                <?php foreach (['ADVANCE', 'FINAL', 'DEBIT_NOTE'] as $type): ?>
                    <option value="<?= e($type) ?>" <?= ($filters['invoice_type'] ?? '') === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>">
            <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>">
            <button type="submit" class="button">Apply Filters</button>
        </div>
    </form>

    <?php if (($report['items'] ?? []) === []): ?>
        <div class="data-card"><span class="subtle">No invoices matched the selected filters.</span></div>
    <?php else: ?>
        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Client / SO</th>
                        <th>Amounts</th>
                        <th>Status</th>
                        <th>Open</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report['items'] as $row): ?>
                        <tr>
                            <td>
                                <strong><?= e($row['invoice_no']) ?></strong><br>
                                <span class="subtle"><?= e($row['invoice_date']) ?> | <?= e($row['invoice_type']) ?></span><br>
                                <span class="subtle"><?= e($row['company_name']) ?> | <?= e($row['financial_year_label']) ?></span>
                            </td>
                            <td>
                                <strong><?= e($row['client_name']) ?></strong><br>
                                <span class="subtle"><?= e($row['pan'] ?: '-') ?></span><br>
                                <span class="subtle"><?= e($row['so_no']) ?> | <?= e($row['service_type_name']) ?></span>
                            </td>
                            <td>
                                Gross: <?= e(number_format((float) $row['gross_total'], 2)) ?><br>
                                <span class="subtle">Net: <?= e(number_format((float) $row['net_payable'], 2)) ?></span><br>
                                <span class="subtle">Collected: <?= e(number_format((float) $row['allocated_total'], 2)) ?></span><br>
                                <span class="subtle">Outstanding: <?= e(number_format((float) $row['outstanding_amount'], 2)) ?></span>
                            </td>
                            <td>
                                <span class="chip chip-strong"><?= e($row['payment_status']) ?></span><br>
                                <span class="subtle"><?= e($row['accounting_status']) ?></span>
                            </td>
                            <td><a href="<?= e(url('/billing/show?service_order_id=' . $row['service_order_id'])) ?>" class="button button-secondary">Open Billing</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= \App\Core\View::render(base_path('app/Views/partials/pagination.php'), [
            'pagination' => $report,
            'path' => '/reports/invoices',
            'query' => $filters,
        ], null) ?>
    <?php endif; ?>
</section>

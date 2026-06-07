<section style="display:grid;gap:18px;">
    <div class="grid">
        <div class="metric">
            <div class="eyebrow">Outstanding</div>
            <strong>Invoices</strong>
            <div style="margin-top:8px;font-size:1.85rem;"><?= e((string) ($report['summary']['invoice_count'] ?? 0)) ?></div>
        </div>
        <div class="metric">
            <div class="eyebrow">Invoiced</div>
            <strong>Total</strong>
            <div style="margin-top:8px;font-size:1.85rem;"><?= e(number_format((float) ($report['summary']['invoiced_total'] ?? 0), 2)) ?></div>
        </div>
        <div class="metric">
            <div class="eyebrow">Collected</div>
            <strong>Total</strong>
            <div style="margin-top:8px;font-size:1.85rem;"><?= e(number_format((float) ($report['summary']['collected_total'] ?? 0), 2)) ?></div>
        </div>
        <div class="metric">
            <div class="eyebrow">Receivable</div>
            <strong>Outstanding</strong>
            <div style="margin-top:8px;font-size:1.85rem;"><?= e(number_format((float) ($report['summary']['outstanding_total'] ?? 0), 2)) ?></div>
        </div>
    </div>

    <section class="panel">
        <div class="toolbar">
            <div>
                <div class="eyebrow">Report</div>
                <h3 style="margin:0 0 6px;">Outstanding Report</h3>
                <p class="subtle" style="margin:0;">Receivables by invoice with ageing and due-date visibility.</p>
            </div>
            <a href="<?= e(url('/reports')) ?>" class="button button-secondary">Back to Reports</a>
        </div>

        <form method="get" action="<?= e(url('/reports/outstanding')) ?>" class="panel" style="box-shadow:none;margin-bottom:18px;padding:18px;">
            <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(170px,1fr));">
                <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Invoice / SO / Client / Mobile">
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
                <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>">
                <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>">
                <label class="chip" style="justify-content:center;">
                    <input type="checkbox" name="overdue_only" value="1" <?= ($filters['overdue_only'] ?? '') === '1' ? 'checked' : '' ?> style="width:auto;margin:0;">
                    Overdue only
                </label>
                <button type="submit" class="button">Apply Filters</button>
            </div>
        </form>

        <?php if (($report['items'] ?? []) === []): ?>
            <div class="data-card"><span class="subtle">No outstanding invoices matched the selected filters.</span></div>
        <?php else: ?>
            <div style="overflow:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Client</th>
                            <th>Billing</th>
                            <th>Receivable</th>
                            <th>Ageing</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report['items'] as $row): ?>
                            <tr>
                                <td>
                                    <strong><?= e($row['invoice_no']) ?></strong><br>
                                    <span class="subtle"><?= e($row['invoice_date']) ?> | Due <?= e($row['due_date'] ?: '-') ?></span><br>
                                    <span class="subtle"><?= e($row['company_name']) ?></span>
                                </td>
                                <td>
                                    <strong><?= e($row['client_name']) ?></strong><br>
                                    <span class="subtle"><?= e($row['mobile'] ?: '-') ?></span><br>
                                    <span class="subtle"><?= e($row['so_no']) ?> | <?= e($row['service_type_name']) ?></span>
                                </td>
                                <td>
                                    Net: <?= e(number_format((float) $row['net_payable'], 2)) ?><br>
                                    <span class="subtle">Collected: <?= e(number_format((float) $row['collected_amount'], 2)) ?></span><br>
                                    <span class="chip"><?= e($row['payment_status']) ?></span>
                                </td>
                                <td><strong><?= e(number_format((float) $row['outstanding_amount'], 2)) ?></strong></td>
                                <td><?= (int) $row['due_days'] > 0 ? e((string) $row['due_days']) . ' days overdue' : 'Current' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= \App\Core\View::render(base_path('app/Views/partials/pagination.php'), [
                'pagination' => $report,
                'path' => '/reports/outstanding',
                'query' => $filters,
            ], null) ?>
        <?php endif; ?>
    </section>
</section>

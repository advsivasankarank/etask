<section style="display:grid;gap:18px;">
    <?php
        $outstandingTiles = [
            ['label' => 'Outstanding Invoices', 'value' => (string) ($report['summary']['invoice_count'] ?? 0), 'severity' => 'neutral'],
            ['label' => 'Invoiced Total', 'value' => number_format((float) ($report['summary']['invoiced_total'] ?? 0), 2), 'severity' => 'neutral'],
            ['label' => 'Collected Total', 'value' => number_format((float) ($report['summary']['collected_total'] ?? 0), 2), 'severity' => 'success'],
            ['label' => 'Outstanding Receivable', 'value' => number_format((float) ($report['summary']['outstanding_total'] ?? 0), 2), 'severity' => 'warning'],
        ];
    ?>
    <div class="kpi-grid">
        <?php foreach ($outstandingTiles as $tile): ?>
            <div class="kpi-card severity-<?= e($tile['severity']) ?>">
                <div class="kpi-icon"><?= metric_icon_svg($tile['severity']) ?></div>
                <div class="kpi-body">
                    <div class="kpi-label"><?= e($tile['label']) ?></div>
                    <div class="kpi-value"><?= e($tile['value']) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
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
            <div class="empty-state">
                <div class="empty-state-icon">🔍</div>
                <div class="empty-state-title">No results</div>
                <div class="empty-state-text">No outstanding invoices matched the selected filters.</div>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead class="table-header">
                        <tr>
                            <th>Invoice</th>
                            <th>Client</th>
                            <th>Billing</th>
                            <th>Receivable</th>
                            <th>Ageing</th>
                        </tr>
                    </thead>
                    <tbody class="table-body">
                        <?php foreach ($report['items'] as $row): ?>
                            <tr>
                                <td>
                                    <strong><?= e($row['invoice_no']) ?></strong><br>
                                    <span class="subtle"><?= e($row['invoice_date']) ?> | Due <?= e($row['due_date'] ?: '-') ?></span><br>
                                    <span class="subtle"><?= e($row['company_name']) ?></span>
                                </td>
                                <td>
                                    <?= queue_cell_html('client_name', $row['client_name']) ?><br>
                                    <span class="subtle"><?= e($row['mobile'] ?: '-') ?></span><br>
                                    <span class="subtle"><?= e($row['so_no']) ?> | <?= e($row['service_type_name']) ?></span>
                                </td>
                                <td>
                                    Net: <?= e(number_format((float) $row['net_payable'], 2)) ?><br>
                                    <span class="subtle">Collected: <?= e(number_format((float) $row['collected_amount'], 2)) ?></span><br>
                                    <span class="badge badge-<?= e(status_severity((string) $row['payment_status'])) ?>"><?= e(label_case((string) $row['payment_status'])) ?></span>
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

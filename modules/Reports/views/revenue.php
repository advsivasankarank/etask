<section style="display:grid;gap:18px;">
    <div class="grid">
        <div class="metric">
            <div class="eyebrow">Invoices</div>
            <strong>Count</strong>
            <div style="margin-top:8px;font-size:1.85rem;"><?= e((string) ($report['summary']['invoice_count'] ?? 0)) ?></div>
        </div>
        <div class="metric">
            <div class="eyebrow">Gross</div>
            <strong>Billed</strong>
            <div style="margin-top:8px;font-size:1.85rem;"><?= e(number_format((float) ($report['summary']['gross_total'] ?? 0), 2)) ?></div>
        </div>
        <div class="metric">
            <div class="eyebrow">Collected</div>
            <strong>Total</strong>
            <div style="margin-top:8px;font-size:1.85rem;"><?= e(number_format((float) ($report['summary']['collected_total'] ?? 0), 2)) ?></div>
        </div>
        <div class="metric">
            <div class="eyebrow">Outstanding</div>
            <strong>Total</strong>
            <div style="margin-top:8px;font-size:1.85rem;"><?= e(number_format((float) ($report['summary']['outstanding_total'] ?? 0), 2)) ?></div>
        </div>
    </div>

    <section class="panel">
        <div class="toolbar">
            <div>
                <div class="eyebrow">Report</div>
                <h3 style="margin:0 0 6px;">Revenue Report</h3>
                <p class="subtle" style="margin:0;">Month-wise revenue by company and service type using issued invoice values.</p>
            </div>
            <a href="<?= e(url('/reports')) ?>" class="button button-secondary">Back to Reports</a>
        </div>

        <form method="get" action="<?= e(url('/reports/revenue')) ?>" class="panel" style="box-shadow:none;margin-bottom:18px;padding:18px;">
            <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(170px,1fr));">
                <select name="company_id">
                    <option value="0">All Companies</option>
                    <?php foreach (($options['companies'] ?? []) as $company): ?>
                        <option value="<?= e((string) $company['id']) ?>" <?= (int) ($filters['company_id'] ?? 0) === (int) $company['id'] ? 'selected' : '' ?>><?= e($company['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="service_type_id">
                    <option value="0">All Services</option>
                    <?php foreach (($options['service_types'] ?? []) as $serviceType): ?>
                        <option value="<?= e((string) $serviceType['id']) ?>" <?= (int) ($filters['service_type_id'] ?? 0) === (int) $serviceType['id'] ? 'selected' : '' ?>><?= e($serviceType['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="financial_year_id">
                    <option value="0">All Financial Years</option>
                    <?php foreach (($options['financial_years'] ?? []) as $year): ?>
                        <option value="<?= e((string) $year['id']) ?>" <?= (int) ($filters['financial_year_id'] ?? 0) === (int) $year['id'] ? 'selected' : '' ?>><?= e($year['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>">
                <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>">
                <button type="submit" class="button">Apply Filters</button>
            </div>
        </form>

        <?php if (($report['items'] ?? []) === []): ?>
            <div class="data-card"><span class="subtle">No revenue rows matched the selected filters.</span></div>
        <?php else: ?>
            <div style="overflow:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Company</th>
                            <th>Service</th>
                            <th>Invoices</th>
                            <th>Amounts</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report['items'] as $row): ?>
                            <tr>
                                <td><?= e($row['revenue_month']) ?></td>
                                <td><?= e($row['company_name']) ?></td>
                                <td><?= e($row['service_type_name']) ?></td>
                                <td><?= e((string) $row['invoice_count']) ?></td>
                                <td>
                                    Gross: <?= e(number_format((float) $row['gross_total'], 2)) ?><br>
                                    <span class="subtle">Tax: <?= e(number_format((float) $row['tax_total'], 2)) ?></span><br>
                                    <span class="subtle">Net: <?= e(number_format((float) $row['net_total'], 2)) ?></span><br>
                                    <span class="subtle">Collected: <?= e(number_format((float) $row['collected_total'], 2)) ?></span><br>
                                    <span class="subtle">Outstanding: <?= e(number_format((float) $row['outstanding_total'], 2)) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</section>

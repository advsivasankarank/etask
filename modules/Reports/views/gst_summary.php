<section style="display:grid;gap:18px;">
    <div class="grid">
        <div class="metric">
            <div class="eyebrow">GST Orders</div>
            <strong>Total</strong>
            <div style="margin-top:8px;font-size:1.85rem;"><?= e((string) ($report['summary']['total_orders'] ?? 0)) ?></div>
        </div>
        <div class="metric">
            <div class="eyebrow">Filing</div>
            <strong>Done</strong>
            <div style="margin-top:8px;font-size:1.85rem;"><?= e((string) ($report['summary']['filing_done_orders'] ?? 0)) ?></div>
        </div>
        <div class="metric">
            <div class="eyebrow">Acknowledgement</div>
            <strong>Captured</strong>
            <div style="margin-top:8px;font-size:1.85rem;"><?= e((string) ($report['summary']['acknowledgement_orders'] ?? 0)) ?></div>
        </div>
        <div class="metric">
            <div class="eyebrow">Collections</div>
            <strong>Total</strong>
            <div style="margin-top:8px;font-size:1.85rem;"><?= e(number_format((float) ($report['summary']['collected_total'] ?? 0), 2)) ?></div>
        </div>
    </div>

    <section class="panel">
        <div class="toolbar">
            <div>
                <div class="eyebrow">Summary</div>
                <h3 style="margin:0 0 6px;">GST Summary</h3>
                <p class="subtle" style="margin:0;">Grouped by company, GST service type, basis, and compliance period.</p>
            </div>
            <a href="<?= e(url('/reports')) ?>" class="button button-secondary">Back to Reports</a>
        </div>

        <form method="get" action="<?= e(url('/reports/gst-summary')) ?>" class="panel" style="box-shadow:none;margin-bottom:18px;padding:18px;">
            <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(170px,1fr));">
                <select name="company_id">
                    <option value="0">All Companies</option>
                    <?php foreach (($options['companies'] ?? []) as $company): ?>
                        <option value="<?= e((string) $company['id']) ?>" <?= (int) ($filters['company_id'] ?? 0) === (int) $company['id'] ? 'selected' : '' ?>><?= e($company['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="work_basis">
                    <option value="">All Basis</option>
                    <?php foreach (['ANNUAL', 'MONTHLY', 'QUARTERLY'] as $basis): ?>
                        <option value="<?= e($basis) ?>" <?= ($filters['work_basis'] ?? '') === $basis ? 'selected' : '' ?>><?= e($basis) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="period_year" value="<?= e($filters['period_year'] ?? '') ?>" placeholder="Period year">
                <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>">
                <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>">
                <button type="submit" class="button">Apply Filters</button>
            </div>
        </form>

        <?php if (($report['items'] ?? []) === []): ?>
            <div class="data-card"><span class="subtle">No GST records matched the selected filters.</span></div>
        <?php else: ?>
            <div style="overflow:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Service</th>
                            <th>Basis / Period</th>
                            <th>Orders</th>
                            <th>Billing</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report['items'] as $row): ?>
                            <tr>
                                <td><?= e($row['company_name']) ?></td>
                                <td><?= e($row['service_type_name']) ?></td>
                                <td>
                                    <?= e($row['work_basis'] ?: '-') ?><br>
                                    <span class="subtle"><?= e($row['report_period']) ?></span>
                                </td>
                                <td>
                                    Total: <?= e((string) $row['total_orders']) ?><br>
                                    <span class="subtle">Filed: <?= e((string) $row['filing_done_orders']) ?></span><br>
                                    <span class="subtle">Ack: <?= e((string) $row['acknowledgement_orders']) ?></span>
                                </td>
                                <td>
                                    Billed: <?= e(number_format((float) $row['billed_total'], 2)) ?><br>
                                    <span class="subtle">Collected: <?= e(number_format((float) $row['collected_total'], 2)) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</section>

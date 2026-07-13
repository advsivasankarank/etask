<section class="panel">
    <div class="toolbar">
        <div>
            <div class="eyebrow">Register</div>
            <h3 style="margin:0 0 6px;">Consultant Report</h3>
            <p class="subtle" style="margin:0;">Review consultant assignments, deliverables, billing, and settlement progress across client cases.</p>
        </div>
        <a href="<?= e(url('/reports')) ?>" class="button button-secondary">Back to Reports</a>
    </div>

    <form method="get" action="<?= e(url('/reports/consultants')) ?>" class="panel" style="box-shadow:none;margin-bottom:18px;padding:18px;">
        <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));">
            <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Consultant / Client / SO">
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
            <select name="status">
                <option value="">All Statuses</option>
                <?php foreach (['ASSIGNED', 'DELIVERED', 'COMPLETED', 'CLOSED'] as $status): ?>
                    <option value="<?= e($status) ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>">
            <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>">
            <button type="submit" class="button">Apply Filters</button>
        </div>
    </form>

    <?php if (($report['items'] ?? []) === []): ?>
        <div class="data-card"><span class="subtle">No consultant assignments matched the selected filters.</span></div>
    <?php else: ?>
        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Consultant</th>
                        <th>Case</th>
                        <th>Status</th>
                        <th>Deliverables</th>
                        <th>Billing</th>
                        <th>Settlement</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report['items'] as $row): ?>
                        <tr>
                            <td>
                                <strong><?= e($row['consultant_name']) ?></strong><br>
                                <span class="subtle">Reviewer: <?= e($row['reviewer_name'] ?: '-') ?></span><br>
                                <span class="subtle">Assigned: <?= e($row['assigned_at']) ?></span>
                            </td>
                            <td>
                                <strong><?= e($row['so_no']) ?></strong><br>
                                <span class="subtle"><?= e($row['client_name']) ?></span><br>
                                <span class="subtle"><?= e($row['company_name']) ?> | <?= e($row['service_type_name']) ?></span><br>
                                <a href="<?= e(url('/consultants/show?service_order_id=' . $row['service_order_id'])) ?>" class="chip" style="margin-top:8px;">Open Workspace</a>
                            </td>
                            <td><span class="chip chip-strong"><?= e($row['status']) ?></span></td>
                            <td><?= e((string) $row['deliverable_count']) ?></td>
                            <td>
                                Bills: <?= e((string) $row['bill_count']) ?><br>
                                <span class="subtle">INR <?= e(number_format((float) $row['billed_total'], 2)) ?></span>
                            </td>
                            <td>
                                Paid: INR <?= e(number_format((float) $row['paid_total'], 2)) ?><br>
                                <span class="subtle">Pending: INR <?= e(number_format(max((float) $row['billed_total'] - (float) $row['paid_total'], 0), 2)) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= \App\Core\View::render(base_path('app/Views/partials/pagination.php'), [
            'pagination' => $report,
            'path' => '/reports/consultants',
            'query' => $filters,
        ], null) ?>
    <?php endif; ?>
</section>

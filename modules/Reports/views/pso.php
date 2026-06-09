<section class="panel">
    <div class="toolbar">
        <div>
            <div class="eyebrow">Register</div>
            <h3 style="margin:0 0 6px;">PSO Register</h3>
            <p class="subtle" style="margin:0;">Track pre-service orders, review status, conversion progress, and created service orders.</p>
        </div>
        <a href="<?= e(url('/reports')) ?>" class="button button-secondary">Back to Reports</a>
    </div>

    <form method="get" action="<?= e(url('/reports/pso')) ?>" class="panel" style="box-shadow:none;margin-bottom:18px;padding:18px;">
        <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));">
            <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="PSO / Client / PAN / TAN">
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
            <select name="current_status">
                <option value="">All Statuses</option>
                <?php foreach (['SUBMITTED', 'UNDER_REVIEW', 'REJECTED', 'CONVERTED_TO_SO'] as $status): ?>
                    <option value="<?= e($status) ?>" <?= ($filters['current_status'] ?? '') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>">
            <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>">
            <button type="submit" class="button">Apply Filters</button>
        </div>
    </form>

    <?php if (($report['items'] ?? []) === []): ?>
        <div class="data-card"><span class="subtle">No PSOs matched the selected filters.</span></div>
    <?php else: ?>
        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>PSO</th>
                        <th>Client</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Requested By</th>
                        <th>Converted SO</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report['items'] as $row): ?>
                        <tr>
                            <td>
                                <strong><?= e($row['pso_no']) ?></strong><br>
                                <span class="subtle"><?= e($row['title']) ?></span><br>
                                <span class="subtle">Submitted: <?= e($row['submitted_at']) ?></span><br>
                                <a href="<?= e(url('/client-portal/pso/show?id=' . $row['id'])) ?>" class="chip" style="margin-top:8px;">Open PSO</a>
                            </td>
                            <td>
                                <?= e($row['client_name']) ?><br>
                                <span class="subtle">PAN: <?= e($row['pan'] ?: '-') ?></span><br>
                                <span class="subtle">TAN: <?= e($row['tan'] ?: '-') ?></span>
                            </td>
                            <td>
                                <?= e($row['service_type_name']) ?><br>
                                <span class="subtle"><?= e($row['company_name']) ?></span><br>
                                <span class="subtle"><?= e($row['requested_for_period'] ?: '-') ?></span>
                            </td>
                            <td>
                                <span class="chip chip-strong"><?= e($row['current_status']) ?></span><br>
                                <span class="subtle">Reviewed: <?= e($row['reviewed_at'] ?: '-') ?></span>
                            </td>
                            <td><?= e($row['requested_by_name'] ?: '-') ?></td>
                            <td><?= e($row['converted_so_no'] ?: '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= \App\Core\View::render(base_path('app/Views/partials/pagination.php'), [
            'pagination' => $report,
            'path' => '/reports/pso',
            'query' => $filters,
        ], null) ?>
    <?php endif; ?>
</section>

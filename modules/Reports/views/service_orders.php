<section class="panel">
    <div class="toolbar">
        <div>
            <div class="eyebrow">Register</div>
            <h3 style="margin:0 0 6px;">Service Order Register</h3>
            <p class="subtle" style="margin:0;">Track SO stage, basis, closure progress, and assigned CRM across companies.</p>
        </div>
        <a href="<?= e(url('/reports')) ?>" class="button button-secondary">Back to Reports</a>
    </div>

    <form method="get" action="<?= e(url('/reports/service-orders')) ?>" class="panel" style="box-shadow:none;margin-bottom:18px;padding:18px;">
        <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(170px,1fr));">
            <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="SO / Client / PAN / TAN">
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
            <select name="work_basis">
                <option value="">All Basis</option>
                <?php foreach (['ANNUAL', 'MONTHLY', 'QUARTERLY'] as $basis): ?>
                    <option value="<?= e($basis) ?>" <?= ($filters['work_basis'] ?? '') === $basis ? 'selected' : '' ?>><?= e($basis) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="stage_code" value="<?= e($filters['stage_code'] ?? '') ?>" placeholder="Stage code">
            <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>">
            <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>">
            <button type="submit" class="button">Apply Filters</button>
        </div>
    </form>

    <?php if (($report['items'] ?? []) === []): ?>
        <div class="data-card"><span class="subtle">No service orders matched the selected filters.</span></div>
    <?php else: ?>
        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>SO</th>
                        <th>Client</th>
                        <th>Service</th>
                        <th>Stage</th>
                        <th>Closure</th>
                        <th>Assignment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report['items'] as $row): ?>
                        <tr>
                            <td>
                                <strong><?= e($row['so_no']) ?></strong><br>
                                <span class="subtle"><?= e($row['company_name']) ?> | <?= e($row['financial_year_label']) ?></span><br>
                                <span class="subtle"><?= e($row['created_at']) ?></span><br>
                                <a href="<?= e(url('/service-orders/show?id=' . $row['id'])) ?>" class="chip" style="margin-top:8px;">Open SO</a>
                            </td>
                            <td>
                                <strong><?= e($row['client_name']) ?></strong><br>
                                <span class="subtle"><?= e($row['pan'] ?: '-') ?> / <?= e($row['tan'] ?: '-') ?></span><br>
                                <span class="subtle"><?= e($row['mobile'] ?: '-') ?></span>
                            </td>
                            <td>
                                <?= e($row['service_type_name']) ?><br>
                                <span class="subtle"><?= e($row['service_group']) ?> | <?= e($row['work_basis'] ?: '-') ?></span><br>
                                <span class="subtle"><?= e($row['period_label'] ?: ($row['assessment_year'] ?: '-')) ?></span>
                            </td>
                            <td>
                                <span class="chip chip-strong"><?= e($row['current_stage_code']) ?></span><br>
                                <span class="subtle">Filing: <?= (int) $row['is_filing_done'] === 1 ? 'Done' : 'Pending' ?></span><br>
                                <span class="subtle">Ack: <?= (int) $row['is_acknowledgement_captured'] === 1 ? 'Captured' : 'Pending' ?></span>
                            </td>
                            <td>
                                <span class="subtle">Procedural: <?= e($row['procedural_closure_status'] ?: 'PENDING') ?></span><br>
                                <span class="subtle">Accounting: <?= e($row['accounting_closure_status'] ?: 'PENDING') ?></span><br>
                                <span class="subtle">Final: <?= e($row['final_closure_status'] ?: 'PENDING') ?></span>
                            </td>
                            <td>
                                CRM: <?= e($row['assigned_crm_name'] ?: '-') ?><br>
                                <span class="subtle">SLA: <?= e($row['sla_due_at'] ?: '-') ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= \App\Core\View::render(base_path('app/Views/partials/pagination.php'), [
            'pagination' => $report,
            'path' => '/reports/service-orders',
            'query' => $filters,
        ], null) ?>
    <?php endif; ?>
</section>

<section class="panel">
    <div class="toolbar">
        <div>
            <div class="eyebrow">Register</div>
            <h3 style="margin:0 0 6px;">Client Register</h3>
            <p class="subtle" style="margin:0;">Search clients by PAN, TAN, GSTIN, mobile, and CRM assignment.</p>
        </div>
        <a href="<?= e(url('/reports')) ?>" class="button button-secondary">Back to Reports</a>
    </div>

    <form method="get" action="<?= e(url('/reports/clients')) ?>" class="panel" style="box-shadow:none;margin-bottom:18px;padding:18px;">
        <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));">
            <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Client / PAN / TAN / GSTIN / Mobile">
            <select name="crm_id">
                <option value="0">All CRM Users</option>
                <?php foreach (($options['crm_users'] ?? []) as $crm): ?>
                    <option value="<?= e((string) $crm['id']) ?>" <?= (int) ($filters['crm_id'] ?? 0) === (int) $crm['id'] ? 'selected' : '' ?>><?= e($crm['label']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status">
                <option value="active" <?= ($filters['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="archived" <?= ($filters['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                <option value="all" <?= ($filters['status'] ?? '') === 'all' ? 'selected' : '' ?>>All</option>
            </select>
            <button type="submit" class="button">Apply Filters</button>
        </div>
    </form>

    <?php if (($report['items'] ?? []) === []): ?>
        <div class="data-card"><span class="subtle">No clients matched the selected filters.</span></div>
    <?php else: ?>
        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>PAN / Aadhaar</th>
                        <th>GST / TAN</th>
                        <th>Contact</th>
                        <th>CRM</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report['items'] as $row): ?>
                        <tr>
                            <td>
                                <strong><?= e($row['legal_name']) ?></strong><br>
                                <span class="subtle"><?= e($row['client_code']) ?> | <?= e($row['client_type']) ?></span><br>
                                <a href="<?= e(url('/clients/show?id=' . $row['id'])) ?>" class="chip" style="margin-top:8px;">Open Client</a>
                            </td>
                            <td>
                                <strong><?= e($row['pan'] ?: '-') ?></strong><br>
                                <span class="subtle">Aadhaar: <?= e($row['aadhaar_last4'] ? 'XXXX-' . $row['aadhaar_last4'] : '-') ?></span>
                            </td>
                            <td>
                                GSTIN: <?= e($row['gstin'] ?: '-') ?><br>
                                TAN: <?= e($row['tan'] ?: '-') ?>
                            </td>
                            <td>
                                <?= e($row['primary_contact_name'] ?: '-') ?><br>
                                <span class="subtle"><?= e($row['primary_contact_mobile'] ?: ($row['mobile'] ?: '-')) ?></span><br>
                                <span class="subtle"><?= e($row['primary_contact_email'] ?: ($row['email'] ?: '-')) ?></span>
                            </td>
                            <td><?= e($row['assigned_crm_name'] ?: '-') ?></td>
                            <td><span class="chip <?= (int) $row['is_active'] === 1 ? 'chip-strong' : '' ?>"><?= (int) $row['is_active'] === 1 ? 'Active' : 'Archived' ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= \App\Core\View::render(base_path('app/Views/partials/pagination.php'), [
            'pagination' => $report,
            'path' => '/reports/clients',
            'query' => $filters,
        ], null) ?>
    <?php endif; ?>
</section>

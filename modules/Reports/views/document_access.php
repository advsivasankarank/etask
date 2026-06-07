<section class="panel">
    <div class="toolbar">
        <div>
            <div class="eyebrow">Audit Report</div>
            <h3 style="margin:0 0 6px;">Document Access Report</h3>
            <p class="subtle" style="margin:0;">Track secure document downloads, denied attempts, users, timestamps, and IP addresses.</p>
        </div>
        <a href="<?= e(url('/reports')) ?>" class="button button-secondary">Back to Reports</a>
    </div>

    <form method="get" action="<?= e(url('/reports/document-access')) ?>" class="panel" style="box-shadow:none;margin-bottom:18px;padding:18px;">
        <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(170px,1fr));">
            <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Document / Client / User / IP">
            <select name="action_code">
                <option value="">All Actions</option>
                <?php foreach (['DOWNLOAD_SUCCESS', 'DOWNLOAD_DENIED', 'DOWNLOAD_MISSING'] as $action): ?>
                    <option value="<?= e($action) ?>" <?= ($filters['action_code'] ?? '') === $action ? 'selected' : '' ?>><?= e($action) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>">
            <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>">
            <button type="submit" class="button">Apply Filters</button>
        </div>
    </form>

    <?php if (($report['items'] ?? []) === []): ?>
        <div class="data-card"><span class="subtle">No document access logs matched the selected filters.</span></div>
    <?php else: ?>
        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Document</th>
                        <th>Action</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report['items'] as $row): ?>
                        <tr>
                            <td><?= e($row['created_at']) ?></td>
                            <td><?= e($row['user_name'] ?: 'Guest / System') ?></td>
                            <td>
                                <strong><?= e($row['document_name'] ?: 'Unknown document') ?></strong><br>
                                <span class="subtle"><?= e($row['client_name'] ?: '-') ?> | <?= e($row['linked_module'] ?: '-') ?></span>
                            </td>
                            <td>
                                <span class="chip <?= ($row['action_code'] ?? '') === 'DOWNLOAD_SUCCESS' ? 'chip-strong' : '' ?>"><?= e($row['action_code']) ?></span><br>
                                <span class="subtle"><?= e($row['description']) ?></span>
                            </td>
                            <td><?= e($row['ip_address'] ?: '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= \App\Core\View::render(base_path('app/Views/partials/pagination.php'), [
            'pagination' => $report,
            'path' => '/reports/document-access',
            'query' => $filters,
        ], null) ?>
    <?php endif; ?>
</section>

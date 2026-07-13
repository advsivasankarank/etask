<section class="panel">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <div>
            <div class="eyebrow">Document Module</div>
            <h3 style="margin:0 0 6px;">Document Register</h3>
            <div class="subtle">Manage and track all documents across clients and service orders.</div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <?php if (\App\Core\Auth::can('documents.request')): ?>
                <a href="<?= e(url('/documents/requests/create')) ?>" class="button">+ Request Document</a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('documents.movement.manage')): ?>
                <a href="<?= e(url('/documents/movement/create')) ?>" class="button button-secondary">+ Record Movement</a>
            <?php endif; ?>
        </div>
    </div>

    <?php
        $docSummaryTiles = [
            'total' => ['label' => 'Total', 'severity' => 'neutral'],
            'pending_verification' => ['label' => 'Pending Verify', 'severity' => 'warning'],
            'verified' => ['label' => 'Verified', 'severity' => 'success'],
            'requested' => ['label' => 'Requested', 'severity' => 'neutral'],
            'in_movement' => ['label' => 'In Movement', 'severity' => 'neutral'],
            'archived' => ['label' => 'Archived', 'severity' => 'neutral'],
        ];
    ?>
    <div class="kpi-grid" style="margin-bottom:20px;">
        <?php foreach ($docSummaryTiles as $key => $tile): ?>
            <div class="kpi-card severity-<?= e($tile['severity']) ?>">
                <div class="kpi-icon"><?= metric_icon_svg($tile['severity']) ?></div>
                <div class="kpi-body">
                    <div class="kpi-label"><?= e($tile['label']) ?></div>
                    <div class="kpi-value"><?= e((string) ($summary[$key] ?? 0)) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <form method="get" action="<?= e(url('/documents')) ?>" class="search-bar">
        <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Search by document name, category, client, or SO number...">
        <select name="verification_status" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
            <option value="">All Statuses</option>
            <option value="PENDING" <?= ($filters['verification_status'] ?? '') === 'PENDING' ? 'selected' : '' ?>>Pending</option>
            <option value="VERIFIED" <?= ($filters['verification_status'] ?? '') === 'VERIFIED' ? 'selected' : '' ?>>Verified</option>
            <option value="REJECTED" <?= ($filters['verification_status'] ?? '') === 'REJECTED' ? 'selected' : '' ?>>Rejected</option>
        </select>
        <button type="submit" class="button">Search</button>
    </form>

    <?php if (($documents['items'] ?? []) === []): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🔍</div>
            <div class="empty-state-title">No documents found</div>
            <div class="empty-state-text">No documents match the current filters. Adjust the filters or upload a document through its linked workflow.</div>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead class="table-header">
                    <tr>
                        <th>Document</th>
                        <th>Client</th>
                        <th>SO</th>
                        <th>Version</th>
                        <th>Uploaded</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="table-body">
                    <?php foreach ($documents['items'] as $doc): ?>
                        <tr>
                            <td>
                                <div style="font-weight:700;"><?= e($doc['document_name']) ?></div>
                                <div class="subtle" style="font-size:0.78rem;"><?= e($doc['document_category'] ?: 'Document') ?></div>
                            </td>
                            <td><?= queue_cell_html('client_name', $doc['client_name'] ?? '') ?></td>
                            <td><?= e($doc['so_no'] ?: '—') ?></td>
                            <td>V<?= e((string) ($doc['current_version_no'] ?? 1)) ?></td>
                            <td><?= e($doc['uploaded_at'] ?: '—') ?></td>
                            <td><span class="badge badge-<?= e(status_severity((string) ($doc['verification_status'] ?? 'PENDING'))) ?>"><?= e(label_case((string) ($doc['verification_status'] ?? 'PENDING'))) ?></span></td>
                            <td>
                                <div style="display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end;">
                                    <a href="<?= e(url('/documents/show?id=' . $doc['id'])) ?>" class="btn btn-secondary btn-sm">View</a>
                                    <a href="<?= e(url('/documents/' . $doc['id'] . '/download')) ?>" class="btn btn-secondary btn-sm">Download</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= \App\Core\View::render(base_path('app/Views/partials/pagination.php'), [
            'pagination' => $documents ?? null,
            'path' => '/documents',
            'query' => ['search' => $filters['search'] ?? '', 'verification_status' => $filters['verification_status'] ?? ''],
        ], null) ?>
    <?php endif; ?>
</section>

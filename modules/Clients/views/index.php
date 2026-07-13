<section class="panel">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <div>
            <div class="eyebrow">Client Module</div>
            <h3 style="margin:0 0 6px;">Client Register</h3>
            <div class="subtle">Search by PAN, TAN, name, GSTIN, or mobile. Archive only, no deletion.</div>
        </div>
        <?php if (\App\Core\Auth::can('clients.create')): ?>
            <a href="<?= e(url('/clients/create')) ?>" class="button">+ Add Client</a>
        <?php endif; ?>
    </div>

    <form method="get" action="<?= e(url('/clients')) ?>" class="search-bar">
        <input type="text" name="search" value="<?= e($search ?? '') ?>" placeholder="Search by PAN, TAN, Name, GSTIN, or Mobile...">
        <button type="submit" class="button">Search</button>
    </form>

    <?php if ($clients === []): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🔍</div>
            <div class="empty-state-title">No results</div>
            <div class="empty-state-text">No clients found matching your search criteria.</div>
        </div>
    <?php else: ?>
        <div class="table-wrap mobile-card-wrap">
            <table class="mobile-card-table">
                <thead class="table-header">
                    <tr>
                        <th>Client</th>
                        <th>GST / TAN</th>
                        <th>Mobile</th>
                        <th>CRM</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="table-body">
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td data-label="Client">
                                <div class="cell-with-avatar">
                                    <span class="avatar-chip"><?= e(strtoupper(substr($client['legal_name'], 0, 1))) ?></span>
                                    <span>
                                        <div style="font-weight:700;"><?= e($client['legal_name']) ?></div>
                                        <div class="subtle" style="font-size:0.78rem;"><?= e($client['pan'] ?: 'No PAN') ?></div>
                                    </span>
                                </div>
                            </td>
                            <td data-label="GST / TAN"><?= e(($client['gstin'] ?: '—') . ' / ' . ($client['tan'] ?: '—')) ?></td>
                            <td data-label="Mobile"><?= e($client['mobile'] ?: '—') ?></td>
                            <td data-label="CRM"><?= e($client['assigned_crm_name'] ?: '—') ?></td>
                            <td data-label="Status">
                                <?php if ((int) $client['is_active'] === 1): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-neutral">Archived</span>
                                <?php endif; ?>
                            </td>
                            <td class="mobile-card-actions" data-label="Actions">
                                <div style="display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end;">
                                    <a href="<?= e(url('/clients/show?id=' . $client['id'])) ?>" class="btn btn-secondary btn-sm">View</a>
                                    <?php if (\App\Core\Auth::can('clients.edit')): ?>
                                        <a href="<?= e(url('/clients/edit?id=' . $client['id'])) ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <?php endif; ?>
                                    <?php if (\App\Core\Auth::can('clients.credentials.manage')): ?>
                                        <a href="<?= e(url('/clients/credentials?id=' . $client['id'])) ?>" class="btn btn-secondary btn-sm">Credentials</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= \App\Core\View::render(base_path('app/Views/partials/pagination.php'), [
            'pagination' => $pagination ?? null,
            'path' => '/clients',
            'query' => ['search' => $search ?? ''],
        ], null) ?>
    <?php endif; ?>
</section>

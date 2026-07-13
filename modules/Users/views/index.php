<section class="panel">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <div>
            <div class="eyebrow">Identity</div>
            <h3 style="margin:0 0 6px;">User Master</h3>
            <div class="subtle"><?= $portalOnly ? 'CRM view is limited to client portal users.' : 'Manage internal and client portal users.' ?></div>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <?php if (!$portalOnly): ?>
                <a href="<?= e(url('/users/create?user_type=internal')) ?>" class="button">Create Internal User</a>
            <?php endif; ?>
            <a href="<?= e(url('/users/create?user_type=portal')) ?>" class="button button-secondary">Create Portal User</a>
        </div>
    </div>

    <form method="get" action="<?= e(url('/users')) ?>" class="search-bar">
        <input type="text" name="search" value="<?= e($search ?? '') ?>" placeholder="Username / Name / Email / Mobile / Client" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
        <button type="submit" class="button">Search</button>
    </form>

    <?php if ($users === []): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🔍</div>
            <div class="empty-state-title">No users found</div>
            <div class="empty-state-text">No user accounts match the current filters. Adjust the filters or create a user if permitted.</div>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead class="table-header">
                    <tr><th>User</th><th>Roles</th><th>Email</th><th>Mobile</th><th>Client Link</th><th>Status</th><th></th></tr>
                </thead>
                <tbody class="table-body">
                    <?php foreach ($users as $row): ?>
                        <tr>
                            <td>
                                <div class="cell-with-avatar">
                                    <span class="avatar-chip"><?= e(strtoupper(substr($row['full_name'], 0, 1))) ?></span>
                                    <span>
                                        <div style="font-weight:700;"><?= e($row['full_name']) ?></div>
                                        <div class="subtle" style="font-size:0.78rem;"><?= e($row['username']) ?></div>
                                    </span>
                                </div>
                            </td>
                            <td><?= e($row['role_labels'] ?: '—') ?></td>
                            <td><?= e($row['email']) ?></td>
                            <td><?= e($row['mobile'] ?: '—') ?></td>
                            <td><?= e($row['client_name'] ?: '—') ?><?= !empty($row['contact_name']) ? ' / ' . e($row['contact_name']) : '' ?></td>
                            <td>
                                <?php if ((int) $row['is_active'] === 1): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-neutral">Archived</span>
                                <?php endif; ?>
                            </td>
                            <td><a href="<?= e(url('/users/show?id=' . $row['id'])) ?>" class="btn btn-secondary btn-sm">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= \App\Core\View::render(base_path('app/Views/partials/pagination.php'), [
            'pagination' => $pagination ?? null,
            'path' => '/users',
            'query' => ['search' => $search ?? ''],
        ], null) ?>
    <?php endif; ?>
</section>

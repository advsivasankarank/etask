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
        <div class="data-card"><span class="subtle">No users found.</span></div>
    <?php else: ?>
        <div class="card-grid">
            <?php foreach ($users as $row): ?>
                <article class="data-card">
                    <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                        <div>
                            <div class="eyebrow"><?= e($row['username']) ?></div>
                            <h4 style="margin:4px 0 0;"><?= e($row['full_name']) ?></h4>
                        </div>
                        <span class="chip <?= (int) $row['is_active'] === 1 ? '' : 'chip-strong' ?>"><?= (int) $row['is_active'] === 1 ? 'Active' : 'Archived' ?></span>
                    </div>
                    <div class="stat-line"><span>Roles</span><strong><?= e($row['role_labels'] ?: '-') ?></strong></div>
                    <div class="stat-line"><span>Email</span><strong><?= e($row['email']) ?></strong></div>
                    <div class="stat-line"><span>Mobile</span><strong><?= e($row['mobile'] ?: '-') ?></strong></div>
                    <div class="stat-line"><span>Client Link</span><strong><?= e($row['client_name'] ?: '-') ?><?= !empty($row['contact_name']) ? ' / ' . e($row['contact_name']) : '' ?></strong></div>
                    <div style="display:flex;justify-content:flex-end;margin-top:6px;">
                        <a href="<?= e(url('/users/show?id=' . $row['id'])) ?>" class="button button-secondary">View User</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?= \App\Core\View::render(base_path('app/Views/partials/pagination.php'), [
            'pagination' => $pagination ?? null,
            'path' => '/users',
            'query' => ['search' => $search ?? ''],
        ], null) ?>
    <?php endif; ?>
</section>

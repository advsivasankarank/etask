<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar"><div><div class="eyebrow">Workforce Module</div><h3 style="margin:0 0 6px;">Consultant Register</h3><div class="subtle">Manage external consultants and their details.</div></div>
        <?php if (\App\Core\Auth::can('workforce.consultants.manage')): ?><a href="<?= e(url('/workforce/consultants/create')) ?>" class="button">+ Add Consultant</a><?php endif; ?>
    </div>

    <form method="get" action="<?= e(url('/workforce/consultants')) ?>" class="search-bar">
        <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Search by name, firm, PAN, or mobile...">
        <select name="status" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;"><option value="">All Statuses</option><option value="ACTIVE" <?= ($filters['status'] ?? '') === 'ACTIVE' ? 'selected' : '' ?>>Active</option><option value="INACTIVE" <?= ($filters['status'] ?? '') === 'INACTIVE' ? 'selected' : '' ?>>Inactive</option></select>
        <button type="submit" class="button">Search</button>
    </form>

    <?php if (($consultants['items'] ?? []) === []): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🔍</div>
            <div class="empty-state-title">No consultants found</div>
            <div class="empty-state-text">No consultant master records match the current filters. Adjust the filters or add a consultant if permitted.</div>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead class="table-header">
                    <tr><th>Consultant</th><th>Mobile</th><th>Email</th><th>PAN</th><th>Expertise</th><th>Status</th><th></th></tr>
                </thead>
                <tbody class="table-body">
                    <?php foreach ($consultants['items'] as $con): ?>
                        <tr>
                            <td>
                                <div class="cell-with-avatar">
                                    <span class="avatar-chip"><?= e(strtoupper(substr($con['name'], 0, 1))) ?></span>
                                    <span>
                                        <div style="font-weight:700;"><?= e($con['name']) ?></div>
                                        <div class="subtle" style="font-size:0.78rem;"><?= e($con['firm_name'] ?: 'Consultant') ?></div>
                                    </span>
                                </div>
                            </td>
                            <td><?= e($con['mobile'] ?: '—') ?></td>
                            <td><?= e($con['email'] ?: '—') ?></td>
                            <td><?= e($con['pan'] ?: '—') ?></td>
                            <td><?= e($con['expertise'] ?: '—') ?></td>
                            <td><span class="badge badge-<?= e(status_severity((string) $con['status'])) ?>"><?= e(label_case((string) $con['status'])) ?></span></td>
                            <td>
                                <div style="display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end;">
                                    <a href="<?= e(url('/workforce/consultants/show?id=' . $con['id'])) ?>" class="btn btn-secondary btn-sm">View</a>
                                    <?php if (\App\Core\Auth::can('workforce.consultants.manage')): ?><a href="<?= e(url('/workforce/consultants/edit?id=' . $con['id'])) ?>" class="btn btn-secondary btn-sm">Edit</a><?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

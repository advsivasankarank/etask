<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar"><div><div class="eyebrow">Workforce Module</div><h3 style="margin:0 0 6px;">Consultant Register</h3><div class="subtle">Manage external consultants and their details.</div></div>
        <?php if (\App\Core\Auth::can('workforce.consultants.manage')): ?><a href="<?= e(url('/workforce/consultants/create')) ?>" class="button">+ Add Consultant</a><?php endif; ?>
    </div>

    <form method="get" action="<?= e(url('/workforce/consultants')) ?>" class="search-bar">
        <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Search by name, firm, PAN, or mobile...">
        <select name="status" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;"><option value="">All Status</option><option value="ACTIVE" <?= ($filters['status'] ?? '') === 'ACTIVE' ? 'selected' : '' ?>>Active</option><option value="INACTIVE" <?= ($filters['status'] ?? '') === 'INACTIVE' ? 'selected' : '' ?>>Inactive</option></select>
        <button type="submit" class="button">Search</button>
    </form>

    <?php if (($consultants['items'] ?? []) === []): ?><div class="data-card" style="text-align:center;padding:40px;"><div class="eyebrow">No Results</div><p class="subtle" style="margin:8px 0 0;">No consultants found.</p></div><?php else: ?>
        <div class="card-grid">
            <?php foreach ($consultants['items'] as $con): ?>
                <article class="data-card">
                    <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;"><div><div class="eyebrow"><?= e($con['firm_name'] ?: 'Consultant') ?></div><h4 style="margin:4px 0 0;"><?= e($con['name']) ?></h4></div><span class="chip"><?= e($con['status']) ?></span></div>
                    <div class="stat-line"><span>Mobile</span><strong><?= e($con['mobile'] ?: '-') ?></strong></div>
                    <div class="stat-line"><span>Email</span><strong><?= e($con['email'] ?: '-') ?></strong></div>
                    <div class="stat-line"><span>PAN</span><strong><?= e($con['pan'] ?: '-') ?></strong></div>
                    <div class="stat-line"><span>Expertise</span><strong><?= e($con['expertise'] ?: '-') ?></strong></div>
                    <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:10px;flex-wrap:wrap;">
                        <a href="<?= e(url('/workforce/consultants/show?id=' . $con['id'])) ?>" class="button button-secondary">View</a>
                        <?php if (\App\Core\Auth::can('workforce.consultants.manage')): ?><a href="<?= e(url('/workforce/consultants/edit?id=' . $con['id'])) ?>" class="button button-secondary">Edit</a><?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

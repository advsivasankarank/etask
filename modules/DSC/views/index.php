<section class="panel">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <div>
            <div class="eyebrow">DSC Module</div>
            <h3 style="margin:0 0 6px;">DSC Register</h3>
            <div class="subtle">Manage digital signature certificates, custody, and renewal tracking.</div>
        </div>
        <?php if (\App\Core\Auth::can('dsc.create')): ?>
            <a href="<?= e(url('/dsc/create')) ?>" class="button">+ Add DSC</a>
        <?php endif; ?>
    </div>

    <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(130px, 1fr));margin-bottom:20px;">
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Total</div><div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['total'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">In Office</div><div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['in_office'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">With Staff</div><div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['with_staff'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">With Client</div><div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['with_client'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Expiring 30d</div><div style="font-size:1.6rem;font-weight:800;color:#ea580c;"><?= e((string) ($summary['expiring_soon'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Expired</div><div style="font-size:1.6rem;font-weight:800;color:#b42318;"><?= e((string) ($summary['expired'] ?? 0)) ?></div></div>
        <div class="metric" style="min-height:80px;"><div class="eyebrow">Archived</div><div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['archived'] ?? 0)) ?></div></div>
    </div>

    <form method="get" action="<?= e(url('/dsc')) ?>" class="search-bar">
        <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Search by holder name, PAN, token serial, or client...">
        <select name="custody_status" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
            <option value="">All Custody</option>
            <option value="WITH_CLIENT" <?= ($filters['custody_status'] ?? '') === 'WITH_CLIENT' ? 'selected' : '' ?>>With Client</option>
            <option value="WITH_OFFICE" <?= ($filters['custody_status'] ?? '') === 'WITH_OFFICE' ? 'selected' : '' ?>>With Office</option>
            <option value="WITH_STAFF" <?= ($filters['custody_status'] ?? '') === 'WITH_STAFF' ? 'selected' : '' ?>>With Staff</option>
            <option value="RETURNED" <?= ($filters['custody_status'] ?? '') === 'RETURNED' ? 'selected' : '' ?>>Returned</option>
        </select>
        <button type="submit" class="button">Search</button>
    </form>

    <?php if (($dscList['items'] ?? []) === []): ?>
        <div class="data-card" style="text-align:center;padding:40px;"><div class="eyebrow">No Results</div><p class="subtle" style="margin:8px 0 0;">No DSC records found.</p></div>
    <?php else: ?>
        <div class="card-grid">
            <?php foreach ($dscList['items'] as $dsc): ?>
                <?php
                $isExpired = !empty($dsc['valid_to']) && strtotime($dsc['valid_to']) < time();
                $isExpiringSoon = !$isExpired && !empty($dsc['valid_to']) && strtotime($dsc['valid_to']) < strtotime('+30 days');
                ?>
                <article class="data-card">
                    <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                        <div>
                            <div class="eyebrow"><?= e($dsc['holder_name']) ?></div>
                            <h4 style="margin:4px 0 0;"><?= e($dsc['dsc_type'] ?: 'DSC') ?></h4>
                        </div>
                        <span class="chip <?= $isExpired ? 'chip-strong' : ($isExpiringSoon ? '' : '') ?>"><?= $isExpired ? 'Expired' : ($isExpiringSoon ? 'Expiring Soon' : e($dsc['custody_status'])) ?></span>
                    </div>
                    <div class="stat-line"><span>Client</span><strong><?= e($dsc['client_name'] ?: '-') ?></strong></div>
                    <div class="stat-line"><span>PAN</span><strong><?= e($dsc['holder_pan'] ?: '-') ?></strong></div>
                    <div class="stat-line"><span>Token</span><strong><?= e($dsc['token_serial_no'] ?: '-') ?></strong></div>
                    <div class="stat-line"><span>Valid To</span><strong><?= e($dsc['valid_to'] ?: '-') ?></strong></div>
                    <div class="stat-line"><span>Assigned</span><strong><?= e($dsc['assigned_user_name'] ?: '-') ?></strong></div>
                    <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:10px;flex-wrap:wrap;">
                        <a href="<?= e(url('/dsc/show?id=' . $dsc['id'])) ?>" class="button button-secondary">View</a>
                        <?php if (\App\Core\Auth::can('dsc.edit')): ?>
                            <a href="<?= e(url('/dsc/edit?id=' . $dsc['id'])) ?>" class="button button-secondary">Edit</a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

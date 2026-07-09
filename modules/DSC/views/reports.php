<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar"><div><div class="eyebrow">DSC Module</div><h3 style="margin:0 0 6px;">DSC Reports</h3><div class="subtle">DSC expiry, custody, and usage overview.</div></div></div>

    <form method="get" action="<?= e(url('/dsc/reports')) ?>" class="search-bar">
        <select name="custody_status" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;"><option value="">All Custody</option><option value="WITH_CLIENT" <?= ($filters['custody_status'] ?? '') === 'WITH_CLIENT' ? 'selected' : '' ?>>With Client</option><option value="WITH_OFFICE" <?= ($filters['custody_status'] ?? '') === 'WITH_OFFICE' ? 'selected' : '' ?>>With Office</option><option value="WITH_STAFF" <?= ($filters['custody_status'] ?? '') === 'WITH_STAFF' ? 'selected' : '' ?>>With Staff</option><option value="RETURNED" <?= ($filters['custody_status'] ?? '') === 'RETURNED' ? 'selected' : '' ?>>Returned</option><option value="ARCHIVED" <?= ($filters['custody_status'] ?? '') === 'ARCHIVED' ? 'selected' : '' ?>>Archived</option></select>
        <button type="submit" class="button">Filter</button>
    </form>

    <?php if ($dscList === []): ?><div class="data-card" style="text-align:center;padding:40px;"><div class="eyebrow">No Data</div><p class="subtle" style="margin:8px 0 0;">No DSC records found.</p></div><?php else: ?>
        <div style="overflow:auto;"><table><thead><tr><th>Holder</th><th>Client</th><th>PAN</th><th>Type</th><th>Valid To</th><th>Custody</th><th>Assigned</th></tr></thead><tbody>
        <?php foreach ($dscList as $dsc): ?>
            <?php $isExpired = !empty($dsc['valid_to']) && strtotime($dsc['valid_to']) < time(); ?>
            <tr><td><strong><?= e($dsc['holder_name']) ?></strong></td><td><?= e($dsc['client_name'] ?: '-') ?></td><td><?= e($dsc['holder_pan'] ?: '-') ?></td><td><?= e($dsc['dsc_type'] ?: '-') ?></td><td style="color:<?= $isExpired ? '#b42318' : '' ?>;"><?= e($dsc['valid_to'] ?: '-') ?><?= $isExpired ? ' (Expired)' : '' ?></td><td><span class="chip"><?= e($dsc['custody_status']) ?></span></td><td><?= e($dsc['assigned_user_name'] ?: '-') ?></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>

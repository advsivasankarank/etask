<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar"><div><div class="eyebrow">DSC Module</div><h3 style="margin:0 0 6px;">DSC Renewals</h3><div class="subtle">Track DSC expiry dates and renewal status.</div></div></div>

    <form method="get" action="<?= e(url('/dsc/renewals')) ?>" class="search-bar">
        <select name="status" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;"><option value="">All Status</option><option value="NOT_DUE" <?= ($filters['status'] ?? '') === 'NOT_DUE' ? 'selected' : '' ?>>Not Due</option><option value="DUE" <?= ($filters['status'] ?? '') === 'DUE' ? 'selected' : '' ?>>Due</option><option value="IN_PROGRESS" <?= ($filters['status'] ?? '') === 'IN_PROGRESS' ? 'selected' : '' ?>>In Progress</option><option value="RENEWED" <?= ($filters['status'] ?? '') === 'RENEWED' ? 'selected' : '' ?>>Renewed</option><option value="EXPIRED" <?= ($filters['status'] ?? '') === 'EXPIRED' ? 'selected' : '' ?>>Expired</option></select>
        <button type="submit" class="button">Filter</button>
    </form>

    <?php if (($renewals['items'] ?? []) === []): ?><div class="data-card" style="text-align:center;padding:40px;"><div class="eyebrow">No Renewals</div><p class="subtle" style="margin:8px 0 0;">No renewal records found.</p></div><?php else: ?>
        <div style="overflow:auto;"><table><thead><tr><th>DSC Holder</th><th>Client</th><th>Valid To</th><th>Status</th><th>New Valid From</th><th>New Valid To</th><th>Remarks</th></tr></thead><tbody>
        <?php foreach ($renewals['items'] as $rn): ?><tr><td><strong><?= e($rn['holder_name'] ?: '-') ?></strong></td><td><?= e($rn['client_name'] ?: '-') ?></td><td><?= e($rn['valid_to'] ?: '-') ?></td><td><span class="chip <?= $rn['renewal_status'] === 'RENEWED' ? '' : ($rn['renewal_status'] === 'EXPIRED' ? 'chip-strong' : '') ?>"><?= e($rn['renewal_status']) ?></span></td><td><?= e($rn['new_valid_from'] ?: '-') ?></td><td><?= e($rn['new_valid_to'] ?: '-') ?></td><td><?= e($rn['remarks'] ?: '-') ?></td></tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>

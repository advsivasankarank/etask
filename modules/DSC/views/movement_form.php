<?php $old = is_array($old ?? null) ? $old : []; ?>
<section class="panel">
    <div class="toolbar"><div><div class="eyebrow">DSC Module</div><h3 style="margin:0 0 6px;">Record DSC Movement</h3><div class="subtle">Track when a DSC is moved, assigned, or transferred.</div></div><a href="<?= e(url('/dsc/movement')) ?>" class="button button-secondary">Back</a></div>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= e(url('/dsc/movement')) ?>" style="display:grid;gap:18px;">
        <?= \App\Core\Csrf::inputField() ?>
        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <div class="eyebrow">Movement Details</div>
            <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
                <label style="display:grid;gap:8px;"><span>DSC *</span><select name="dsc_id" required><option value="">Select DSC</option><?php foreach ($dscList as $dsc): ?><option value="<?= e((string) $dsc['id']) ?>" <?= (string) ($old['dsc_id'] ?? '') === (string) $dsc['id'] ? 'selected' : '' ?>><?= e($dsc['holder_name']) ?> (<?= e($dsc['client_name'] ?: 'General') ?>)</option><?php endforeach; ?></select></label>
                <label style="display:grid;gap:8px;"><span>Movement Type *</span><select name="movement_type" required><option value="RECEIVED">Received</option><option value="ASSIGNED">Assigned</option><option value="TRANSFERRED" selected>Transferred</option><option value="RETURNED">Returned</option><option value="ARCHIVED">Archived</option></select></label>
                <label style="display:grid;gap:8px;"><span>From Location</span><input type="text" name="from_location" value="<?= e($old['from_location'] ?? '') ?>"></label>
                <label style="display:grid;gap:8px;"><span>To Location</span><input type="text" name="to_location" value="<?= e($old['to_location'] ?? '') ?>"></label>
                <label style="display:grid;gap:8px;"><span>Purpose</span><input type="text" name="purpose" value="<?= e($old['purpose'] ?? '') ?>"></label>
                <label style="display:grid;gap:8px;"><span>Expected Return Date</span><input type="date" name="expected_return_date" value="<?= e($old['expected_return_date'] ?? '') ?>"></label>
            </div>
        </div>
        <label style="display:grid;gap:8px;"><span>Remarks</span><textarea name="remarks" rows="3" placeholder="Additional notes"><?= e($old['remarks'] ?? '') ?></textarea></label>
        <div style="display:flex;gap:12px;flex-wrap:wrap;"><button type="submit" class="button">Record Movement</button><a href="<?= e(url('/dsc/movement')) ?>" class="button button-secondary">Cancel</a></div>
    </form>
</section>

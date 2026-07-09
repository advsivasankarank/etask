<?php $old = is_array($old ?? null) ? $old : []; $conData = $consultant ?? []; ?>
<section class="panel">
    <div class="toolbar"><div><div class="eyebrow">Workforce Module</div><h3 style="margin:0 0 6px;"><?= $mode === 'edit' ? 'Edit Consultant' : 'Add Consultant' ?></h3><div class="subtle">Register or update consultant details.</div></div><a href="<?= e(url('/workforce/consultants')) ?>" class="button button-secondary">Back</a></div>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= e($mode === 'edit' ? url('/workforce/consultants/update') : url('/workforce/consultants')) ?>" style="display:grid;gap:18px;">
        <?= \App\Core\Csrf::inputField() ?>
        <?php if ($mode === 'edit'): ?><input type="hidden" name="id" value="<?= e((string) $conData['id']) ?>"><?php endif; ?>
        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <div class="eyebrow">Consultant Details</div>
            <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
                <label style="display:grid;gap:8px;"><span>Name *</span><input type="text" name="name" value="<?= e($old['name'] ?? $conData['name'] ?? '') ?>" required></label>
                <label style="display:grid;gap:8px;"><span>Firm Name</span><input type="text" name="firm_name" value="<?= e($old['firm_name'] ?? $conData['firm_name'] ?? '') ?>"></label>
                <label style="display:grid;gap:8px;"><span>Mobile</span><input type="text" name="mobile" value="<?= e($old['mobile'] ?? $conData['mobile'] ?? '') ?>"></label>
                <label style="display:grid;gap:8px;"><span>Email</span><input type="email" name="email" value="<?= e($old['email'] ?? $conData['email'] ?? '') ?>"></label>
                <label style="display:grid;gap:8px;"><span>PAN</span><input type="text" name="pan" value="<?= e($old['pan'] ?? $conData['pan'] ?? '') ?>" style="text-transform:uppercase;"></label>
                <label style="display:grid;gap:8px;"><span>GSTIN</span><input type="text" name="gstin" value="<?= e($old['gstin'] ?? $conData['gstin'] ?? '') ?>"></label>
                <label style="display:grid;gap:8px;"><span>Expertise</span><input type="text" name="expertise" value="<?= e($old['expertise'] ?? $conData['expertise'] ?? '') ?>" placeholder="e.g., ITR, GST, ROC"></label>
                <label style="display:grid;gap:8px;"><span>Status</span><select name="status"><option value="ACTIVE" <?= ($old['status'] ?? $conData['status'] ?? 'ACTIVE') === 'ACTIVE' ? 'selected' : '' ?>>Active</option><option value="INACTIVE" <?= ($old['status'] ?? $conData['status'] ?? '') === 'INACTIVE' ? 'selected' : '' ?>>Inactive</option></select></label>
            </div>
        </div>
        <label style="display:grid;gap:8px;"><span>Address</span><textarea name="address" rows="3" placeholder="Consultant address"><?= e($old['address'] ?? $conData['address'] ?? '') ?></textarea></label>
        <label style="display:grid;gap:8px;"><span>Remarks</span><textarea name="remarks" rows="2" placeholder="Additional notes"><?= e($old['remarks'] ?? $conData['remarks'] ?? '') ?></textarea></label>
        <div style="display:flex;gap:12px;flex-wrap:wrap;"><button type="submit" class="button"><?= $mode === 'edit' ? 'Update Consultant' : 'Add Consultant' ?></button><a href="<?= e(url('/workforce/consultants')) ?>" class="button button-secondary">Cancel</a></div>
    </form>
</section>

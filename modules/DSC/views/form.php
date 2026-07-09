<?php $old = is_array($old ?? null) ? $old : []; $dscData = $dsc ?? []; ?>
<section class="panel">
    <div class="toolbar">
        <div>
            <div class="eyebrow">DSC Module</div>
            <h3 style="margin:0 0 6px;"><?= $mode === 'edit' ? 'Edit DSC' : 'Add DSC' ?></h3>
            <div class="subtle">Register a new Digital Signature Certificate or update existing details.</div>
        </div>
        <a href="<?= e(url('/dsc')) ?>" class="button button-secondary">Back to Register</a>
    </div>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <form method="post" action="<?= e($mode === 'edit' ? url('/dsc/update') : url('/dsc')) ?>" style="display:grid;gap:18px;">
        <?= \App\Core\Csrf::inputField() ?>
        <?php if ($mode === 'edit'): ?><input type="hidden" name="id" value="<?= e((string) $dscData['id']) ?>"><?php endif; ?>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <div class="eyebrow">Holder Details</div>
            <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
                <label style="display:grid;gap:8px;"><span>Holder Name *</span><input type="text" name="holder_name" value="<?= e($old['holder_name'] ?? $dscData['holder_name'] ?? '') ?>" required></label>
                <label style="display:grid;gap:8px;"><span>PAN</span><input type="text" name="holder_pan" value="<?= e($old['holder_pan'] ?? $dscData['holder_pan'] ?? '') ?>" style="text-transform:uppercase;"></label>
                <label style="display:grid;gap:8px;"><span>Email</span><input type="email" name="holder_email" value="<?= e($old['holder_email'] ?? $dscData['holder_email'] ?? '') ?>"></label>
                <label style="display:grid;gap:8px;"><span>Mobile</span><input type="text" name="holder_mobile" value="<?= e($old['holder_mobile'] ?? $dscData['holder_mobile'] ?? '') ?>"></label>
            </div>
        </div>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <div class="eyebrow">DSC Details</div>
            <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
                <label style="display:grid;gap:8px;"><span>Client</span><select name="client_id"><option value="">Select client</option><?php foreach ($clients as $client): ?><option value="<?= e((string) $client['id']) ?>" <?= (string) ($old['client_id'] ?? $dscData['client_id'] ?? '') === (string) $client['id'] ? 'selected' : '' ?>><?= e($client['legal_name']) ?></option><?php endforeach; ?></select></label>
                <label style="display:grid;gap:8px;"><span>Token Serial No.</span><input type="text" name="token_serial_no" value="<?= e($old['token_serial_no'] ?? $dscData['token_serial_no'] ?? '') ?>"></label>
                <label style="display:grid;gap:8px;"><span>DSC Type</span><input type="text" name="dsc_type" value="<?= e($old['dsc_type'] ?? $dscData['dsc_type'] ?? '') ?>" placeholder="e.g., Class 3"></label>
                <label style="display:grid;gap:8px;"><span>Provider</span><input type="text" name="provider_name" value="<?= e($old['provider_name'] ?? $dscData['provider_name'] ?? '') ?>"></label>
                <label style="display:grid;gap:8px;"><span>Valid From</span><input type="date" name="valid_from" value="<?= e($old['valid_from'] ?? $dscData['valid_from'] ?? '') ?>"></label>
                <label style="display:grid;gap:8px;"><span>Valid To</span><input type="date" name="valid_to" value="<?= e($old['valid_to'] ?? $dscData['valid_to'] ?? '') ?>"></label>
            </div>
        </div>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <div class="eyebrow">Custody Details</div>
            <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
                <label style="display:grid;gap:8px;"><span>Custody Status</span><select name="custody_status"><option value="WITH_CLIENT" <?= ($old['custody_status'] ?? $dscData['custody_status'] ?? '') === 'WITH_CLIENT' ? 'selected' : '' ?>>With Client</option><option value="WITH_OFFICE" <?= ($old['custody_status'] ?? $dscData['custody_status'] ?? '') === 'WITH_OFFICE' ? 'selected' : '' ?>>With Office</option><option value="WITH_STAFF" <?= ($old['custody_status'] ?? $dscData['custody_status'] ?? '') === 'WITH_STAFF' ? 'selected' : '' ?>>With Staff</option><option value="RETURNED" <?= ($old['custody_status'] ?? $dscData['custody_status'] ?? '') === 'RETURNED' ? 'selected' : '' ?>>Returned</option></select></label>
                <label style="display:grid;gap:8px;"><span>Storage Location</span><input type="text" name="storage_location" value="<?= e($old['storage_location'] ?? $dscData['storage_location'] ?? '') ?>"></label>
                <label style="display:grid;gap:8px;"><span>Password Status</span><select name="password_status"><option value="NOT_STORED" <?= ($old['password_status'] ?? $dscData['password_status'] ?? '') === 'NOT_STORED' ? 'selected' : '' ?>>Not Stored</option><option value="CLIENT_RETAINED" <?= ($old['password_status'] ?? $dscData['password_status'] ?? '') === 'CLIENT_RETAINED' ? 'selected' : '' ?>>Client Retained</option><option value="SECURE_CUSTODY" <?= ($old['password_status'] ?? $dscData['password_status'] ?? '') === 'SECURE_CUSTODY' ? 'selected' : '' ?>>Secure Custody</option></select></label>
            </div>
        </div>

        <label style="display:grid;gap:8px;"><span>Remarks</span><textarea name="remarks" rows="3" placeholder="Additional notes"><?= e($old['remarks'] ?? $dscData['remarks'] ?? '') ?></textarea></label>

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="button"><?= $mode === 'edit' ? 'Update DSC' : 'Register DSC' ?></button>
            <a href="<?= e(url('/dsc')) ?>" class="button button-secondary">Cancel</a>
        </div>
    </form>
</section>

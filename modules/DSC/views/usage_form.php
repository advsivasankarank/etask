<?php $old = is_array($old ?? null) ? $old : []; ?>
<section class="panel">
    <div class="toolbar"><div><div class="eyebrow">DSC Module</div><h3 style="margin:0 0 6px;">Log DSC Usage</h3><div class="subtle">Record when a DSC is used for filing, signing, or portal access.</div></div><a href="<?= e(url('/dsc/usage')) ?>" class="button button-secondary">Back</a></div>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= e(url('/dsc/usage')) ?>" style="display:grid;gap:18px;">
        <?= \App\Core\Csrf::inputField() ?>
        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <div class="eyebrow">Usage Details</div>
            <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
                <label style="display:grid;gap:8px;"><span>DSC *</span><select name="dsc_id" required><option value="">Select DSC</option><?php foreach ($dscList as $dsc): ?><option value="<?= e((string) $dsc['id']) ?>" <?= (string) ($old['dsc_id'] ?? '') === (string) $dsc['id'] ? 'selected' : '' ?>><?= e($dsc['holder_name']) ?> (<?= e($dsc['client_name'] ?: 'General') ?>)</option><?php endforeach; ?></select></label>
                <label style="display:grid;gap:8px;"><span>Client</span><select name="client_id"><option value="">Select client</option><?php foreach ($clients as $client): ?><option value="<?= e((string) $client['id']) ?>" <?= (string) ($old['client_id'] ?? '') === (string) $client['id'] ? 'selected' : '' ?>><?= e($client['legal_name']) ?></option><?php endforeach; ?></select></label>
                <label style="display:grid;gap:8px;"><span>Purpose *</span><input type="text" name="purpose" value="<?= e($old['purpose'] ?? '') ?>" placeholder="e.g., ITR Filing" required></label>
                <label style="display:grid;gap:8px;"><span>Portal / Department</span><input type="text" name="portal_or_department" value="<?= e($old['portal_or_department'] ?? '') ?>" placeholder="e.g., Income Tax Portal"></label>
                <label style="display:grid;gap:8px;"><span>Filing Reference</span><input type="text" name="filing_reference" value="<?= e($old['filing_reference'] ?? '') ?>"></label>
                <label style="display:grid;gap:8px;"><span>Acknowledgement No.</span><input type="text" name="acknowledgement_no" value="<?= e($old['acknowledgement_no'] ?? '') ?>"></label>
            </div>
        </div>
        <label style="display:grid;gap:8px;"><span>Remarks</span><textarea name="remarks" rows="3" placeholder="Additional notes"><?= e($old['remarks'] ?? '') ?></textarea></label>
        <div style="display:flex;gap:12px;flex-wrap:wrap;"><button type="submit" class="button">Log Usage</button><a href="<?= e(url('/dsc/usage')) ?>" class="button button-secondary">Cancel</a></div>
    </form>
</section>

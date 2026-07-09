<?php $old = is_array($old ?? null) ? $old : []; ?>
<section class="panel">
    <div class="toolbar">
        <div>
            <div class="eyebrow">Document Module</div>
            <h3 style="margin:0 0 6px;">Record Document Movement</h3>
            <div class="subtle">Track when a document is moved, assigned, or transferred.</div>
        </div>
        <a href="<?= e(url('/documents/movement')) ?>" class="button button-secondary">Back to Movement Register</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= e(url('/documents/movement')) ?>" style="display:grid;gap:18px;">
        <?= \App\Core\Csrf::inputField() ?>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <div class="eyebrow">Document Selection</div>
            <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
                <label style="display:grid;gap:8px;">
                    <span>Document *</span>
                    <select name="document_id" required>
                        <option value="">Select document</option>
                        <?php foreach ($documents as $doc): ?>
                            <option value="<?= e((string) $doc['id']) ?>" <?= (string) ($old['document_id'] ?? '') === (string) $doc['id'] ? 'selected' : '' ?>><?= e($doc['document_name']) ?> (<?= e($doc['client_name'] ?: 'General') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label style="display:grid;gap:8px;">
                    <span>Movement Type *</span>
                    <select name="movement_type" required>
                        <option value="RECEIVED" <?= ($old['movement_type'] ?? '') === 'RECEIVED' ? 'selected' : '' ?>>Received</option>
                        <option value="ASSIGNED" <?= ($old['movement_type'] ?? '') === 'ASSIGNED' ? 'selected' : '' ?>>Assigned</option>
                        <option value="TRANSFERRED" <?= ($old['movement_type'] ?? 'TRANSFERRED') === 'TRANSFERRED' ? 'selected' : '' ?>>Transferred</option>
                        <option value="USED_FOR_WORK" <?= ($old['movement_type'] ?? '') === 'USED_FOR_WORK' ? 'selected' : '' ?>>Used for Work</option>
                        <option value="RETURNED" <?= ($old['movement_type'] ?? '') === 'RETURNED' ? 'selected' : '' ?>>Returned</option>
                        <option value="ARCHIVED" <?= ($old['movement_type'] ?? '') === 'ARCHIVED' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </label>
            </div>
        </div>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <div class="eyebrow">Movement Details</div>
            <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
                <label style="display:grid;gap:8px;">
                    <span>From Location / User</span>
                    <input type="text" name="from_location" value="<?= e($old['from_location'] ?? '') ?>" placeholder="e.g., Office Reception">
                </label>
                <label style="display:grid;gap:8px;">
                    <span>To Location / User</span>
                    <input type="text" name="to_location" value="<?= e($old['to_location'] ?? '') ?>" placeholder="e.g., Backend Team">
                </label>
                <label style="display:grid;gap:8px;">
                    <span>Purpose</span>
                    <input type="text" name="purpose" value="<?= e($old['purpose'] ?? '') ?>" placeholder="e.g., For ITR filing">
                </label>
                <label style="display:grid;gap:8px;">
                    <span>Expected Return Date</span>
                    <input type="date" name="expected_return_date" value="<?= e($old['expected_return_date'] ?? '') ?>">
                </label>
            </div>
        </div>

        <label style="display:grid;gap:8px;">
            <span>Remarks</span>
            <textarea name="remarks" rows="3" placeholder="Additional notes about this movement"><?= e($old['remarks'] ?? '') ?></textarea>
        </label>

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="button">Record Movement</button>
            <a href="<?= e(url('/documents/movement')) ?>" class="button button-secondary">Cancel</a>
        </div>
    </form>
</section>

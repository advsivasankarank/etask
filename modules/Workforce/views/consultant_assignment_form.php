<?php $old = is_array($old ?? null) ? $old : []; ?>
<section class="panel">
    <div class="toolbar"><div><div class="eyebrow">Workforce Module</div><h3 style="margin:0 0 6px;">Create Assignment</h3><div class="subtle">Assign work to a consultant.</div></div><a href="<?= e(url('/workforce/consultant-assignments')) ?>" class="button button-secondary">Back</a></div>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= e(url('/workforce/consultant-assignments')) ?>" style="display:grid;gap:18px;">
        <?= \App\Core\Csrf::inputField() ?>
        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <div class="eyebrow">Assignment Details</div>
            <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
                <label style="display:grid;gap:8px;"><span>Consultant *</span><select name="consultant_id" required><option value="">Select consultant</option><?php foreach ($consultants as $c): ?><option value="<?= e((string) $c['id']) ?>" <?= (string) ($old['consultant_id'] ?? '') === (string) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?><?= $c['firm_name'] ? ' (' . e($c['firm_name']) . ')' : '' ?></option><?php endforeach; ?></select></label>
                <label style="display:grid;gap:8px;"><span>Assignment Title *</span><input type="text" name="assignment_title" value="<?= e($old['assignment_title'] ?? '') ?>" required></label>
                <label style="display:grid;gap:8px;"><span>Due Date</span><input type="date" name="due_date" value="<?= e($old['due_date'] ?? '') ?>"></label>
                <label style="display:grid;gap:8px;"><span>Fee Agreed (INR)</span><input type="number" name="fee_agreed" value="<?= e((string) ($old['fee_agreed'] ?? '')) ?>" step="0.01"></label>
            </div>
        </div>
        <label style="display:grid;gap:8px;"><span>Description</span><textarea name="assignment_description" rows="3" placeholder="Assignment details"><?= e($old['assignment_description'] ?? '') ?></textarea></label>
        <label style="display:grid;gap:8px;"><span>Remarks</span><textarea name="remarks" rows="2" placeholder="Internal notes"><?= e($old['remarks'] ?? '') ?></textarea></label>
        <div style="display:flex;gap:12px;flex-wrap:wrap;"><button type="submit" class="button">Create Assignment</button><a href="<?= e(url('/workforce/consultant-assignments')) ?>" class="button button-secondary">Cancel</a></div>
    </form>
</section>

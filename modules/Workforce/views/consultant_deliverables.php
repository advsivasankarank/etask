<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar"><div><div class="eyebrow">Workforce Module</div><h3 style="margin:0 0 6px;">Consultant Deliverables</h3><div class="subtle">Track deliverable submissions and reviews.</div></div></div>

    <form method="get" action="<?= e(url('/workforce/consultant-deliverables')) ?>" class="search-bar">
        <select name="status" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;"><option value="">All Statuses</option><?php foreach (['PENDING','SUBMITTED','APPROVED','REWORK','REJECTED'] as $s): ?><option value="<?= e($s) ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= e(label_case($s)) ?></option><?php endforeach; ?></select>
        <button type="submit" class="button">Filter</button>
    </form>

    <?php if (($deliverables['items'] ?? []) === []): ?><div class="data-card" style="text-align:center;padding:40px;"><div class="eyebrow">No Deliverables</div><p class="subtle" style="margin:8px 0 0;">No deliverables found.</p></div><?php else: ?>
        <div style="overflow:auto;"><table><thead><tr><th>Consultant</th><th>Assignment</th><th>Deliverable</th><th>Submitted</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($deliverables['items'] as $d): ?><tr><td><strong><?= e($d['consultant_name'] ?: '-') ?></strong></td><td><?= e($d['assignment_title'] ?: '-') ?></td><td><?= e($d['deliverable_title']) ?></td><td><?= e($d['submitted_at'] ?: '-') ?></td><td><span class="chip <?= $d['status'] === 'APPROVED' ? '' : ($d['status'] === 'REJECTED' ? 'chip-strong' : '') ?>"><?= e(label_case((string) $d['status'])) ?></span></td><td><?php if (\App\Core\Auth::can('workforce.consultants.manage')): ?><form method="post" action="<?= e(url('/workforce/consultant-deliverables/status')) ?>" style="display:inline;"><?= \App\Core\Csrf::inputField() ?><input type="hidden" name="deliverable_id" value="<?= e((string) $d['id']) ?>"><select name="status" onchange="this.form.submit()" style="padding:4px 8px;font-size:0.78rem;border:1px solid #d8e1eb;border-radius:8px;"><option value="">Update</option><?php foreach (['PENDING','SUBMITTED','APPROVED','REWORK','REJECTED'] as $s): ?><option value="<?= e($s) ?>"><?= e(label_case($s)) ?></option><?php endforeach; ?></select></form><?php endif; ?></td></tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>

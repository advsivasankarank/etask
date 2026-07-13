<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar"><div><div class="eyebrow">Workforce Module</div><h3 style="margin:0 0 6px;">Consultant Assignments</h3><div class="subtle">Track consultant assignments and status.</div></div>
        <?php if (\App\Core\Auth::can('workforce.consultants.manage')): ?><a href="<?= e(url('/workforce/consultant-assignments/create')) ?>" class="button">+ Create Assignment</a><?php endif; ?>
    </div>

    <form method="get" action="<?= e(url('/workforce/consultant-assignments')) ?>" class="search-bar">
        <select name="status" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;"><option value="">All Statuses</option><?php foreach (['ASSIGNED','IN_PROGRESS','DELIVERED','APPROVED','REWORK','CANCELLED'] as $s): ?><option value="<?= e($s) ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= e(label_case($s)) ?></option><?php endforeach; ?></select>
        <button type="submit" class="button">Filter</button>
    </form>

    <?php if (($assignments['items'] ?? []) === []): ?><div class="data-card" style="text-align:center;padding:40px;"><div class="eyebrow">No Assignments</div><p class="subtle" style="margin:8px 0 0;">No assignments found.</p></div><?php else: ?>
        <div style="overflow:auto;"><table><thead><tr><th>Consultant</th><th>Title</th><th>SO</th><th>Client</th><th>Due</th><th>Status</th><th>Fee</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($assignments['items'] as $a): ?><tr><td><strong><?= e($a['consultant_name'] ?: '-') ?></strong></td><td><?= e($a['assignment_title']) ?></td><td><?= e($a['so_no'] ?: '-') ?></td><td><?= e($a['client_name'] ?: '-') ?></td><td><?= e($a['due_date'] ?: '-') ?></td><td><span class="chip"><?= e(label_case((string) $a['status'])) ?></span></td><td><?= $a['fee_agreed'] ? e(money_inr($a['fee_agreed'])) : '-' ?></td><td><?php if (\App\Core\Auth::can('workforce.consultants.manage')): ?><form method="post" action="<?= e(url('/workforce/consultant-assignments/status')) ?>" style="display:inline;"><?= \App\Core\Csrf::inputField() ?><input type="hidden" name="assignment_id" value="<?= e((string) $a['id']) ?>"><select name="status" onchange="this.form.submit()" style="padding:4px 8px;font-size:0.78rem;border:1px solid #d8e1eb;border-radius:8px;"><option value="">Update</option><?php foreach (['ASSIGNED','IN_PROGRESS','DELIVERED','APPROVED','REWORK','CANCELLED'] as $s): ?><option value="<?= e($s) ?>"><?= e(label_case($s)) ?></option><?php endforeach; ?></select></form><?php endif; ?></td></tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>

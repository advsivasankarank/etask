<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar"><div><div class="eyebrow">Accounts Module</div><h3 style="margin:0 0 6px;">Collection Follow-up</h3><div class="subtle">Track collection follow-up activities.</div></div></div>

    <form method="post" action="<?= e(url('/accounts/followups')) ?>" style="margin-bottom:20px;padding:18px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;">
        <?= \App\Core\Csrf::inputField() ?>
        <div class="eyebrow" style="margin-bottom:12px;">New Follow-up</div>
        <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));">
            <label style="display:grid;gap:8px;"><span>Follow-up Date *</span><input type="date" name="followup_date" value="<?= e(date('Y-m-d')) ?>" required></label>
            <label style="display:grid;gap:8px;"><span>Mode</span><input type="text" name="followup_mode" placeholder="e.g., Phone, Email"></label>
            <label style="display:grid;gap:8px;"><span>Note *</span><input type="text" name="followup_note" placeholder="Follow-up note" required></label>
            <label style="display:grid;gap:8px;"><span>Next Follow-up</span><input type="date" name="next_followup_date"></label>
        </div>
        <button type="submit" class="button" style="margin-top:12px;">Record Follow-up</button>
    </form>

    <form method="get" action="<?= e(url('/accounts/followups')) ?>" class="search-bar">
        <select name="status" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;"><option value="">All Statuses</option><?php foreach (['OPEN','FOLLOWED_UP','PROMISED','DISPUTED','CLOSED'] as $s): ?><option value="<?= e($s) ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= e(label_case($s)) ?></option><?php endforeach; ?></select>
        <button type="submit" class="button">Filter</button>
    </form>

    <?php if (($followups['items'] ?? []) === []): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🔍</div>
            <div class="empty-state-title">No collection follow-ups found</div>
            <div class="empty-state-text">No follow-ups match the current filters. Adjust the filters or add a follow-up to an outstanding invoice.</div>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead class="table-header"><tr><th>Date</th><th>Client</th><th>Invoice</th><th>Mode</th><th>Note</th><th>Next</th><th>Status</th></tr></thead>
                <tbody class="table-body">
                <?php foreach ($followups['items'] as $f): ?>
                    <tr>
                        <td><?= e($f['followup_date']) ?></td>
                        <td><?= queue_cell_html('client_name', $f['client_name'] ?? '') ?></td>
                        <td><?= e($f['invoice_no'] ?: '—') ?></td>
                        <td><?= e($f['followup_mode'] ?: '—') ?></td>
                        <td><?= e($f['followup_note'] ?: '—') ?></td>
                        <td><?= e($f['next_followup_date'] ?: '—') ?></td>
                        <td><span class="badge badge-<?= e(status_severity((string) $f['status'])) ?>"><?= e(label_case((string) $f['status'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

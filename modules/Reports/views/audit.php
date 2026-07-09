<section class="panel">
    <div class="toolbar"><div><div class="eyebrow">Reports Module</div><h3 style="margin:0 0 6px;">Audit Reports</h3><div class="subtle">Activity logs and collection follow-up status.</div></div>
        <a href="<?= e(url('/reports')) ?>" class="button button-secondary">Back</a>
    </div>

    <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin-bottom:16px;">
        <h4 style="margin-top:0;">Recent Activity (7 days)</h4>
        <?php if (($activity ?? []) === []): ?><p class="subtle">No activity data.</p><?php else: ?>
            <div style="overflow:auto;"><table><thead><tr><th>Action</th><th>Count</th><th>Last At</th></tr></thead><tbody>
            <?php foreach ($activity as $a): ?>
                <tr><td><?= e($a['action_code']) ?></td><td><?= e((string) $a['count']) ?></td><td><?= e($a['last_at'] ?: '-') ?></td></tr>
            <?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </div>

    <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
        <h4 style="margin-top:0;">Pending Follow-ups</h4>
        <?php if (($followups ?? []) === []): ?><p class="subtle">No pending follow-ups.</p><?php else: ?>
            <div style="overflow:auto;"><table><thead><tr><th>Date</th><th>Client</th><th>Invoice</th><th>Note</th><th>Next</th><th>Status</th></tr></thead><tbody>
            <?php foreach ($followups as $f): ?>
                <tr><td><?= e($f['followup_date']) ?></td><td><?= e($f['client_name'] ?: '-') ?></td><td><?= e($f['invoice_no'] ?: '-') ?></td><td><?= e($f['followup_note'] ?: '-') ?></td><td><?= e($f['next_followup_date'] ?: '-') ?></td><td><span class="chip"><?= e($f['status']) ?></span></td></tr>
            <?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </div>
</section>

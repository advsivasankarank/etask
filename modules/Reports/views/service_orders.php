<section class="panel">
    <div class="toolbar"><div><div class="eyebrow">Reports Module</div><h3 style="margin:0 0 6px;">Service Order Reports</h3><div class="subtle">Active and recent service orders by status.</div></div>
        <a href="<?= e(url('/reports')) ?>" class="button button-secondary">Back</a>
    </div>

    <?php if (($report ?? []) === []): ?><div class="data-card" style="text-align:center;padding:40px;"><div class="eyebrow">No Data</div><p class="subtle" style="margin:8px 0 0;">No service order data found.</p></div><?php else: ?>
        <div style="overflow:auto;"><table><thead><tr><th>SO No.</th><th>Client</th><th>Service Type</th><th>Stage</th><th>Due</th><th>Status</th></tr></thead><tbody>
        <?php foreach ($report as $so): ?>
            <tr><td><strong><?= e($so['so_no']) ?></strong></td><td><?= e($so['client_name'] ?: '-') ?></td><td><?= e($so['service_type_name'] ?: '-') ?></td><td><?= e(str_replace('_', ' ', $so['current_stage_code'])) ?></td><td><?= e($so['sla_due_at'] ?: '-') ?></td><td><span class="chip <?= $so['status_label'] === 'Overdue' ? 'chip-strong' : '' ?>"><?= e($so['status_label']) ?></span></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>

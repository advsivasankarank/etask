<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar"><div><div class="eyebrow">Accounts Module</div><h3 style="margin:0 0 6px;">Unbilled Completed Work</h3><div class="subtle">Service orders that are completed but not yet invoiced.</div></div></div>

    <?php if ($workOrders === []): ?>
        <div class="empty-state">
            <div class="empty-state-icon">✅</div>
            <div class="empty-state-title">All caught up</div>
            <div class="empty-state-text">All completed work has been invoiced.</div>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead class="table-header"><tr><th>SO No.</th><th>Client</th><th>Service Type</th><th>Completed</th><th>Assigned CRM</th><th>Status</th></tr></thead>
                <tbody class="table-body">
                <?php foreach ($workOrders as $wo): ?>
                    <tr>
                        <td><strong><?= e($wo['so_no']) ?></strong></td>
                        <td><?= queue_cell_html('client_name', $wo['client_name'] ?? '') ?></td>
                        <td><?= e($wo['service_type_name'] ?: '—') ?></td>
                        <td><?= e($wo['final_closed_at'] ?: '—') ?></td>
                        <td><?= e($wo['assigned_crm_name'] ?: '—') ?></td>
                        <td><span class="badge badge-warning">Unbilled</span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

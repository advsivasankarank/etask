<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar"><div><div class="eyebrow">Accounts Module</div><h3 style="margin:0 0 6px;">Invoice Register</h3><div class="subtle">Track all invoices and their payment status.</div></div></div>

    <form method="get" action="<?= e(url('/accounts/invoices')) ?>" class="search-bar">
        <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Search by invoice no, client, or SO...">
        <select name="payment_status" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;"><option value="">All Status</option><option value="UNPAID" <?= ($filters['payment_status'] ?? '') === 'UNPAID' ? 'selected' : '' ?>>Unpaid</option><option value="PARTIALLY_PAID" <?= ($filters['payment_status'] ?? '') === 'PARTIALLY_PAID' ? 'selected' : '' ?>>Partially Paid</option><option value="PAID" <?= ($filters['payment_status'] ?? '') === 'PAID' ? 'selected' : '' ?>>Paid</option></select>
        <button type="submit" class="button">Search</button>
    </form>

    <?php if (($invoices['items'] ?? []) === []): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🔍</div>
            <div class="empty-state-title">No results</div>
            <div class="empty-state-text">No invoices found.</div>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead class="table-header"><tr><th>Invoice No.</th><th>Client</th><th>SO</th><th>Date</th><th>Due</th><th>Net Payable</th><th>Status</th><th></th></tr></thead>
                <tbody class="table-body">
                <?php foreach ($invoices['items'] as $inv): ?>
                    <tr>
                        <td><strong><?= e($inv['invoice_no']) ?></strong></td>
                        <td><?= queue_cell_html('client_name', $inv['client_name'] ?? '') ?></td>
                        <td><?= e($inv['so_no'] ?: '—') ?></td>
                        <td><?= e($inv['invoice_date']) ?></td>
                        <td><?= e($inv['due_date'] ?: '—') ?></td>
                        <td>INR <?= e(number_format((float) $inv['net_payable'], 2)) ?></td>
                        <td><span class="badge badge-<?= e(status_severity((string) $inv['payment_status'])) ?>"><?= e(label_case((string) $inv['payment_status'])) ?></span></td>
                        <td><?php if (\App\Core\Auth::can('billing.view')): ?><a href="<?= e(url('/billing/invoice?id=' . $inv['id'])) ?>" class="btn btn-secondary btn-sm">View</a><?php endif; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

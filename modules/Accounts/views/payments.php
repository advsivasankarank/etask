<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar"><div><div class="eyebrow">Accounts Module</div><h3 style="margin:0 0 6px;">Payment Register</h3><div class="subtle">Track all payments received from clients.</div></div></div>

    <form method="get" action="<?= e(url('/accounts/payments')) ?>" class="search-bar">
        <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Search by client, reference, or SO...">
        <select name="transaction_type" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;"><option value="">All Types</option><option value="ADVANCE" <?= ($filters['transaction_type'] ?? '') === 'ADVANCE' ? 'selected' : '' ?>>Advance</option><option value="INVOICE_PAYMENT" <?= ($filters['transaction_type'] ?? '') === 'INVOICE_PAYMENT' ? 'selected' : '' ?>>Invoice Payment</option><option value="REFUND" <?= ($filters['transaction_type'] ?? '') === 'REFUND' ? 'selected' : '' ?>>Refund</option></select>
        <button type="submit" class="button">Search</button>
    </form>

    <?php if (($payments['items'] ?? []) === []): ?><div class="data-card" style="text-align:center;padding:40px;"><div class="eyebrow">No Payments</div><p class="subtle" style="margin:8px 0 0;">No payments found.</p></div><?php else: ?>
        <div style="overflow:auto;"><table><thead><tr><th>Date</th><th>Client</th><th>SO</th><th>Type</th><th>Amount</th><th>Mode</th><th>Reference</th><th>Status</th></tr></thead><tbody>
        <?php foreach ($payments['items'] as $p): ?>
            <tr>
                <td><?= e($p['payment_date']) ?></td>
                <td><?= e($p['client_name'] ?: '-') ?></td>
                <td><?= e($p['so_no'] ?: '-') ?></td>
                <td><span class="chip"><?= e($p['transaction_type']) ?></span></td>
                <td>INR <?= e(number_format((float) $p['amount'], 2)) ?></td>
                <td><?= e($p['payment_mode']) ?></td>
                <td><?= e($p['reference_no'] ?: '-') ?></td>
                <td><span class="chip <?= $p['status'] === 'SUCCESS' ? '' : 'chip-strong' ?>"><?= e($p['status']) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>

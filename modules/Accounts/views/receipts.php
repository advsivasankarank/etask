<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar"><div><div class="eyebrow">Accounts Module</div><h3 style="margin:0 0 6px;">Receipt Register</h3><div class="subtle">Track all receipts generated for payments.</div></div></div>

    <form method="get" action="<?= e(url('/accounts/receipts')) ?>" class="search-bar">
        <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Search by receipt no or client...">
        <button type="submit" class="button">Search</button>
    </form>

    <?php if (($receipts['items'] ?? []) === []): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🔍</div>
            <div class="empty-state-title">No receipts found</div>
            <div class="empty-state-text">No receipts match the current search. Adjust the search or record an eligible payment first.</div>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead class="table-header"><tr><th>Receipt No.</th><th>Client</th><th>Date</th><th>Amount</th><th>Mode</th><th>Reference</th></tr></thead>
                <tbody class="table-body">
                <?php foreach ($receipts['items'] as $r): ?>
                    <tr>
                        <td><strong><?= e($r['receipt_no']) ?></strong></td>
                        <td><?= queue_cell_html('client_name', $r['client_name'] ?? '') ?></td>
                        <td><?= e($r['receipt_date']) ?></td>
                        <td>INR <?= e(number_format((float) $r['receipt_amount'], 2)) ?></td>
                        <td><?= e($r['payment_mode'] ?: '—') ?></td>
                        <td><?= e($r['reference_no'] ?: '—') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

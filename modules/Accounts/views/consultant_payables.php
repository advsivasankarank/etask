<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar"><div><div class="eyebrow">Accounts Module</div><h3 style="margin:0 0 6px;">Consultant Payables</h3><div class="subtle">Outstanding amounts payable to consultants.</div></div></div>

    <?php if ($payables === []): ?>
        <div class="empty-state">
            <div class="empty-state-icon">✅</div>
            <div class="empty-state-title">No payables</div>
            <div class="empty-state-text">No consultant payables found.</div>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead class="table-header"><tr><th>Consultant</th><th>Assignment</th><th>Bill No.</th><th>Date</th><th>Total</th><th>Balance</th><th>Status</th></tr></thead>
                <tbody class="table-body">
                <?php foreach ($payables as $p): ?>
                    <tr>
                        <td><?= queue_cell_html('client_name', $p['consultant_name'] ?? '') ?></td>
                        <td><?= e($p['assignment_title'] ?: '—') ?></td>
                        <td><?= e($p['bill_no'] ?: '—') ?></td>
                        <td><?= e($p['bill_date'] ?: '—') ?></td>
                        <td>INR <?= e(number_format((float) ($p['total_amount'] ?? 0), 2)) ?></td>
                        <td>INR <?= e(number_format((float) ($p['balance_payable'] ?? 0), 2)) ?></td>
                        <td><span class="badge badge-<?= e(status_severity((string) $p['review_status'])) ?>"><?= e(label_case((string) $p['review_status'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

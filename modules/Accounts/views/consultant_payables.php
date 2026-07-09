<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar"><div><div class="eyebrow">Accounts Module</div><h3 style="margin:0 0 6px;">Consultant Payables</h3><div class="subtle">Outstanding amounts payable to consultants.</div></div></div>

    <?php if ($payables === []): ?><div class="data-card" style="text-align:center;padding:40px;"><div class="eyebrow">No Payables</div><p class="subtle" style="margin:8px 0 0;">No consultant payables found.</p></div><?php else: ?>
        <div style="overflow:auto;"><table><thead><tr><th>Consultant</th><th>Assignment</th><th>Bill No.</th><th>Date</th><th>Total</th><th>Balance</th><th>Status</th></tr></thead><tbody>
        <?php foreach ($payables as $p): ?>
            <tr>
                <td><strong><?= e($p['consultant_name'] ?: '-') ?></strong></td>
                <td><?= e($p['assignment_title'] ?: '-') ?></td>
                <td><?= e($p['bill_no'] ?: '-') ?></td>
                <td><?= e($p['bill_date'] ?: '-') ?></td>
                <td>INR <?= e(number_format((float) ($p['total_amount'] ?? 0), 2)) ?></td>
                <td>INR <?= e(number_format((float) ($p['balance_payable'] ?? 0), 2)) ?></td>
                <td><span class="chip"><?= e($p['review_status']) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>

<section class="panel">
    <div class="toolbar"><div><div class="eyebrow">Reports Module</div><h3 style="margin:0 0 6px;">Consultant Reports</h3><div class="subtle">Consultant summary, assignments, and payables.</div></div>
        <a href="<?= e(url('/reports')) ?>" class="button button-secondary">Back</a>
    </div>

    <?php if (($report ?? []) === []): ?><div class="data-card" style="text-align:center;padding:40px;"><div class="eyebrow">No Data</div><p class="subtle" style="margin:8px 0 0;">No consultant data found.</p></div><?php else: ?>
        <div style="overflow:auto;"><table><thead><tr><th>Consultant</th><th>Status</th><th>Pending</th><th>Balance</th></tr></thead><tbody>
        <?php foreach ($report as $c): ?>
            <tr><td><strong><?= e($c['name']) ?></strong></td><td><span class="chip"><?= e($c['status']) ?></span></td><td><?= e((string) $c['pending_assignments']) ?></td><td>INR <?= e(number_format((float) $c['balance_payable'], 2)) ?></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>

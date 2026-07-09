<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar"><div><div class="eyebrow">DSC Module</div><h3 style="margin:0 0 6px;">DSC Usage Log</h3><div class="subtle">Track when DSC is used for filing, signing, or portal access.</div></div>
        <?php if (\App\Core\Auth::can('dsc.usage.log')): ?><a href="<?= e(url('/dsc/usage/create')) ?>" class="button">+ Log Usage</a><?php endif; ?>
    </div>

    <?php if (($usageLogs['items'] ?? []) === []): ?><div class="data-card" style="text-align:center;padding:40px;"><div class="eyebrow">No Usage</div><p class="subtle" style="margin:8px 0 0;">No DSC usage logged.</p></div><?php else: ?>
        <div style="overflow:auto;"><table><thead><tr><th>DSC Holder</th><th>Client</th><th>SO</th><th>Purpose</th><th>Date</th><th>Reference</th></tr></thead><tbody>
        <?php foreach ($usageLogs['items'] as $log): ?><tr><td><strong><?= e($log['holder_name'] ?: '-') ?></strong></td><td><?= e($log['client_name'] ?: '-') ?></td><td><?= e($log['so_no'] ?: '-') ?></td><td><?= e($log['purpose']) ?></td><td><?= e($log['usage_date']) ?></td><td><?= e($log['filing_reference'] ?: $log['acknowledgement_no'] ?: '-') ?></td></tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>

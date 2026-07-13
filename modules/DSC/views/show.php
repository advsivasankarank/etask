<?php
$isExpired = !empty($dsc['valid_to']) && strtotime($dsc['valid_to']) < time();
$isExpiringSoon = !$isExpired && !empty($dsc['valid_to']) && strtotime($dsc['valid_to']) < strtotime('+30 days');
?>
<section class="panel">
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="toolbar">
        <div>
            <div class="eyebrow">DSC Module</div>
            <h3 style="margin:0 0 6px;"><?= e($dsc['holder_name']) ?></h3>
            <div class="subtle"><?= e($dsc['dsc_type'] ?: 'DSC') ?> | <?= e($dsc['client_name'] ?: 'No Client') ?> | <?= e($dsc['custody_status']) ?></div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <?php if (\App\Core\Auth::can('dsc.edit')): ?><a href="<?= e(url('/dsc/edit?id=' . $dsc['id'])) ?>" class="button">Edit</a><?php endif; ?>
            <?php if (\App\Core\Auth::can('dsc.movement.manage')): ?><a href="<?= e(url('/dsc/movement/create')) ?>" class="button button-secondary">Record Movement</a><?php endif; ?>
            <?php if (\App\Core\Auth::can('dsc.usage.log')): ?><a href="<?= e(url('/dsc/usage/create')) ?>" class="button button-secondary">Log Usage</a><?php endif; ?>
            <a href="<?= e(url('/dsc')) ?>" class="button button-secondary">Back</a>
        </div>
    </div>

    <div class="grid">
        <div class="metric"><strong>Holder</strong><div style="margin-top:8px;"><?= e($dsc['holder_name']) ?></div><div style="font-size:0.85rem;color:#64748b;"><?= e($dsc['holder_pan'] ?: '-') ?> | <?= e($dsc['holder_email'] ?: '-') ?></div></div>
        <div class="metric"><strong>Token Serial</strong><div style="margin-top:8px;"><?= e($dsc['token_serial_no'] ?: '-') ?></div></div>
        <div class="metric"><strong>Provider</strong><div style="margin-top:8px;"><?= e($dsc['provider_name'] ?: '-') ?></div></div>
        <div class="metric"><strong>Validity</strong><div style="margin-top:8px;"><?= e($dsc['valid_from'] ?: '-') ?> to <?= e($dsc['valid_to'] ?: '-') ?></div><div style="font-size:0.85rem;color:<?= $isExpired ? '#b42318' : ($isExpiringSoon ? '#ea580c' : '#047857') ?>;"><?= $isExpired ? 'EXPIRED' : ($isExpiringSoon ? 'Expiring Soon' : 'Valid') ?></div></div>
        <div class="metric"><strong>Custody</strong><div style="margin-top:8px;"><?= e($dsc['custody_status']) ?></div></div>
        <div class="metric"><strong>Assigned Staff</strong><div style="margin-top:8px;"><?= e($dsc['assigned_user_name'] ?: '-') ?></div></div>
        <div class="metric"><strong>Storage Location</strong><div style="margin-top:8px;"><?= e($dsc['storage_location'] ?: '-') ?></div></div>
        <div class="metric"><strong>Password Status</strong><div style="margin-top:8px;"><?= e($dsc['password_status']) ?></div></div>
    </div>

    <?php if (!empty($dsc['remarks'])): ?>
        <div class="panel" style="box-shadow:none;margin-top:18px;background:linear-gradient(180deg,#fff,#f6faf7);"><h4 style="margin-top:0;">Remarks</h4><p><?= e($dsc['remarks']) ?></p></div>
    <?php endif; ?>

    <div class="panel" style="box-shadow:none;margin-top:18px;background:linear-gradient(180deg,#fff,#f6faf7);">
        <h4 style="margin-top:0;">Movement History</h4>
        <?php if ($movements === []): ?><p class="subtle">No movements recorded.</p><?php else: ?>
            <div style="overflow:auto;"><table><thead><tr><th>Type</th><th>From</th><th>To</th><th>Date</th><th>Status</th></tr></thead><tbody>
            <?php foreach ($movements as $mov): ?><tr><td><?= e(label_case((string) $mov['movement_type'])) ?></td><td><?= e($mov['from_user_name'] ?: $mov['from_location'] ?: '-') ?></td><td><?= e($mov['to_user_name'] ?: $mov['to_location'] ?: '-') ?></td><td><?= e($mov['movement_date']) ?></td><td><span class="chip"><?= e(label_case((string) $mov['status'])) ?></span></td></tr><?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </div>

    <div class="panel" style="box-shadow:none;margin-top:18px;background:linear-gradient(180deg,#fff,#f6faf7);">
        <h4 style="margin-top:0;">Usage History</h4>
        <?php if ($usageLogs === []): ?><p class="subtle">No usage logged.</p><?php else: ?>
            <div style="overflow:auto;"><table><thead><tr><th>Date</th><th>Purpose</th><th>Client</th><th>SO</th><th>Reference</th></tr></thead><tbody>
            <?php foreach ($usageLogs as $log): ?><tr><td><?= e($log['usage_date']) ?></td><td><?= e($log['purpose']) ?></td><td><?= e($log['client_name'] ?: '-') ?></td><td><?= e($log['so_no'] ?: '-') ?></td><td><?= e($log['filing_reference'] ?: $log['acknowledgement_no'] ?: '-') ?></td></tr><?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </div>
</section>

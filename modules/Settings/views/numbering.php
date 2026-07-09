<?php $settings = $settings ?? []; ?>
<section class="panel">
    <div class="toolbar"><div><div class="eyebrow">Settings Module</div><h3 style="margin:0 0 6px;">Numbering Settings</h3><div class="subtle">Numbering prefixes for entities.</div></div><a href="<?= e(url('/settings')) ?>" class="button button-secondary">Back</a></div>
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= e(url('/settings/numbering')) ?>" style="display:grid;gap:18px;">
        <?= \App\Core\Csrf::inputField() ?>
        <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
            <label style="display:grid;gap:8px;"><span>Client Prefix</span><input type="text" name="client_prefix" value="<?= e($settings['client_prefix'] ?? 'CL') ?>"></label>
            <label style="display:grid;gap:8px;"><span>Service Order Prefix</span><input type="text" name="so_prefix" value="<?= e($settings['so_prefix'] ?? 'SO') ?>"></label>
            <label style="display:grid;gap:8px;"><span>Document Prefix</span><input type="text" name="document_prefix" value="<?= e($settings['document_prefix'] ?? 'DOC') ?>"></label>
            <label style="display:grid;gap:8px;"><span>DSC Prefix</span><input type="text" name="dsc_prefix" value="<?= e($settings['dsc_prefix'] ?? 'DSC') ?>"></label>
        </div>
        <div style="display:flex;gap:12px;"><button type="submit" class="button">Update Numbering</button><a href="<?= e(url('/settings')) ?>" class="button button-secondary">Cancel</a></div>
    </form>
</section>

<section class="panel">
    <div class="toolbar"><div><div class="eyebrow">Reports Module</div><h3 style="margin:0 0 6px;">DSC Reports</h3><div class="subtle">DSC custody status and register summary.</div></div>
        <a href="<?= e(url('/reports')) ?>" class="button button-secondary">Back</a>
    </div>

    <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));margin-bottom:20px;">
        <?php foreach (($summary ?? []) as $row): ?>
            <div class="metric" style="min-height:80px;"><div class="eyebrow"><?= e($row['custody_status'] ?: 'Unknown') ?></div><div style="font-size:1.6rem;font-weight:800;"><?= e((string) $row['count']) ?></div></div>
        <?php endforeach; ?>
    </div>

    <?php if (($summary ?? []) === []): ?><div class="data-card" style="text-align:center;padding:40px;"><div class="eyebrow">No Data</div><p class="subtle" style="margin:8px 0 0;">No DSC data found.</p></div><?php endif; ?>
</section>

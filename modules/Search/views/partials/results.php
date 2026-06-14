<?php
/** @var array $items */
$items = $items ?? [];
$heading = $heading ?? 'Results';
$description = $description ?? '';
$emptyMessage = $emptyMessage ?? 'No results matched this search.';
$showSource = $showSource ?? true;
?>
<section style="display:grid;gap:16px;">
    <div style="display:flex;justify-content:space-between;gap:12px;align-items:end;flex-wrap:wrap;">
        <div>
            <div class="eyebrow"><?= e($heading) ?></div>
            <?php if ($description !== ''): ?>
                <p class="subtle" style="margin:6px 0 0;"><?= e($description) ?></p>
            <?php endif; ?>
        </div>
        <div class="chip"><?= e((string) count($items)) ?> item(s)</div>
    </div>

    <?php if ($items === []): ?>
        <article class="data-card">
            <span class="subtle"><?= e($emptyMessage) ?></span>
        </article>
    <?php else: ?>
        <div class="result-grid">
            <?php foreach ($items as $item): ?>
                <article class="result-card">
                    <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                        <div style="display:grid;gap:10px;min-width:0;">
                            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                                <?php if ($showSource && !empty($item['type'])): ?>
                                    <span class="result-type"><?= e((string) $item['type']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($item['badge'])): ?>
                                    <span class="result-badge"><?= e((string) $item['badge']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h4 style="margin:0 0 6px;font-size:1.08rem;"><?= e((string) ($item['title'] ?? 'Result')) ?></h4>
                                <?php if (!empty($item['subtitle'])): ?>
                                    <p style="margin:0;color:var(--text);font-weight:600;"><?= e((string) $item['subtitle']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <a href="<?= e((string) ($item['url'] ?? '#')) ?>" class="chip chip-strong"><?= e((string) ($item['action_label'] ?? 'Open')) ?></a>
                    </div>

                    <?php if (!empty($item['meta']) && is_array($item['meta'])): ?>
                        <div class="result-meta">
                            <?php foreach ($item['meta'] as $meta): ?>
                                <span class="chip"><?= e((string) $meta) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="panel">
    <div class="toolbar">
        <div>
            <div class="eyebrow">Quick Search</div>
            <h3 style="margin:0 0 6px;">Header search results</h3>
            <p class="subtle" style="margin:0;">Fast lookup for the most relevant records across your accessible modules.</p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="<?= e(url('/search')) ?>" class="button button-secondary">Global Search</a>
            <a href="<?= e(url('/search/advanced')) ?>" class="button button-secondary">Advanced Search</a>
        </div>
    </div>

    <form method="get" action="<?= e(url('/search/quick')) ?>" class="search-bar">
        <input type="text" name="q" value="<?= e($query) ?>" placeholder="Quick search by client, PAN, SO, invoice, receipt, consultant, or document">
        <button type="submit" class="button">Go</button>
    </form>

    <?php if ($query === ''): ?>
        <div class="data-card"><span class="subtle">Use the quick search bar in the header or search here directly.</span></div>
    <?php else: ?>
        <div class="grid" style="margin-bottom:18px;">
            <div class="metric">
                <div class="eyebrow">Quick Results</div>
                <strong>Total Matches</strong>
                <div style="margin-top:8px;font-size:1.85rem;"><?= e((string) ($results['total'] ?? 0)) ?></div>
            </div>
        </div>

        <div class="card-grid">
            <?php foreach (($results['sources'] ?? []) as $sourceKey => $sourceResults): ?>
                <?= \App\Core\View::render(
                    base_path('modules/Search/views/partials/results.php'),
                    [
                        'sourceKey' => $sourceKey,
                        'sourceLabel' => $sourceResults['label'],
                        'sourceResults' => $sourceResults,
                        'query' => $query,
                    ],
                    null
                ) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

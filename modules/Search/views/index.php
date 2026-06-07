<section style="display:grid;gap:18px;">
    <div class="hero-card">
        <div class="eyebrow">Enterprise Search</div>
        <h3 style="margin:10px 0 8px;font-size:2rem;">Search across operational, billing, portal, consultant, and document records</h3>
        <p class="subtle" style="margin:0;">Results are automatically trimmed to the records your role is allowed to see.</p>
    </div>

    <section class="panel">
        <div class="toolbar">
            <div>
                <div class="eyebrow">Global Search</div>
                <h3 style="margin:0 0 6px;">Cross-module lookup</h3>
                <p class="subtle" style="margin:0;">Search clients, service orders, credentials, finance, consultants, and documents in one pass.</p>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="<?= e(url('/search/advanced')) ?>" class="button button-secondary">Advanced Search</a>
                <a href="<?= e(url('/search/history')) ?>" class="button button-secondary">Search History</a>
            </div>
        </div>

        <form method="get" action="<?= e(url('/search')) ?>" class="search-bar">
            <input type="text" name="q" value="<?= e($query) ?>" placeholder="Search by PAN, TAN, client name, mobile, SO no, invoice, receipt, consultant, or document">
            <button type="submit" class="button">Search</button>
        </form>

        <?php if ($query === ''): ?>
            <div class="data-card">
                <span class="subtle">Enter a search term to run a global lookup. Use Advanced Search for source-specific filtering.</span>
            </div>
        <?php else: ?>
            <div class="grid" style="margin-bottom:18px;">
                <div class="metric">
                    <div class="eyebrow">Results</div>
                    <strong>Total Matches</strong>
                    <div style="margin-top:8px;font-size:1.85rem;"><?= e((string) ($results['total'] ?? 0)) ?></div>
                </div>
                <div class="metric">
                    <div class="eyebrow">Sources</div>
                    <strong>Visible Modules</strong>
                    <div style="margin-top:8px;font-size:1.85rem;"><?= e((string) count($results['sources'] ?? [])) ?></div>
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
</section>

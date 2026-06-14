<section class="panel">
    <div class="toolbar">
        <div>
            <div class="eyebrow">Quick Search</div>
            <h3 style="margin:0 0 6px;">Direct workspace access</h3>
            <p class="subtle" style="margin:0;">Use this as a fast route into clients, service orders, billing, documents, and portal access records.</p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="<?= e(url('/search')) ?>" class="button button-secondary">Global Search</a>
            <a href="<?= e(url('/search/advanced')) ?>" class="button button-secondary">Advanced Search</a>
        </div>
    </div>

    <form method="get" action="<?= e(url('/search/quick')) ?>" class="search-bar">
        <input type="text" name="q" value="<?= e($query) ?>" placeholder="Search clients, service orders, invoices, PAN, GSTIN, mobile, documents...">
        <button type="submit" class="button">Search</button>
    </form>

    <?php if ($query === ''): ?>
        <div class="card-grid">
            <article class="data-card">
                <div class="eyebrow">Recent Searches</div>
                <strong>Resume recent lookups</strong>
                <?php if (($recentSearches ?? []) === []): ?>
                    <span class="subtle">Recent searches will appear here.</span>
                <?php else: ?>
                    <div style="display:grid;gap:10px;">
                        <?php foreach (($recentSearches ?? []) as $entry): ?>
                            <a href="<?= e(url('/search/quick?q=' . urlencode((string) ($entry['query_text'] ?? '')))) ?>" class="chip" style="justify-content:space-between;">
                                <span><?= e((string) ($entry['query_text'] ?? '')) ?></span>
                                <span class="subtle"><?= e((string) ($entry['searched_at'] ?? '')) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>

            <article class="data-card">
                <div class="eyebrow">Quick Access</div>
                <strong>Open key workspaces</strong>
                <div style="display:grid;gap:10px;">
                    <?php foreach (($quickAccess ?? []) as $item): ?>
                        <a href="<?= e((string) ($item['url'] ?? '#')) ?>" class="chip" style="justify-content:space-between;">
                            <span><?= e((string) ($item['label'] ?? 'Workspace')) ?></span>
                            <span class="subtle"><?= e((string) ($item['description'] ?? '')) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </article>
        </div>
    <?php else: ?>
        <?php
        echo \App\Core\View::render(
            base_path('modules/Search/views/partials/results.php'),
            [
                'heading' => 'Quick Results',
                'description' => 'The fastest route into the record you need right now.',
                'items' => $results['items'] ?? [],
                'emptyMessage' => 'No matching records were found for this quick search.',
                'showSource' => true,
            ],
            null
        );
        ?>
    <?php endif; ?>
</section>

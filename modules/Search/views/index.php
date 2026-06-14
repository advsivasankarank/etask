<section style="display:grid;gap:18px;">
    <div class="hero-card">
        <div class="eyebrow">Go To Anything</div>
        <h3 style="margin:10px 0 10px;font-size:2rem;">Find any client, service order, invoice, receipt, document, or portal account in seconds</h3>
        <p class="subtle" style="margin:0;max-width:760px;">Use one permission-aware search to jump directly into the right workspace instead of opening multiple modules.</p>
    </div>

    <section class="panel">
        <div class="toolbar">
            <div>
                <div class="eyebrow">Universal Search</div>
                <h3 style="margin:0 0 6px;">Action-first navigation</h3>
                <p class="subtle" style="margin:0;">Search by client name, PAN, GSTIN, mobile, service order, invoice, receipt, portal user, or document title.</p>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="<?= e(url('/search/advanced')) ?>" class="button button-secondary">Advanced Search</a>
                <a href="<?= e(url('/search/history')) ?>" class="button button-secondary">Search History</a>
            </div>
        </div>

        <form method="get" action="<?= e(url('/search')) ?>" class="search-bar">
            <input type="text" name="q" value="<?= e($query) ?>" placeholder="Search clients, service orders, invoices, PAN, GSTIN, mobile, documents...">
            <button type="submit" class="button">Search</button>
        </form>

        <?php if ($query === ''): ?>
            <div class="card-grid" style="margin-bottom:18px;">
                <article class="data-card">
                    <div class="eyebrow">Recent Searches</div>
                    <strong>Resume recent lookups</strong>
                    <?php if (($recentSearches ?? []) === []): ?>
                        <span class="subtle">Your recent searches will appear here.</span>
                    <?php else: ?>
                        <div style="display:grid;gap:10px;">
                            <?php foreach (($recentSearches ?? []) as $entry): ?>
                                <a href="<?= e(url('/search?q=' . urlencode((string) ($entry['query_text'] ?? '')))) ?>" class="chip" style="justify-content:space-between;">
                                    <span><?= e((string) ($entry['query_text'] ?? '')) ?></span>
                                    <span class="subtle"><?= e((string) ($entry['searched_at'] ?? '')) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>

                <article class="data-card">
                    <div class="eyebrow">Recent Records</div>
                    <strong>Jump back into active work</strong>
                    <?php
                    echo \App\Core\View::render(
                        base_path('modules/Search/views/partials/results.php'),
                        [
                            'heading' => 'Recent Records',
                            'description' => '',
                            'items' => $recentRecords ?? [],
                            'emptyMessage' => 'Recent records will appear after you use search.',
                            'showSource' => true,
                        ],
                        null
                    );
                    ?>
                </article>
            </div>

            <article class="data-card">
                <div class="eyebrow">Quick Access</div>
                <strong>Open key workspaces directly</strong>
                <div class="result-grid">
                    <?php foreach (($quickAccess ?? []) as $item): ?>
                        <article class="result-card">
                            <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                                <div style="display:grid;gap:8px;">
                                    <span class="result-type"><?= e((string) ($item['icon'] ?? 'OPEN')) ?></span>
                                    <h4 style="margin:0;font-size:1.04rem;"><?= e((string) ($item['label'] ?? 'Workspace')) ?></h4>
                                    <p class="subtle" style="margin:0;"><?= e((string) ($item['description'] ?? '')) ?></p>
                                </div>
                                <a href="<?= e((string) ($item['url'] ?? '#')) ?>" class="chip chip-strong">Open</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php else: ?>
            <div class="grid" style="margin-bottom:18px;">
                <div class="metric">
                    <div class="eyebrow">Matches</div>
                    <strong>Total Results</strong>
                    <div style="margin-top:8px;font-size:1.85rem;"><?= e((string) ($results['total'] ?? 0)) ?></div>
                </div>
                <div class="metric">
                    <div class="eyebrow">Coverage</div>
                    <strong>Modules Returned</strong>
                    <div style="margin-top:8px;font-size:1.85rem;"><?= e((string) count($results['groups'] ?? [])) ?></div>
                </div>
            </div>

            <?php
            echo \App\Core\View::render(
                base_path('modules/Search/views/partials/results.php'),
                [
                    'heading' => 'Search Results',
                    'description' => 'The most relevant records are ranked first and open directly into their workspace.',
                    'items' => $results['items'] ?? [],
                    'emptyMessage' => 'No matching records were found in the modules you can access.',
                    'showSource' => true,
                ],
                null
            );
            ?>
        <?php endif; ?>
    </section>
</section>

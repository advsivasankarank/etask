<section class="panel">
    <div class="toolbar">
        <div>
            <div class="eyebrow">Search History</div>
            <h3 style="margin:0 0 6px;">Audit trail for search activity</h3>
            <p class="subtle" style="margin:0;"><?= $canAudit ? 'View and filter search activity across users.' : 'View your recent search activity.' ?></p>
        </div>
        <a href="<?= e(url('/search')) ?>" class="button button-secondary">Back to Search</a>
    </div>

    <form method="get" action="<?= e(url('/search/history')) ?>" class="panel" style="box-shadow:none;margin-bottom:18px;padding:18px;">
        <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));">
            <input type="text" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Query / Source / User">
            <select name="mode">
                <option value="">All Modes</option>
                <?php foreach (['QUICK', 'GLOBAL', 'ADVANCED'] as $mode): ?>
                    <option value="<?= e($mode) ?>" <?= ($filters['mode'] ?? '') === $mode ? 'selected' : '' ?>><?= e($mode) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="source">
                <option value="">All Sources</option>
                <?php foreach (($options['sources'] ?? []) as $code => $label): ?>
                    <option value="<?= e($code) ?>" <?= ($filters['source'] ?? '') === $code ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>">
            <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>">
            <button type="submit" class="button">Apply Filters</button>
        </div>
    </form>

    <?php if (($report['items'] ?? []) === []): ?>
        <div class="data-card"><span class="subtle">No search history entries matched the selected filters.</span></div>
    <?php else: ?>
        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Mode</th>
                        <th>Query</th>
                        <th>Source</th>
                        <th>Results</th>
                        <th>IP Address</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($report['items'] ?? []) as $row): ?>
                        <tr>
                            <td><?= e($row['user_name'] ?: '-') ?></td>
                            <td><span class="chip chip-strong"><?= e($row['search_mode']) ?></span></td>
                            <td><?= e($row['query_text'] !== '' ? $row['query_text'] : '[no-query]') ?></td>
                            <td><?= e($row['source_scope']) ?></td>
                            <td><?= e((string) $row['result_count']) ?></td>
                            <td><?= e($row['ip_address'] ?: '-') ?></td>
                            <td><?= e($row['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?= \App\Core\View::render(base_path('app/Views/partials/pagination.php'), [
            'pagination' => $report,
            'path' => '/search/history',
            'query' => $filters,
        ], null) ?>
    <?php endif; ?>
</section>

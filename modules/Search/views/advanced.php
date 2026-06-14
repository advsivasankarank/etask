<?php $source = (string) ($filters['source'] ?? 'clients'); ?>
<section class="panel">
    <div class="toolbar">
        <div>
            <div class="eyebrow">Advanced Search</div>
            <h3 style="margin:0 0 6px;">Source-specific filters</h3>
            <p class="subtle" style="margin:0;">Filter one source at a time using operational and compliance identifiers.</p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="<?= e(url('/search')) ?>" class="button button-secondary">Global Search</a>
            <a href="<?= e(url('/search/history')) ?>" class="button button-secondary">Search History</a>
        </div>
    </div>

    <form method="get" action="<?= e(url('/search/advanced')) ?>" class="panel" style="box-shadow:none;margin-bottom:18px;padding:18px;">
        <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));">
            <select name="source">
                <?php foreach (($options['sources'] ?? []) as $code => $label): ?>
                    <option value="<?= e($code) ?>" <?= $source === $code ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Keyword">
            <input type="text" name="pan" value="<?= e($filters['pan'] ?? '') ?>" placeholder="PAN">
            <input type="text" name="tan" value="<?= e($filters['tan'] ?? '') ?>" placeholder="TAN">
            <input type="text" name="gstin" value="<?= e($filters['gstin'] ?? '') ?>" placeholder="GSTIN">
            <input type="text" name="mobile" value="<?= e($filters['mobile'] ?? '') ?>" placeholder="Mobile">
            <select name="portal_code">
                <option value="">All Portals</option>
                <?php foreach (($options['portal_definitions'] ?? []) as $code => $definition): ?>
                    <option value="<?= e($code) ?>" <?= ($filters['portal_code'] ?? '') === $code ? 'selected' : '' ?>><?= e($definition['label']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="company_id">
                <option value="0">All Companies</option>
                <?php foreach (($options['companies'] ?? []) as $company): ?>
                    <option value="<?= e((string) $company['id']) ?>" <?= (int) ($filters['company_id'] ?? 0) === (int) $company['id'] ? 'selected' : '' ?>><?= e($company['label']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="service_type_id">
                <option value="0">All Service Types</option>
                <?php foreach (($options['service_types'] ?? []) as $serviceType): ?>
                    <option value="<?= e((string) $serviceType['id']) ?>" <?= (int) ($filters['service_type_id'] ?? 0) === (int) $serviceType['id'] ? 'selected' : '' ?>><?= e($serviceType['label']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="document_category">
                <option value="">All Document Categories</option>
                <?php foreach (($options['document_categories'] ?? []) as $code => $label): ?>
                    <option value="<?= e($code) ?>" <?= ($filters['document_category'] ?? '') === $code ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>">
            <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>">
            <button type="submit" class="button">Run Search</button>
        </div>
    </form>

    <?php if (!$hasCriteria): ?>
        <div class="data-card"><span class="subtle">Apply at least one filter to run an advanced search.</span></div>
    <?php elseif (($report['items'] ?? []) === []): ?>
        <div class="data-card"><span class="subtle">No records matched the selected filters.</span></div>
    <?php else: ?>
        <?= \App\Core\View::render(
            base_path('modules/Search/views/partials/results.php'),
            [
                'heading' => ($options['sources'][$source] ?? ucfirst(str_replace('_', ' ', $source))),
                'description' => 'Filtered search results for the selected module.',
                'items' => $cards ?? [],
                'emptyMessage' => 'No matching records were found for this source.',
                'showSource' => false,
            ],
            null
        ) ?>

        <?= \App\Core\View::render(base_path('app/Views/partials/pagination.php'), [
            'pagination' => $report,
            'path' => '/search/advanced',
            'query' => $filters,
        ], null) ?>
    <?php endif; ?>
</section>

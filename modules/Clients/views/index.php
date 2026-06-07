<section class="panel">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <div>
            <div class="eyebrow">Register</div>
            <h3 style="margin:0 0 6px;">Client Master</h3>
            <div class="subtle">Search by PAN, TAN, name, GSTIN, or mobile. Archive only, no deletion.</div>
        </div>
        <a href="<?= e(url('/clients/create')) ?>" class="button">Create Client</a>
    </div>

    <form method="get" action="<?= e(url('/clients')) ?>" class="search-bar">
        <input type="text" name="search" value="<?= e($search ?? '') ?>" placeholder="PAN / TAN / Name / GSTIN / Mobile" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
        <button type="submit" class="button">Search</button>
    </form>

    <?php if ($clients === []): ?>
        <div class="data-card"><span class="subtle">No clients found.</span></div>
    <?php else: ?>
        <div class="card-grid">
            <?php foreach ($clients as $client): ?>
                <article class="data-card">
                    <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                        <div>
                            <div class="eyebrow"><?= e($client['pan'] ?: 'Client') ?></div>
                            <h4 style="margin:4px 0 0;"><?= e($client['legal_name']) ?></h4>
                        </div>
                        <span class="chip <?= (int) $client['is_active'] === 1 ? '' : 'chip-strong' ?>"><?= (int) $client['is_active'] === 1 ? 'Active' : 'Archived' ?></span>
                    </div>
                    <div class="stat-line"><span>GST / TAN</span><strong><?= e(($client['gstin'] ?: '-') . ' / ' . ($client['tan'] ?: '-')) ?></strong></div>
                    <div class="stat-line"><span>Mobile</span><strong><?= e($client['mobile'] ?: '-') ?></strong></div>
                    <div class="stat-line"><span>CRM</span><strong><?= e($client['assigned_crm_name'] ?: '-') ?></strong></div>
                    <div style="display:flex;justify-content:flex-end;margin-top:6px;">
                        <a href="<?= e(url('/clients/show?id=' . $client['id'])) ?>" class="button button-secondary">View Client</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?= \App\Core\View::render(base_path('app/Views/partials/pagination.php'), [
            'pagination' => $pagination ?? null,
            'path' => '/clients',
            'query' => ['search' => $search ?? ''],
        ], null) ?>
    <?php endif; ?>
</section>

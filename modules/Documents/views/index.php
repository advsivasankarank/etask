<section class="panel">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <div>
            <div class="eyebrow">Document Module</div>
            <h3 style="margin:0 0 6px;">Document Register</h3>
            <div class="subtle">Manage and track all documents across clients and service orders.</div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <?php if (\App\Core\Auth::can('documents.request')): ?>
                <a href="<?= e(url('/documents/requests/create')) ?>" class="button">+ Request Document</a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('documents.movement.manage')): ?>
                <a href="<?= e(url('/documents/movement/create')) ?>" class="button button-secondary">+ Record Movement</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(140px, 1fr));margin-bottom:20px;">
        <div class="metric" style="min-height:80px;">
            <div class="eyebrow">Total</div>
            <div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['total'] ?? 0)) ?></div>
        </div>
        <div class="metric" style="min-height:80px;">
            <div class="eyebrow">Pending Verify</div>
            <div style="font-size:1.6rem;font-weight:800;color:#ea580c;"><?= e((string) ($summary['pending_verification'] ?? 0)) ?></div>
        </div>
        <div class="metric" style="min-height:80px;">
            <div class="eyebrow">Verified</div>
            <div style="font-size:1.6rem;font-weight:800;color:#047857;"><?= e((string) ($summary['verified'] ?? 0)) ?></div>
        </div>
        <div class="metric" style="min-height:80px;">
            <div class="eyebrow">Requested</div>
            <div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['requested'] ?? 0)) ?></div>
        </div>
        <div class="metric" style="min-height:80px;">
            <div class="eyebrow">In Movement</div>
            <div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['in_movement'] ?? 0)) ?></div>
        </div>
        <div class="metric" style="min-height:80px;">
            <div class="eyebrow">Archived</div>
            <div style="font-size:1.6rem;font-weight:800;"><?= e((string) ($summary['archived'] ?? 0)) ?></div>
        </div>
    </div>

    <form method="get" action="<?= e(url('/documents')) ?>" class="search-bar">
        <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Search by document name, category, client, or SO number...">
        <select name="verification_status" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
            <option value="">All Status</option>
            <option value="PENDING" <?= ($filters['verification_status'] ?? '') === 'PENDING' ? 'selected' : '' ?>>Pending</option>
            <option value="VERIFIED" <?= ($filters['verification_status'] ?? '') === 'VERIFIED' ? 'selected' : '' ?>>Verified</option>
            <option value="REJECTED" <?= ($filters['verification_status'] ?? '') === 'REJECTED' ? 'selected' : '' ?>>Rejected</option>
        </select>
        <button type="submit" class="button">Search</button>
    </form>

    <?php if (($documents['items'] ?? []) === []): ?>
        <div class="data-card" style="text-align:center;padding:40px;">
            <div class="eyebrow">No Results</div>
            <p class="subtle" style="margin:8px 0 0;">No documents found matching your criteria.</p>
        </div>
    <?php else: ?>
        <div class="card-grid">
            <?php foreach ($documents['items'] as $doc): ?>
                <article class="data-card">
                    <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                        <div>
                            <div class="eyebrow"><?= e($doc['document_category'] ?: 'Document') ?></div>
                            <h4 style="margin:4px 0 0;"><?= e($doc['document_name']) ?></h4>
                        </div>
                        <span class="chip <?= ($doc['verification_status'] ?? 'PENDING') === 'VERIFIED' ? '' : (($doc['verification_status'] ?? 'PENDING') === 'REJECTED' ? 'chip-strong' : '') ?>"><?= e($doc['verification_status'] ?? 'PENDING') ?></span>
                    </div>
                    <div class="stat-line"><span>Client</span><strong><?= e($doc['client_name'] ?: '-') ?></strong></div>
                    <div class="stat-line"><span>SO</span><strong><?= e($doc['so_no'] ?: '-') ?></strong></div>
                    <div class="stat-line"><span>Version</span><strong>V<?= e((string) ($doc['current_version_no'] ?? 1)) ?></strong></div>
                    <div class="stat-line"><span>Uploaded</span><strong><?= e($doc['uploaded_at'] ?: '-') ?></strong></div>
                    <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:10px;flex-wrap:wrap;">
                        <a href="<?= e(url('/documents/show?id=' . $doc['id'])) ?>" class="button button-secondary">View</a>
                        <a href="<?= e(url('/documents/' . $doc['id'] . '/download')) ?>" class="button button-secondary">Download</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?= \App\Core\View::render(base_path('app/Views/partials/pagination.php'), [
            'pagination' => $documents ?? null,
            'path' => '/documents',
            'query' => ['search' => $filters['search'] ?? '', 'verification_status' => $filters['verification_status'] ?? ''],
        ], null) ?>
    <?php endif; ?>
</section>

<section class="panel">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <div>
            <div class="eyebrow">PSO Detail</div>
            <h3 style="margin:0 0 6px;"><?= e($pso['pso_no']) ?></h3>
            <div class="subtle"><?= e($pso['title']) ?></div>
        </div>
        <a href="<?= e(url('/client-portal/pso')) ?>" class="button button-secondary">Back to PSOs</a>
    </div>

    <div class="grid">
        <div class="metric">
            <strong>Client</strong>
            <div style="margin-top:8px;"><?= e($pso['client_name']) ?></div>
            <div style="margin-top:4px;color:#62748a;">PAN: <?= e($pso['pan'] ?: '-') ?></div>
        </div>
        <div class="metric">
            <strong>Service / Company</strong>
            <div style="margin-top:8px;"><?= e($pso['service_type_name']) ?></div>
            <div style="margin-top:4px;color:#62748a;"><?= e($pso['company_name']) ?></div>
        </div>
        <div class="metric">
            <strong>Status</strong>
            <div style="margin-top:8px;"><?= e($pso['current_status']) ?></div>
            <div style="margin-top:4px;color:#62748b;">Submitted: <?= e($pso['submitted_at']) ?></div>
        </div>
        <div class="metric">
            <strong>Converted SO</strong>
            <div style="margin-top:8px;"><?= e($pso['converted_so_no'] ?: 'Not converted yet') ?></div>
        </div>
    </div>

    <div class="grid" style="margin-top:18px;">
        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6fafb);">
            <h4 style="margin-top:0;">Documents</h4>
            <?php if ($documents === []): ?>
                <p style="color:#64748b;">No supporting documents uploaded.</p>
            <?php else: ?>
                <div style="display:grid;gap:10px;">
                    <?php foreach ($documents as $document): ?>
                        <div style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;background:#f8fafc;">
                            <div><strong><?= e($document['document_name']) ?></strong></div>
                            <div style="margin-top:8px;">
                                <a href="<?= e(url('/documents/' . $document['id'] . '/download')) ?>" class="button button-secondary">Download Document</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6fafb);">
            <h4 style="margin-top:0;">Review Trail</h4>
            <?php if ($reviews === []): ?>
                <p style="color:#64748b;">No review activity yet.</p>
            <?php else: ?>
                <div style="display:grid;gap:10px;">
                    <?php foreach ($reviews as $review): ?>
                        <div style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;background:#fff;">
                            <div><strong><?= e($review['review_action']) ?></strong> by <?= e($review['acted_by_name']) ?></div>
                            <div style="margin-top:6px;color:#64748b;"><?= e($review['acted_at']) ?></div>
                            <div style="margin-top:6px;color:#64748b;"><?= e($review['remarks'] ?: '-') ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (\App\Core\Auth::canAny('portal.pso.review', 'portal.pso.approve')): ?>
        <div class="grid" style="margin-top:18px;">
            <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6fafb);">
                <h4 style="margin-top:0;">CRM Review</h4>
                <form method="post" action="<?= e(url('/client-portal/pso/recommend')) ?>" style="display:grid;gap:10px;">
                    <?= \App\Core\Csrf::inputField() ?>
                    <input type="hidden" name="pso_id" value="<?= e($pso['id']) ?>">
                    <textarea name="remarks" rows="4" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;resize:vertical;" placeholder="CRM review remarks"></textarea>
                    <button type="submit" class="button">Mark Under Review / Recommend Approval</button>
                </form>
            </div>

            <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6fafb);">
                <h4 style="margin-top:0;">Approval</h4>
                <form method="post" action="<?= e(url('/client-portal/pso/approve')) ?>" style="display:grid;gap:10px;">
                    <?= \App\Core\Csrf::inputField() ?>
                    <input type="hidden" name="pso_id" value="<?= e($pso['id']) ?>">
                    <textarea name="remarks" rows="4" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;resize:vertical;" placeholder="Approval remarks"></textarea>
                    <button type="submit" class="button">Approve and Create SO</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php if (\App\Core\Auth::can('portal.pso.reject')): ?>
        <div class="panel" style="box-shadow:none;margin-top:18px;background:linear-gradient(180deg,#fff,#f6fafb);">
            <h4 style="margin-top:0;">Admin Rejection</h4>
            <form method="post" action="<?= e(url('/client-portal/pso/reject')) ?>" style="display:grid;gap:10px;">
                <?= \App\Core\Csrf::inputField() ?>
                <input type="hidden" name="pso_id" value="<?= e($pso['id']) ?>">
                <textarea name="reason" rows="4" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;resize:vertical;" placeholder="Rejection reason" required></textarea>
                <button type="submit" class="button" style="background:#b42318;">Reject PSO</button>
            </form>
        </div>
    <?php endif; ?>
</section>

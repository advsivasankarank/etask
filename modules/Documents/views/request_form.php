<?php $old = is_array($old ?? null) ? $old : []; ?>
<section class="panel">
    <div class="toolbar">
        <div>
            <div class="eyebrow">Document Module</div>
            <h3 style="margin:0 0 6px;">Create Document Request</h3>
            <div class="subtle">Request a document from a client for a service order.</div>
        </div>
        <a href="<?= e(url('/documents/requests')) ?>" class="button button-secondary">Back to Requests</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= e(url('/documents/requests')) ?>" style="display:grid;gap:18px;">
        <?= \App\Core\Csrf::inputField() ?>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <div class="eyebrow">Request Details</div>
            <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
                <label style="display:grid;gap:8px;">
                    <span>Client *</span>
                    <select name="client_id" required>
                        <option value="">Select client</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?= e((string) $client['id']) ?>" <?= (string) ($old['client_id'] ?? '') === (string) $client['id'] ? 'selected' : '' ?>><?= e($client['legal_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label style="display:grid;gap:8px;">
                    <span>Document Title *</span>
                    <input type="text" name="document_title" value="<?= e($old['document_title'] ?? '') ?>" placeholder="e.g., PAN Card Copy" required>
                </label>
                <label style="display:grid;gap:8px;">
                    <span>Category</span>
                    <input type="text" name="document_category" value="<?= e($old['document_category'] ?? '') ?>" placeholder="e.g., IDENTITY">
                </label>
                <label style="display:grid;gap:8px;">
                    <span>Due Date</span>
                    <input type="date" name="due_date" value="<?= e($old['due_date'] ?? '') ?>">
                </label>
            </div>
        </div>

        <label style="display:grid;gap:8px;">
            <span>Description</span>
            <textarea name="description" rows="3" placeholder="Additional details about the requested document"><?= e($old['description'] ?? '') ?></textarea>
        </label>

        <label style="display:grid;gap:8px;">
            <span>Remarks</span>
            <textarea name="remarks" rows="2" placeholder="Internal notes"><?= e($old['remarks'] ?? '') ?></textarea>
        </label>

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="button">Create Request</button>
            <a href="<?= e(url('/documents/requests')) ?>" class="button button-secondary">Cancel</a>
        </div>
    </form>
</section>

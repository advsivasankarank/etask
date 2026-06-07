<?php $old = is_array($old ?? null) ? $old : []; ?>
<section class="panel">
    <div class="toolbar">
        <div>
            <div class="eyebrow">Client Request</div>
            <h3 style="margin:0 0 6px;">Create Pre-Service Order</h3>
            <p class="subtle" style="margin:0;">Upload supporting documents and submit the request for CRM review. Rejection is restricted to Admin.</p>
        </div>
        <a href="<?= e(url('/client-portal/pso')) ?>" class="button button-secondary">Back</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= e(url('/client-portal/pso')) ?>" enctype="multipart/form-data" style="display:grid;gap:18px;">
        <?= \App\Core\Csrf::inputField() ?>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6fafb);display:grid;gap:18px;">
        <label style="display:grid;gap:8px;">
            <span>Service Type</span>
            <select name="service_type_id" required>
                <option value="">Select service</option>
                <?php foreach ($serviceTypes as $serviceType): ?>
                    <option value="<?= e($serviceType['id']) ?>" <?= (string) ($old['service_type_id'] ?? '') === (string) $serviceType['id'] ? 'selected' : '' ?>>
                        <?= e($serviceType['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label style="display:grid;gap:8px;">
            <span>Title</span>
            <input type="text" name="title" value="<?= e($old['title'] ?? '') ?>" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;" required>
        </label>

        <label style="display:grid;gap:8px;">
            <span>Requested Period</span>
            <input type="text" name="requested_for_period" value="<?= e($old['requested_for_period'] ?? '') ?>" placeholder="Example: FY 2026-27 / Apr 2026" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
        </label>

        <label style="display:grid;gap:8px;">
            <span>Description</span>
            <textarea name="description" rows="5" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;resize:vertical;"><?= e($old['description'] ?? '') ?></textarea>
        </label>

        <label style="display:grid;gap:8px;">
            <span>Supporting Documents</span>
            <input type="file" name="documents[]" multiple style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;">
        </label>
        </div>

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="button">Submit PSO</button>
            <a href="<?= e(url('/client-portal/pso')) ?>" class="button button-secondary">Back</a>
        </div>
    </form>
</section>

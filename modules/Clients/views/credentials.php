<?php
$old = is_array($old ?? null) ? $old : [];
$customCredentials = [];
foreach ($credentials as $portalCode => $credential) {
    if (str_starts_with((string) $portalCode, 'CUSTOM_PORTAL_')) {
        $customCredentials[] = $credential;
    }
}

$customRows = [];
if (isset($old['custom_portal_label']) && is_array($old['custom_portal_label'])) {
    $totalRows = count($old['custom_portal_label']);
    for ($index = 0; $index < $totalRows; $index++) {
        $customRows[] = [
            'portal_code' => $old['custom_portal_code'][$index] ?? '',
            'portal_label' => $old['custom_portal_label'][$index] ?? '',
            'user_identifier' => $old['custom_portal_user_identifier'][$index] ?? '',
            'has_password' => !empty($old['custom_portal_password'][$index]) ? 1 : 0,
        ];
    }
} else {
    $customRows = $customCredentials;
}

if ($customRows === []) {
    $customRows[] = [
        'portal_code' => '',
        'portal_label' => '',
        'user_identifier' => '',
        'has_password' => 0,
    ];
}
?>
<section class="panel">
    <div class="toolbar">
        <div>
            <div class="eyebrow">Portal Access</div>
            <h3 style="margin:0 0 6px;">Portal Credentials</h3>
            <div class="subtle"><?= e($client['legal_name']) ?> | PAN: <?= e($client['pan']) ?></div>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <a href="<?= e(url('/service-orders/create?client_id=' . $client['id'])) ?>" class="button">Open Service Order</a>
            <a href="<?= e(url('/clients/show?id=' . $client['id'])) ?>" class="button button-secondary">Back to Client</a>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="data-card" style="margin-bottom:18px;">
        <div class="eyebrow">Next Step</div>
        <div class="subtle">Capture portal login IDs and passwords after client onboarding. Leave password blank if an existing stored password should remain unchanged.</div>
    </div>

    <form method="post" action="<?= e(url('/clients/credentials')) ?>" style="display:grid;gap:18px;">
        <?= \App\Core\Csrf::inputField() ?>
        <input type="hidden" name="id" value="<?= e((string) $client['id']) ?>">

        <?php foreach ($portalDefinitions as $portalCode => $portal): ?>
            <?php $saved = $credentials[$portalCode] ?? []; ?>
            <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
                <h4 style="margin-top:0;"><?= e($portal['label']) ?></h4>
                <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));">
                    <label style="display:grid;gap:8px;">
                        <span>User ID</span>
                        <input type="text" name="<?= e($portalCode) ?>_user_identifier" value="<?= e((string) ($old[$portalCode . '_user_identifier'] ?? $saved['user_identifier'] ?? '')) ?>" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
                    </label>
                    <label style="display:grid;gap:8px;">
                        <span>Password</span>
                        <input type="password" name="<?= e($portalCode) ?>_password" value="" placeholder="<?= (int) ($saved['has_password'] ?? 0) === 1 ? 'Saved securely. Enter only to change.' : 'Enter password' ?>" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
                    </label>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
                <h4 style="margin:0;">New Portals</h4>
                <button type="button" class="button button-secondary" id="add-custom-portal">Add New Portal</button>
            </div>
            <div id="custom-portal-list" style="display:grid;gap:14px;margin-top:16px;">
                <?php foreach ($customRows as $saved): ?>
                    <div class="custom-portal-row" style="padding:14px;border:1px solid #d8e1eb;border-radius:12px;background:#fff;">
                        <input type="hidden" name="custom_portal_code[]" value="<?= e((string) ($saved['portal_code'] ?? '')) ?>">
                        <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));">
                            <label style="display:grid;gap:8px;">
                                <span>Portal Name</span>
                                <input type="text" name="custom_portal_label[]" value="<?= e((string) ($saved['portal_label'] ?? '')) ?>" placeholder="Example: UDYAM / ICEGATE / Profession Tax" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
                            </label>
                            <label style="display:grid;gap:8px;">
                                <span>User ID</span>
                                <input type="text" name="custom_portal_user_identifier[]" value="<?= e((string) ($saved['user_identifier'] ?? '')) ?>" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
                            </label>
                            <label style="display:grid;gap:8px;">
                                <span>Password</span>
                                <input type="password" name="custom_portal_password[]" value="" placeholder="<?= (int) ($saved['has_password'] ?? 0) === 1 ? 'Saved securely. Enter only to change.' : 'Enter password' ?>" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <template id="custom-portal-template">
            <div class="custom-portal-row" style="padding:14px;border:1px solid #d8e1eb;border-radius:12px;background:#fff;">
                <input type="hidden" name="custom_portal_code[]" value="">
                <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));">
                    <label style="display:grid;gap:8px;">
                        <span>Portal Name</span>
                        <input type="text" name="custom_portal_label[]" value="" placeholder="Example: UDYAM / ICEGATE / Profession Tax" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
                    </label>
                    <label style="display:grid;gap:8px;">
                        <span>User ID</span>
                        <input type="text" name="custom_portal_user_identifier[]" value="" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
                    </label>
                    <label style="display:grid;gap:8px;">
                        <span>Password</span>
                        <input type="password" name="custom_portal_password[]" value="" placeholder="Enter password" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
                    </label>
                </div>
            </div>
        </template>

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="button">Save Portal Credentials</button>
            <a href="<?= e(url('/service-orders/create?client_id=' . $client['id'])) ?>" class="button button-secondary">Open Service Order</a>
            <a href="<?= e(url('/clients/show?id=' . $client['id'])) ?>" class="button button-secondary">Skip for Now</a>
        </div>
    </form>
</section>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const addButton = document.getElementById('add-custom-portal');
        const container = document.getElementById('custom-portal-list');
        const template = document.getElementById('custom-portal-template');

        if (!addButton || !container || !template) {
            return;
        }

        addButton.addEventListener('click', function () {
            container.appendChild(template.content.cloneNode(true));
        });
    });
</script>

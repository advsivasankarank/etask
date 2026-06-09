<section class="panel">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <div>
            <div class="eyebrow">Client Profile</div>
            <h3 style="margin:0 0 6px;"><?= e($client['legal_name']) ?></h3>
            <div class="subtle">PAN: <?= e($client['pan']) ?> | Status: <?= (int) $client['is_active'] === 1 ? 'Active' : 'Archived' ?></div>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <a href="<?= e(url('/clients/edit?id=' . $client['id'])) ?>" class="button">Edit</a>
            <a href="<?= e(url('/clients/credentials?id=' . $client['id'])) ?>" class="button button-secondary">Portal Credentials</a>
            <a href="<?= e(url('/clients')) ?>" class="button button-secondary">Back</a>
        </div>
    </div>

    <div class="grid">
        <div class="metric">
            <strong>Tax Identity</strong>
            <div style="margin-top:8px;">PAN: <?= e($client['pan'] ?: '-') ?></div>
            <div style="margin-top:4px;">GSTIN: <?= e($client['gstin'] ?: '-') ?></div>
            <div style="margin-top:4px;">TAN: <?= e($client['tan'] ?: '-') ?></div>
            <div style="margin-top:4px;">Aadhaar: <?= e(!empty($client['aadhaar_last4']) ? 'XXXXXXXX' . $client['aadhaar_last4'] : '-') ?></div>
        </div>
        <div class="metric">
            <strong>Communication</strong>
            <div style="margin-top:8px;">Email: <?= e($client['email'] ?: '-') ?></div>
            <div style="margin-top:4px;">Mobile: <?= e($client['mobile'] ?: '-') ?></div>
            <div style="margin-top:4px;">Landline: <?= e($client['landline'] ?: '-') ?></div>
        </div>
        <div class="metric">
            <strong>CRM Ownership</strong>
            <div style="margin-top:8px;"><?= e($client['assigned_crm_name'] ?: '-') ?></div>
        </div>
        <div class="metric">
            <strong>Primary Contact</strong>
            <div style="margin-top:8px;"><?= e($contact['contact_name'] ?? '-') ?></div>
            <div style="margin-top:4px;"><?= e($contact['email'] ?? '-') ?></div>
            <div style="margin-top:4px;"><?= e($contact['mobile'] ?? '-') ?></div>
        </div>
    </div>

    <div class="panel" style="box-shadow:none;margin-top:18px;background:linear-gradient(180deg,#fff,#f6faf7);">
        <h4 style="margin-top:0;">Address</h4>
        <p><?= e(trim(($client['address_line1'] ?? '') . ' ' . ($client['address_line2'] ?? ''))) ?></p>
        <p><?= e(trim(($client['city'] ?? '') . ' ' . ($client['state_name'] ?? '') . ' ' . ($client['postal_code'] ?? ''))) ?></p>
    </div>

    <div class="panel" style="box-shadow:none;margin-top:18px;background:linear-gradient(180deg,#fff,#f6faf7);">
        <h4 style="margin-top:0;">Identity Documents</h4>
        <?php if (($identityDocuments ?? []) === []): ?>
            <p class="subtle" style="color:#62748a;">PAN and Aadhaar images have not been uploaded yet.</p>
        <?php else: ?>
            <div style="display:grid;gap:10px;">
                <?php foreach ($identityDocuments as $document): ?>
                    <div style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;background:#fff;">
                        <div><strong><?= e($document['document_category']) ?></strong></div>
                        <div style="margin-top:4px;"><?= e($document['document_name']) ?></div>
                        <div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap;">
                            <a href="<?= e(url('/documents/show?id=' . $document['id'])) ?>" class="button button-secondary">Open Document</a>
                            <a href="<?= e(url('/documents/' . $document['id'] . '/download')) ?>" class="button button-secondary">Download Document</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="panel" style="box-shadow:none;margin-top:18px;background:linear-gradient(180deg,#fff,#f6faf7);">
        <h4 style="margin-top:0;">Portal Credentials</h4>
        <?php if (($portalCredentials ?? []) === []): ?>
            <p class="subtle" style="color:#62748a;">Portal credentials are not captured yet.</p>
        <?php else: ?>
            <div style="display:grid;gap:10px;">
                <?php foreach ($portalCredentials as $credential): ?>
                    <div style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;background:#fff;">
                        <div><strong><?= e($credential['portal_label'] ?: ($portalDefinitions[$credential['portal_code']]['label'] ?? $credential['portal_code'])) ?></strong></div>
                        <div style="margin-top:4px;">User ID: <?= e($credential['user_identifier'] ?: '-') ?></div>
                        <div style="margin-top:4px;">Password: <?= (int) $credential['has_password'] === 1 ? 'Stored securely' : 'Not saved' ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="panel" style="box-shadow:none;margin-top:18px;background:linear-gradient(180deg,#fff,#f6faf7);">
        <h4 style="margin-top:0;">Linked Service Orders</h4>
        <?php if (($serviceOrders ?? []) === []): ?>
            <p class="subtle" style="color:#62748a;">No Service Orders are linked to this client yet.</p>
        <?php else: ?>
            <div style="display:grid;gap:10px;">
                <?php foreach ($serviceOrders as $serviceOrder): ?>
                    <div style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;background:#fff;">
                        <div><strong>SO No:</strong> <?= e($serviceOrder['so_no']) ?></div>
                        <div style="margin-top:4px;"><strong>Title:</strong> <?= e($serviceOrder['title']) ?></div>
                        <div style="margin-top:4px;"><strong>Service Type:</strong> <?= e($serviceOrder['service_type_name']) ?></div>
                        <div style="margin-top:4px;"><strong>Stage:</strong> <?= e($serviceOrder['current_stage_code']) ?></div>
                        <div style="margin-top:8px;">
                            <a href="<?= e(url('/service-orders/show?id=' . $serviceOrder['id'])) ?>" class="button button-secondary">Open Service Order</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ((int) $client['is_active'] === 1): ?>
        <div class="panel" style="box-shadow:none;margin-top:18px;background:linear-gradient(180deg,#fff,#f6faf7);">
            <h4 style="margin-top:0;">Archive Client</h4>
            <form method="post" action="<?= e(url('/clients/archive')) ?>" style="display:grid;gap:10px;">
                <?= \App\Core\Csrf::inputField() ?>
                <input type="hidden" name="id" value="<?= e((string) $client['id']) ?>">
                <textarea name="archive_reason" rows="4" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;resize:vertical;" placeholder="Archive reason" required></textarea>
                <button type="submit" class="button" style="background:#b42318;">Archive Client</button>
            </form>
        </div>
    <?php endif; ?>
</section>

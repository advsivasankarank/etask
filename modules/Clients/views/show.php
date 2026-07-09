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
            <div class="subtle">
                <span class="chip <?= (int) $client['is_active'] === 1 ? '' : 'chip-strong' ?>"><?= (int) $client['is_active'] === 1 ? 'Active' : 'Archived' ?></span>
                PAN: <?= e($client['pan']) ?>
            </div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <?php if (\App\Core\Auth::can('clients.edit')): ?>
                <a href="<?= e(url('/clients/edit?id=' . $client['id'])) ?>" class="button">Edit Client</a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('clients.credentials.manage')): ?>
                <a href="<?= e(url('/clients/credentials?id=' . $client['id'])) ?>" class="button button-secondary">Portal Credentials</a>
            <?php endif; ?>
            <?php if (\App\Core\Auth::can('service_orders.create')): ?>
                <a href="<?= e(url('/service-orders/create?client_id=' . $client['id'])) ?>" class="button button-secondary">+ Create Service Order</a>
            <?php endif; ?>
            <a href="<?= e(url('/clients')) ?>" class="button button-secondary">Back</a>
        </div>
    </div>

    <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));">
        <div class="metric">
            <div class="eyebrow">Tax Identity</div>
            <div style="margin-top:8px;font-size:0.92rem;">
                <div><strong>PAN:</strong> <?= e($client['pan'] ?: '-') ?></div>
                <div style="margin-top:4px;"><strong>GSTIN:</strong> <?= e($client['gstin'] ?: '-') ?></div>
                <div style="margin-top:4px;"><strong>TAN:</strong> <?= e($client['tan'] ?: '-') ?></div>
                <div style="margin-top:4px;"><strong>Aadhaar:</strong> <?= e(!empty($client['aadhaar_last4']) ? 'XXXXXXXX' . $client['aadhaar_last4'] : '-') ?></div>
            </div>
        </div>
        <div class="metric">
            <div class="eyebrow">Communication</div>
            <div style="margin-top:8px;font-size:0.92rem;">
                <div><strong>Email:</strong> <?= e($client['email'] ?: '-') ?></div>
                <div style="margin-top:4px;"><strong>Mobile:</strong> <?= e($client['mobile'] ?: '-') ?></div>
                <div style="margin-top:4px;"><strong>Landline:</strong> <?= e($client['landline'] ?: '-') ?></div>
            </div>
        </div>
        <div class="metric">
            <div class="eyebrow">Primary Contact</div>
            <div style="margin-top:8px;font-size:0.92rem;">
                <div><strong><?= e($contact['contact_name'] ?? '-') ?></strong></div>
                <div style="margin-top:4px;"><?= e($contact['email'] ?? '-') ?></div>
                <div style="margin-top:4px;"><?= e($contact['mobile'] ?? '-') ?></div>
            </div>
        </div>
        <div class="metric">
            <div class="eyebrow">CRM Ownership</div>
            <div style="margin-top:8px;font-size:0.92rem;">
                <div><strong><?= e($client['assigned_crm_name'] ?: 'Unassigned') ?></strong></div>
            </div>
        </div>
    </div>

    <div class="panel" style="box-shadow:none;margin-top:18px;background:linear-gradient(180deg,#fff,#f6faf7);">
        <h4 style="margin-top:0;">Address</h4>
        <?php $address = trim(($client['address_line1'] ?? '') . ' ' . ($client['address_line2'] ?? '')); ?>
        <?php $cityState = trim(($client['city'] ?? '') . ' ' . ($client['state_name'] ?? '') . ' ' . ($client['postal_code'] ?? '')); ?>
        <?php if ($address !== '' || $cityState !== ''): ?>
            <p style="margin:0;"><?= e($address) ?></p>
            <p style="margin:4px 0 0;"><?= e($cityState) ?></p>
        <?php else: ?>
            <p class="subtle" style="color:#62748a;">No address captured yet.</p>
        <?php endif; ?>
    </div>

    <div class="panel" style="box-shadow:none;margin-top:18px;background:linear-gradient(180deg,#fff,#f6faf7);">
        <h4 style="margin-top:0;">Identity Documents</h4>
        <?php if (($identityDocuments ?? []) === []): ?>
            <p class="subtle" style="color:#62748a;">PAN and Aadhaar images have not been uploaded yet.</p>
        <?php else: ?>
            <div style="display:grid;gap:10px;">
                <?php foreach ($identityDocuments as $document): ?>
                    <div style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;background:#fff;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                        <div>
                            <strong><?= e($document['document_category']) ?></strong>
                            <div style="margin-top:4px;font-size:0.85rem;color:#64748b;"><?= e($document['document_name']) ?></div>
                        </div>
                        <div style="display:flex;gap:8px;">
                            <a href="<?= e(url('/documents/show?id=' . $document['id'])) ?>" class="button button-secondary" style="padding:6px 12px;font-size:0.82rem;">Open</a>
                            <a href="<?= e(url('/documents/' . $document['id'] . '/download')) ?>" class="button button-secondary" style="padding:6px 12px;font-size:0.82rem;">Download</a>
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
                        <div style="margin-top:4px;font-size:0.85rem;">User ID: <?= e($credential['user_identifier'] ?: '-') ?></div>
                        <div style="margin-top:4px;font-size:0.85rem;">Password: <?= (int) $credential['has_password'] === 1 ? 'Stored securely' : 'Not saved' ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="panel" style="box-shadow:none;margin-top:18px;background:linear-gradient(180deg,#fff,#f6faf7);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <h4 style="margin:0;">Linked Service Orders</h4>
            <?php if (\App\Core\Auth::can('service_orders.view')): ?>
                <a href="<?= e(url('/service-orders?client_id=' . $client['id'])) ?>" class="button button-secondary" style="padding:6px 12px;font-size:0.82rem;">View All</a>
            <?php endif; ?>
        </div>
        <?php if (($serviceOrders ?? []) === []): ?>
            <p class="subtle" style="color:#62748a;">No Service Orders are linked to this client yet.</p>
        <?php else: ?>
            <div style="display:grid;gap:10px;">
                <?php foreach ($serviceOrders as $serviceOrder): ?>
                    <div style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;background:#fff;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                        <div>
                            <div><strong><?= e($serviceOrder['so_no']) ?></strong> — <?= e($serviceOrder['title']) ?></div>
                            <div style="margin-top:4px;font-size:0.85rem;color:#64748b;">
                                <?= e($serviceOrder['service_type_name']) ?> | Stage: <?= e($serviceOrder['current_stage_code']) ?>
                            </div>
                        </div>
                        <a href="<?= e(url('/service-orders/show?id=' . $serviceOrder['id'])) ?>" class="button button-secondary" style="padding:6px 12px;font-size:0.82rem;">Open</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ((int) $client['is_active'] === 1 && \App\Core\Auth::can('clients.archive')): ?>
        <div class="panel" style="box-shadow:none;margin-top:18px;background:linear-gradient(180deg,#fff,#f6faf7);">
            <h4 style="margin-top:0;">Archive Client</h4>
            <form method="post" action="<?= e(url('/clients/archive')) ?>" style="display:grid;gap:10px;">
                <?= \App\Core\Csrf::inputField() ?>
                <input type="hidden" name="id" value="<?= e((string) $client['id']) ?>">
                <textarea name="archive_reason" rows="3" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;resize:vertical;" placeholder="Archive reason" required></textarea>
                <button type="submit" class="button" style="background:#b42318;">Archive Client</button>
            </form>
        </div>
    <?php endif; ?>
</section>

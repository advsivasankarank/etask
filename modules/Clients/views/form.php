<?php
$old = is_array($old ?? null) ? $old : [];
$clientData = $client ?? [];
$contactData = $contact ?? [];
$publicRegistration = !empty($publicRegistration);
$isPublicMode = ($mode ?? '') === 'public_register';
$value = static function (string $key, array $primary, array $secondary = []): string {
    return (string) ($primary[$key] ?? $secondary[$key] ?? '');
};
?>
<section class="panel">
    <div class="toolbar">
        <div>
            <div class="eyebrow"><?= $publicRegistration ? 'Client Registration' : 'Client Module' ?></div>
            <h3 style="margin:0 0 6px;"><?= $mode === 'edit' ? 'Edit Client' : ($publicRegistration ? 'Register for Portal Access' : 'Create New Client') ?></h3>
            <div class="subtle"><?= $publicRegistration ? 'Create your client record and portal login in one step.' : 'Capture tax identity, contacts, ownership, and onboarding documents.' ?></div>
        </div>
        <a href="<?= e($publicRegistration ? url('/login?audience=portal') : url('/clients')) ?>" class="button button-secondary"><?= $publicRegistration ? 'Portal Login' : 'Back to Clients' ?></a>
    </div>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= e($mode === 'edit' ? url('/clients/update') : ($publicRegistration ? url('/register-client') : url('/clients'))) ?>" enctype="multipart/form-data" style="display:grid;gap:18px;">
        <?= \App\Core\Csrf::inputField() ?>
        <?php if ($mode === 'edit'): ?>
            <input type="hidden" name="id" value="<?= e((string) $clientData['id']) ?>">
        <?php endif; ?>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <div class="eyebrow">Basic Details</div>
            <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
            <label style="display:grid;gap:8px;">
                <span>Client Type</span>
                <select name="client_type">
                    <?php foreach (['INDIVIDUAL','PROPRIETORSHIP','PARTNERSHIP','LLP','PRIVATE_LIMITED','PUBLIC_LIMITED','TRUST','SOCIETY','OTHER'] as $type): ?>
                        <option value="<?= e($type) ?>" <?= $value('client_type', $old, $clientData) === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label style="display:grid;gap:8px;">
                <span>Legal Name *</span>
                <input type="text" name="legal_name" value="<?= e($value('legal_name', $old, $clientData)) ?>" required>
            </label>

            <label style="display:grid;gap:8px;">
                <span>Trade Name</span>
                <input type="text" name="trade_name" value="<?= e($value('trade_name', $old, $clientData)) ?>">
            </label>

            <?php if (!$publicRegistration): ?>
                <label style="display:grid;gap:8px;">
                    <span>Assigned CRM</span>
                    <select name="assigned_crm_id">
                        <option value="">Select CRM</option>
                        <?php foreach ($crmUsers as $crm): ?>
                            <option value="<?= e((string) $crm['id']) ?>" <?= $value('assigned_crm_id', $old, $clientData) === (string) $crm['id'] ? 'selected' : '' ?>>
                                <?= e($crm['full_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            <?php endif; ?>
        </div>
        </div>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <div class="eyebrow">Tax / Registration Details</div>
            <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
            <label style="display:grid;gap:8px;">
                <span>PAN *</span>
                <input type="text" name="pan" value="<?= e($value('pan', $old, $clientData)) ?>" style="text-transform:uppercase;" required>
            </label>

            <label style="display:grid;gap:8px;">
                <span>GSTIN</span>
                <input type="text" name="gstin" value="<?= e($value('gstin', $old, $clientData)) ?>" style="text-transform:uppercase;">
            </label>

            <label style="display:grid;gap:8px;">
                <span>TAN</span>
                <input type="text" name="tan" value="<?= e($value('tan', $old, $clientData)) ?>" style="text-transform:uppercase;">
            </label>

            <label style="display:grid;gap:8px;">
                <span>Aadhaar Number</span>
                <input type="text" name="aadhaar_no" value="<?= e((string) ($old['aadhaar_no'] ?? '')) ?>" maxlength="12" placeholder="<?= !empty($clientData['aadhaar_last4']) ? 'Stored securely ending ' . $clientData['aadhaar_last4'] : '' ?>">
            </label>
        </div>
        </div>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <div class="eyebrow">Contact Details</div>
            <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
            <label style="display:grid;gap:8px;">
                <span>Email</span>
                <input type="email" name="email" value="<?= e($value('email', $old, $clientData)) ?>">
            </label>

            <label style="display:grid;gap:8px;">
                <span>Mobile</span>
                <input type="text" name="mobile" value="<?= e($value('mobile', $old, $clientData)) ?>">
            </label>

            <label style="display:grid;gap:8px;">
                <span>Alternate Mobile</span>
                <input type="text" name="alternate_mobile" value="<?= e($value('alternate_mobile', $old, $clientData)) ?>">
            </label>

            <label style="display:grid;gap:8px;">
                <span>Landline</span>
                <input type="text" name="landline" value="<?= e($value('landline', $old, $clientData)) ?>">
            </label>
        </div>
        </div>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <div class="eyebrow">Primary Contact</div>
            <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
            <label style="display:grid;gap:8px;">
                <span>Contact Name</span>
                <input type="text" name="contact_name" value="<?= e($value('contact_name', $old, $contactData)) ?>">
            </label>
            <label style="display:grid;gap:8px;">
                <span>Designation</span>
                <input type="text" name="designation" value="<?= e($value('designation', $old, $contactData)) ?>">
            </label>
            <label style="display:grid;gap:8px;">
                <span>Contact Email</span>
                <input type="email" name="contact_email" value="<?= e($value('contact_email', $old, ['contact_email' => $contactData['email'] ?? ''])) ?>">
            </label>
            <label style="display:grid;gap:8px;">
                <span>Contact Mobile</span>
                <input type="text" name="contact_mobile" value="<?= e($value('contact_mobile', $old, ['contact_mobile' => $contactData['mobile'] ?? ''])) ?>">
            </label>
        </div>
        </div>

        <?php if ($publicRegistration): ?>
            <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
                <div class="eyebrow">Portal Access</div>
                <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
                    <label style="display:grid;gap:8px;">
                        <span>Username Basis</span>
                        <select name="username_basis" required>
                            <?php foreach (['PAN', 'TAN', 'AADHAAR'] as $basis): ?>
                                <option value="<?= e($basis) ?>" <?= $value('username_basis', $old, ['username_basis' => 'PAN']) === $basis ? 'selected' : '' ?>><?= e($basis) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color:#64748b;">Your portal username will be assigned from the selected PAN, TAN, or Aadhaar.</small>
                    </label>

                    <label style="display:grid;gap:8px;">
                        <span>Password *</span>
                        <input type="password" name="password" required>
                    </label>

                    <label style="display:grid;gap:8px;">
                        <span>Confirm Password *</span>
                        <input type="password" name="confirm_password" required>
                    </label>
                </div>
            </div>
        <?php endif; ?>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <div class="eyebrow">Documents</div>
            <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
            <label style="display:grid;gap:8px;">
                <span>PAN Image</span>
                <input type="file" name="pan_document" accept=".jpg,.jpeg,.png,.pdf">
            </label>
            <label style="display:grid;gap:8px;">
                <span>Aadhaar Image</span>
                <input type="file" name="aadhaar_document" accept=".jpg,.jpeg,.png,.pdf">
            </label>
        </div>
        </div>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <div class="eyebrow">Address</div>
            <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
            <label style="display:grid;gap:8px;">
                <span>Address Line 1</span>
                <input type="text" name="address_line1" value="<?= e($value('address_line1', $old, $clientData)) ?>">
            </label>
            <label style="display:grid;gap:8px;">
                <span>Address Line 2</span>
                <input type="text" name="address_line2" value="<?= e($value('address_line2', $old, $clientData)) ?>">
            </label>
            <label style="display:grid;gap:8px;">
                <span>City</span>
                <input type="text" name="city" value="<?= e($value('city', $old, $clientData)) ?>">
            </label>
            <label style="display:grid;gap:8px;">
                <span>State</span>
                <input type="text" name="state_name" value="<?= e($value('state_name', $old, $clientData)) ?>">
            </label>
            <label style="display:grid;gap:8px;">
                <span>Postal Code</span>
                <input type="text" name="postal_code" value="<?= e($value('postal_code', $old, $clientData)) ?>">
            </label>
        </div>
        </div>

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="button"><?= $mode === 'edit' ? 'Update Client' : ($publicRegistration ? 'Create Portal Account' : 'Create Client') ?></button>
            <a href="<?= e($publicRegistration ? url('/login?audience=portal') : url('/clients')) ?>" class="button button-secondary"><?= $publicRegistration ? 'Back to Portal Login' : 'Cancel' ?></a>
        </div>
    </form>
</section>

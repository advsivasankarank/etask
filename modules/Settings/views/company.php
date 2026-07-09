<?php $company = $company ?? []; ?>
<section class="panel">
    <div class="toolbar"><div><div class="eyebrow">Settings Module</div><h3 style="margin:0 0 6px;">Company Settings</h3><div class="subtle">Company profile and contact details.</div></div><a href="<?= e(url('/settings')) ?>" class="button button-secondary">Back</a></div>
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= e(url('/settings/company')) ?>" style="display:grid;gap:18px;">
        <?= \App\Core\Csrf::inputField() ?>
        <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
            <label style="display:grid;gap:8px;"><span>Legal Name</span><input type="text" name="legal_name" value="<?= e($company['legal_name'] ?? '') ?>"></label>
            <label style="display:grid;gap:8px;"><span>Display Name</span><input type="text" name="display_name" value="<?= e($company['display_name'] ?? '') ?>"></label>
            <label style="display:grid;gap:8px;"><span>PAN</span><input type="text" name="pan" value="<?= e($company['pan'] ?? '') ?>"></label>
            <label style="display:grid;gap:8px;"><span>GSTIN</span><input type="text" name="gstin" value="<?= e($company['gstin'] ?? '') ?>"></label>
            <label style="display:grid;gap:8px;"><span>TAN</span><input type="text" name="tan" value="<?= e($company['tan'] ?? '') ?>"></label>
            <label style="display:grid;gap:8px;"><span>Email</span><input type="email" name="email" value="<?= e($company['email'] ?? '') ?>"></label>
            <label style="display:grid;gap:8px;"><span>Mobile</span><input type="text" name="mobile" value="<?= e($company['mobile'] ?? '') ?>"></label>
            <label style="display:grid;gap:8px;"><span>Phone</span><input type="text" name="phone" value="<?= e($company['phone'] ?? '') ?>"></label>
        </div>
        <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
            <label style="display:grid;gap:8px;"><span>Address Line 1</span><input type="text" name="address_line1" value="<?= e($company['address_line1'] ?? '') ?>"></label>
            <label style="display:grid;gap:8px;"><span>Address Line 2</span><input type="text" name="address_line2" value="<?= e($company['address_line2'] ?? '') ?>"></label>
            <label style="display:grid;gap:8px;"><span>City</span><input type="text" name="city" value="<?= e($company['city'] ?? '') ?>"></label>
            <label style="display:grid;gap:8px;"><span>State</span><input type="text" name="state_name" value="<?= e($company['state_name'] ?? '') ?>"></label>
            <label style="display:grid;gap:8px;"><span>Postal Code</span><input type="text" name="postal_code" value="<?= e($company['postal_code'] ?? '') ?>"></label>
        </div>
        <div style="display:flex;gap:12px;"><button type="submit" class="button">Update Company</button><a href="<?= e(url('/settings')) ?>" class="button button-secondary">Cancel</a></div>
    </form>
</section>

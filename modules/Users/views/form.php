<?php
$old = is_array($old ?? null) ? $old : [];
$userData = $user ?? [];
$selectedRoleIds = $selectedRoleIds ?? [];
$value = static function (string $key, array $old, array $userData): string {
    return (string) ($old[$key] ?? $userData[$key] ?? '');
};
?>
<section class="panel">
    <div class="toolbar">
        <div>
            <div class="eyebrow">User Access</div>
            <h3 style="margin:0 0 6px;"><?= $mode === 'edit' ? 'Edit User' : 'Create User' ?></h3>
            <div class="subtle"><?= $userType === 'PORTAL' ? 'Client portal user' : 'Internal user' ?></div>
        </div>
        <a href="<?= e(url('/users')) ?>" class="button button-secondary">Back to Users</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= e($mode === 'edit' ? url('/users/update') : url('/users')) ?>" style="display:grid;gap:18px;">
        <?= \App\Core\Csrf::inputField() ?>
        <input type="hidden" name="user_type" value="<?= e($userType) ?>">
        <?php if ($mode === 'edit'): ?>
            <input type="hidden" name="id" value="<?= e((string) $userData['id']) ?>">
        <?php endif; ?>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <div class="eyebrow">Profile</div>
            <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));">
            <?php if ($userType === 'INTERNAL'): ?>
                <label style="display:grid;gap:8px;">
                    <span>Employee Code</span>
                    <input type="text" name="employee_code" value="<?= e($value('employee_code', $old, $userData)) ?>" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
                </label>
            <?php endif; ?>

            <label style="display:grid;gap:8px;">
                <span>Username</span>
                <input type="text" name="username" value="<?= e($value('username', $old, $userData)) ?>" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;" required>
            </label>

            <label style="display:grid;gap:8px;">
                <span>Full Name</span>
                <input type="text" name="full_name" value="<?= e($value('full_name', $old, $userData)) ?>" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;" required>
            </label>

            <label style="display:grid;gap:8px;">
                <span>Email</span>
                <input type="email" name="email" value="<?= e($value('email', $old, $userData)) ?>" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;" required>
            </label>

            <label style="display:grid;gap:8px;">
                <span>Mobile</span>
                <input type="text" name="mobile" value="<?= e($value('mobile', $old, $userData)) ?>" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
            </label>

            <?php if ($mode === 'create'): ?>
                <label style="display:grid;gap:8px;">
                    <span>Password</span>
                    <input type="password" name="password" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;" required>
                </label>
            <?php endif; ?>

            <label style="display:grid;gap:8px;">
                <span>Role</span>
                <select name="role_ids[]" required>
                    <option value="">Select role</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= e((string) $role['id']) ?>" <?= in_array((int) $role['id'], array_map('intval', (array) ($old['role_ids'] ?? $selectedRoleIds)), true) ? 'selected' : '' ?>><?= e($role['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        </div>

        <?php if ($userType === 'PORTAL'): ?>
            <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
                <div class="eyebrow">Portal Mapping</div>
                <label style="display:grid;gap:8px;">
                <span>Client Contact</span>
                <select name="client_contact_id" required>
                    <option value="">Select client contact</option>
                    <?php foreach ($clientContacts as $contact): ?>
                        <?php
                        $isCurrent = (string) ($contact['id'] ?? '') === (string) ($userData['client_contact_id'] ?? '');
                        $isLinkedElsewhere = !empty($contact['user_id']) && !$isCurrent;
                        if ($isLinkedElsewhere) {
                            continue;
                        }
                        ?>
                        <option value="<?= e((string) $contact['id']) ?>" <?= $value('client_contact_id', $old, $userData) === (string) $contact['id'] ? 'selected' : '' ?>>
                            <?= e($contact['client_name']) ?> / <?= e($contact['contact_name']) ?><?= !empty($contact['email']) ? ' / ' . e($contact['email']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                </label>
            </div>
        <?php endif; ?>

        <?php if ($mode === 'edit'): ?>
            <label style="display:flex;align-items:center;gap:10px;">
                <input type="checkbox" name="must_change_password" value="1" <?= !empty($old) ? (!empty($old['must_change_password']) ? 'checked' : '') : ((int) ($userData['must_change_password'] ?? 0) === 1 ? 'checked' : '') ?>>
                <span>Force password change on next login</span>
            </label>
        <?php endif; ?>

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="button"><?= $mode === 'edit' ? 'Update User' : 'Create User' ?></button>
            <a href="<?= e(url('/users')) ?>" class="button button-secondary">Cancel</a>
        </div>
    </form>
</section>

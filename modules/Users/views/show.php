<?php $roleLabels = array_map(static fn (array $role): string => (string) $role['label'], $userRecord['roles'] ?? []); ?>
<section class="panel">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <div>
            <div class="eyebrow">User Profile</div>
            <h3 style="margin:0 0 6px;"><?= e($userRecord['full_name']) ?></h3>
            <div class="subtle"><?= e($userRecord['username']) ?> | <?= e($userType) ?> user</div>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <a href="<?= e(url('/users/edit?id=' . $userRecord['id'])) ?>" class="button">Edit</a>
            <a href="<?= e(url('/users')) ?>" class="button button-secondary">Back</a>
        </div>
    </div>

    <div class="grid">
        <div class="metric">
            <strong>Identity</strong>
            <div style="margin-top:8px;">Username: <?= e($userRecord['username']) ?></div>
            <div style="margin-top:4px;">Email: <?= e($userRecord['email']) ?></div>
            <div style="margin-top:4px;">Mobile: <?= e($userRecord['mobile'] ?: '-') ?></div>
        </div>
        <div class="metric">
            <strong>Roles</strong>
            <div style="margin-top:8px;"><?= e($roleLabels !== [] ? implode(', ', $roleLabels) : '-') ?></div>
            <div style="margin-top:4px;color:#62748a;">Must change password: <?= (int) $userRecord['must_change_password'] === 1 ? 'Yes' : 'No' ?></div>
        </div>
        <div class="metric">
            <strong>Status</strong>
            <div style="margin-top:8px;"><?= (int) $userRecord['is_active'] === 1 ? 'Active' : 'Archived' ?></div>
            <div style="margin-top:4px;color:#62748a;">Last login: <?= e($userRecord['last_login_at'] ?: '-') ?></div>
        </div>
        <div class="metric">
            <strong>Portal Link</strong>
            <div style="margin-top:8px;"><?= e($userRecord['client_name'] ?: '-') ?></div>
            <div style="margin-top:4px;color:#62748a;"><?= e($userRecord['contact_name'] ?: '-') ?></div>
        </div>
    </div>

    <div class="grid" style="margin-top:18px;">
        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <h4 style="margin-top:0;">Reset Password</h4>
            <form method="post" action="<?= e(url('/users/reset-password')) ?>" style="display:grid;gap:10px;">
                <?= \App\Core\Csrf::inputField() ?>
                <input type="hidden" name="id" value="<?= e((string) $userRecord['id']) ?>">
                <input type="password" name="new_password" placeholder="New password" style="padding:12px;border:1px solid #d8e1eb;border-radius:12px;" required>
                <button type="submit" class="button">Reset Password</button>
            </form>
        </div>

        <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
            <h4 style="margin-top:0;">User Status</h4>
            <?php if ((int) $userRecord['is_active'] === 1): ?>
                <form method="post" action="<?= e(url('/users/archive')) ?>" style="display:grid;gap:10px;">
                    <?= \App\Core\Csrf::inputField() ?>
                    <input type="hidden" name="id" value="<?= e((string) $userRecord['id']) ?>">
                    <button type="submit" class="button" style="background:#b42318;">Archive User</button>
                </form>
            <?php else: ?>
                <form method="post" action="<?= e(url('/users/activate')) ?>" style="display:grid;gap:10px;">
                    <?= \App\Core\Csrf::inputField() ?>
                    <input type="hidden" name="id" value="<?= e((string) $userRecord['id']) ?>">
                    <button type="submit" class="button">Activate User</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>

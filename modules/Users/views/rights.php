<?php $roleLabels = array_map(static fn (array $role): string => (string) $role['label'], $rightsUser['roles'] ?? []); ?>
<section class="panel">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <div>
            <div class="eyebrow">Rights Control</div>
            <h3 style="margin:0 0 6px;"><?= e($rightsUser['full_name']) ?></h3>
            <div class="subtle"><?= e($rightsUser['username']) ?> | Roles: <?= e($roleLabels === [] ? '-' : implode(', ', $roleLabels)) ?></div>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <a href="<?= e(url('/users/show?id=' . $rightsUser['id'])) ?>" class="button button-secondary">Back to User</a>
        </div>
    </div>

    <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin-bottom:18px;">
        <div class="eyebrow">Control Model</div>
        <div class="subtle">Inherited rights come from roles and stay enabled automatically. Use the live checkboxes below only to grant extra rights for this user.</div>
    </div>

    <form method="post" action="<?= e(url('/users/rights')) ?>" style="display:grid;gap:18px;">
        <?= \App\Core\Csrf::inputField() ?>
        <input type="hidden" name="id" value="<?= e((string) $rightsUser['id']) ?>">

        <?php foreach ($rightsGroups as $moduleCode => $permissions): ?>
            <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);">
                <div class="toolbar" style="margin-bottom:12px;">
                    <div>
                        <div class="eyebrow"><?= e(str_replace('_', ' ', $moduleCode)) ?></div>
                        <h4 style="margin:0;"><?= e(ucwords(strtolower(str_replace('_', ' ', $moduleCode)))) ?></h4>
                    </div>
                </div>
                <div class="grid" style="grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:14px;">
                    <?php foreach ($permissions as $permission): ?>
                        <label style="display:grid;gap:8px;padding:16px;border:1px solid #d8e1eb;border-radius:14px;background:#fff;">
                            <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                                <span style="font-weight:700;"><?= e($permission['label']) ?></span>
                                <?php if ($permission['inherited']): ?>
                                    <span class="chip chip-strong">Inherited</span>
                                <?php elseif ($permission['direct']): ?>
                                    <span class="chip chip-strong" style="background:#ecfdf5;color:#0f766e;">Enabled</span>
                                <?php endif; ?>
                            </div>
                            <span style="color:#62748a;font-size:0.92rem;line-height:1.6;"><?= e($permission['description'] ?: $permission['code']) ?></span>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <input
                                    type="checkbox"
                                    name="granted_permissions[]"
                                    value="<?= e($permission['code']) ?>"
                                    <?= $permission['direct'] ? 'checked' : '' ?>
                                    <?= $permission['inherited'] ? 'disabled' : '' ?>
                                >
                                <span style="font-size:0.92rem;color:#334155;"><?= $permission['inherited'] ? 'Enabled through role' : 'Enable extra right for this user' ?></span>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="button">Save Rights</button>
            <a href="<?= e(url('/users/show?id=' . $rightsUser['id'])) ?>" class="button button-secondary">Cancel</a>
        </div>
    </form>
</section>

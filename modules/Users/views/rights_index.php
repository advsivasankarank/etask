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
            <h1 style="margin:0 0 6px;">Roles &amp; Permissions</h1>
            <div class="subtle">Select a user to review inherited role access and assign additional rights.</div>
        </div>
        <a href="<?= e(url('/users')) ?>" class="button button-secondary">Back to Users</a>
    </div>

    <?php if ($users === []): ?>
        <div class="empty-state">
            <strong>No users available</strong>
            <span>Create a user before assigning roles or additional permissions.</span>
            <a href="<?= e(url('/users/create')) ?>" class="button">Create User</a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Roles</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th aria-label="Actions"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <strong><?= e((string) $user['full_name']) ?></strong>
                                <div class="subtle"><?= e((string) $user['username']) ?></div>
                            </td>
                            <td><?= e((string) (($user['role_labels'] ?? '') ?: '-')) ?></td>
                            <td><?= !empty($user['client_contact_id']) ? 'Portal' : 'Internal' ?></td>
                            <td><?= !empty($user['is_active']) ? 'Active' : 'Archived' ?></td>
                            <td>
                                <a class="button button-secondary" href="<?= e(url('/users/rights?id=' . (int) $user['id'])) ?>">Manage Rights</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

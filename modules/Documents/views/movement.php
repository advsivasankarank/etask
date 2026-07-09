<section class="panel">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <div>
            <div class="eyebrow">Document Module</div>
            <h3 style="margin:0 0 6px;">Document Movement Register</h3>
            <div class="subtle">Track document movement, assignment, return, and archive.</div>
        </div>
        <?php if (\App\Core\Auth::can('documents.movement.manage')): ?>
            <a href="<?= e(url('/documents/movement/create')) ?>" class="button">+ Record Movement</a>
        <?php endif; ?>
    </div>

    <form method="get" action="<?= e(url('/documents/movement')) ?>" class="search-bar">
        <select name="status" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
            <option value="">All Status</option>
            <option value="OPEN" <?= ($filters['status'] ?? '') === 'OPEN' ? 'selected' : '' ?>>Open</option>
            <option value="RETURNED" <?= ($filters['status'] ?? '') === 'RETURNED' ? 'selected' : '' ?>>Returned</option>
            <option value="ARCHIVED" <?= ($filters['status'] ?? '') === 'ARCHIVED' ? 'selected' : '' ?>>Archived</option>
        </select>
        <select name="movement_type" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
            <option value="">All Types</option>
            <option value="RECEIVED" <?= ($filters['movement_type'] ?? '') === 'RECEIVED' ? 'selected' : '' ?>>Received</option>
            <option value="ASSIGNED" <?= ($filters['movement_type'] ?? '') === 'ASSIGNED' ? 'selected' : '' ?>>Assigned</option>
            <option value="TRANSFERRED" <?= ($filters['movement_type'] ?? '') === 'TRANSFERRED' ? 'selected' : '' ?>>Transferred</option>
            <option value="USED_FOR_WORK" <?= ($filters['movement_type'] ?? '') === 'USED_FOR_WORK' ? 'selected' : '' ?>>Used for Work</option>
            <option value="RETURNED" <?= ($filters['movement_type'] ?? '') === 'RETURNED' ? 'selected' : '' ?>>Returned</option>
            <option value="ARCHIVED" <?= ($filters['movement_type'] ?? '') === 'ARCHIVED' ? 'selected' : '' ?>>Archived</option>
        </select>
        <button type="submit" class="button">Filter</button>
    </form>

    <?php if (($movements['items'] ?? []) === []): ?>
        <div class="data-card" style="text-align:center;padding:40px;">
            <div class="eyebrow">No Movements</div>
            <p class="subtle" style="margin:8px 0 0;">No document movements recorded.</p>
        </div>
    <?php else: ?>
        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Document</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Purpose</th>
                        <th>Date</th>
                        <th>Return By</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movements['items'] as $mov): ?>
                        <tr>
                            <td><strong><?= e($mov['document_name'] ?: '-') ?></strong></td>
                            <td><?= e($mov['client_name'] ?: '-') ?></td>
                            <td><span class="chip"><?= e($mov['movement_type']) ?></span></td>
                            <td><?= e($mov['from_user_name'] ?: $mov['from_location'] ?: '-') ?></td>
                            <td><?= e($mov['to_user_name'] ?: $mov['to_location'] ?: '-') ?></td>
                            <td><?= e($mov['purpose'] ?: '-') ?></td>
                            <td><?= e($mov['movement_date'] ?: '-') ?></td>
                            <td><?= e($mov['expected_return_date'] ?: '-') ?></td>
                            <td><span class="chip <?= $mov['status'] === 'OPEN' ? '' : 'chip-strong' ?>"><?= e($mov['status']) ?></span></td>
                            <td>
                                <?php if ($mov['status'] === 'OPEN' && \App\Core\Auth::can('documents.movement.manage')): ?>
                                    <form method="post" action="<?= e(url('/documents/movement/return')) ?>" style="display:inline;">
                                        <?= \App\Core\Csrf::inputField() ?>
                                        <input type="hidden" name="movement_id" value="<?= e((string) $mov['id']) ?>">
                                        <button type="submit" class="button button-secondary" style="padding:4px 8px;font-size:0.78rem;">Return</button>
                                    </form>
                                    <form method="post" action="<?= e(url('/documents/movement/archive')) ?>" style="display:inline;">
                                        <?= \App\Core\Csrf::inputField() ?>
                                        <input type="hidden" name="movement_id" value="<?= e((string) $mov['id']) ?>">
                                        <button type="submit" class="button button-secondary" style="padding:4px 8px;font-size:0.78rem;">Archive</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

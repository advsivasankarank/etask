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
            <h3 style="margin:0 0 6px;">Document Requests</h3>
            <div class="subtle">Track document requests from clients and service orders.</div>
        </div>
        <?php if (\App\Core\Auth::can('documents.request')): ?>
            <a href="<?= e(url('/documents/requests/create')) ?>" class="button">+ Create Request</a>
        <?php endif; ?>
    </div>

    <form method="get" action="<?= e(url('/documents/requests')) ?>" class="search-bar">
        <select name="status" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
            <option value="">All Status</option>
            <option value="REQUESTED" <?= ($filters['status'] ?? '') === 'REQUESTED' ? 'selected' : '' ?>>Requested</option>
            <option value="RECEIVED" <?= ($filters['status'] ?? '') === 'RECEIVED' ? 'selected' : '' ?>>Received</option>
            <option value="VERIFIED" <?= ($filters['status'] ?? '') === 'VERIFIED' ? 'selected' : '' ?>>Verified</option>
            <option value="CANCELLED" <?= ($filters['status'] ?? '') === 'CANCELLED' ? 'selected' : '' ?>>Cancelled</option>
        </select>
        <button type="submit" class="button">Filter</button>
    </form>

    <?php if (($requests['items'] ?? []) === []): ?>
        <div class="data-card" style="text-align:center;padding:40px;">
            <div class="eyebrow">No Requests</div>
            <p class="subtle" style="margin:8px 0 0;">No document requests found.</p>
        </div>
    <?php else: ?>
        <div style="overflow:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Client</th>
                        <th>SO</th>
                        <th>Category</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Requested By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests['items'] as $req): ?>
                        <tr>
                            <td><strong><?= e($req['document_title']) ?></strong></td>
                            <td><?= e($req['client_name'] ?: '-') ?></td>
                            <td><?= e($req['so_no'] ?: '-') ?></td>
                            <td><?= e($req['document_category'] ?: '-') ?></td>
                            <td><?= e($req['due_date'] ?: '-') ?></td>
                            <td>
                                <span class="chip <?= $req['status'] === 'REQUESTED' ? '' : ($req['status'] === 'CANCELLED' ? 'chip-strong' : '') ?>"><?= e($req['status']) ?></span>
                            </td>
                            <td><?= e($req['requested_by_name'] ?: '-') ?></td>
                            <td>
                                <?php if ($req['status'] === 'REQUESTED' && \App\Core\Auth::can('documents.upload')): ?>
                                    <form method="post" action="<?= e(url('/documents/requests/mark-received')) ?>" style="display:inline;">
                                        <?= \App\Core\Csrf::inputField() ?>
                                        <input type="hidden" name="request_id" value="<?= e((string) $req['id']) ?>">
                                        <button type="submit" class="button button-secondary" style="padding:4px 8px;font-size:0.78rem;">Mark Received</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($req['status'] === 'REQUESTED' && \App\Core\Auth::can('documents.request')): ?>
                                    <form method="post" action="<?= e(url('/documents/requests/cancel')) ?>" style="display:inline;">
                                        <?= \App\Core\Csrf::inputField() ?>
                                        <input type="hidden" name="request_id" value="<?= e((string) $req['id']) ?>">
                                        <button type="submit" class="button button-secondary" style="padding:4px 8px;font-size:0.78rem;">Cancel</button>
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

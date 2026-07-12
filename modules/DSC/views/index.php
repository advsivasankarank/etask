<section class="panel">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <div>
            <div class="eyebrow">DSC Module</div>
            <h3 style="margin:0 0 6px;">DSC Register</h3>
            <div class="subtle">Manage digital signature certificates, custody, and renewal tracking.</div>
        </div>
        <?php if (\App\Core\Auth::can('dsc.create')): ?>
            <a href="<?= e(url('/dsc/create')) ?>" class="button">+ Add DSC</a>
        <?php endif; ?>
    </div>

    <?php
        $dscSummaryTiles = [
            'total' => ['label' => 'Total', 'severity' => 'neutral'],
            'in_office' => ['label' => 'In Office', 'severity' => 'neutral'],
            'with_staff' => ['label' => 'With Staff', 'severity' => 'neutral'],
            'with_client' => ['label' => 'With Client', 'severity' => 'neutral'],
            'expiring_soon' => ['label' => 'Expiring 30d', 'severity' => 'warning'],
            'expired' => ['label' => 'Expired', 'severity' => 'danger'],
            'archived' => ['label' => 'Archived', 'severity' => 'neutral'],
        ];
    ?>
    <div class="kpi-grid" style="margin-bottom:20px;">
        <?php foreach ($dscSummaryTiles as $key => $tile): ?>
            <div class="kpi-card severity-<?= e($tile['severity']) ?>">
                <div class="kpi-icon"><?= metric_icon_svg($tile['severity']) ?></div>
                <div class="kpi-body">
                    <div class="kpi-label"><?= e($tile['label']) ?></div>
                    <div class="kpi-value"><?= e((string) ($summary[$key] ?? 0)) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <form method="get" action="<?= e(url('/dsc')) ?>" class="search-bar">
        <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Search by holder name, PAN, token serial, or client...">
        <select name="custody_status" style="padding:14px 15px;border:1px solid #d8e1eb;border-radius:12px;">
            <option value="">All Custody</option>
            <option value="WITH_CLIENT" <?= ($filters['custody_status'] ?? '') === 'WITH_CLIENT' ? 'selected' : '' ?>>With Client</option>
            <option value="WITH_OFFICE" <?= ($filters['custody_status'] ?? '') === 'WITH_OFFICE' ? 'selected' : '' ?>>With Office</option>
            <option value="WITH_STAFF" <?= ($filters['custody_status'] ?? '') === 'WITH_STAFF' ? 'selected' : '' ?>>With Staff</option>
            <option value="RETURNED" <?= ($filters['custody_status'] ?? '') === 'RETURNED' ? 'selected' : '' ?>>Returned</option>
        </select>
        <button type="submit" class="button">Search</button>
    </form>

    <?php if (($dscList['items'] ?? []) === []): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🔍</div>
            <div class="empty-state-title">No results</div>
            <div class="empty-state-text">No DSC records found.</div>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead class="table-header">
                    <tr>
                        <th>Holder</th>
                        <th>Client</th>
                        <th>PAN</th>
                        <th>Token</th>
                        <th>Valid To</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="table-body">
                    <?php foreach ($dscList['items'] as $dsc): ?>
                        <?php
                        $isExpired = !empty($dsc['valid_to']) && strtotime($dsc['valid_to']) < time();
                        $isExpiringSoon = !$isExpired && !empty($dsc['valid_to']) && strtotime($dsc['valid_to']) < strtotime('+30 days');
                        $custodyLabel = $isExpired ? 'Expired' : ($isExpiringSoon ? 'Expiring Soon' : (string) $dsc['custody_status']);
                        $custodySeverity = $isExpired ? 'danger' : ($isExpiringSoon ? 'warning' : status_severity((string) $dsc['custody_status']));
                        ?>
                        <tr>
                            <td>
                                <div style="font-weight:700;"><?= e($dsc['holder_name']) ?></div>
                                <div class="subtle" style="font-size:0.78rem;"><?= e($dsc['dsc_type'] ?: 'DSC') ?></div>
                            </td>
                            <td><?= queue_cell_html('client_name', $dsc['client_name'] ?? '') ?></td>
                            <td><?= e($dsc['holder_pan'] ?: '—') ?></td>
                            <td><?= e($dsc['token_serial_no'] ?: '—') ?></td>
                            <td><?= e($dsc['valid_to'] ?: '—') ?></td>
                            <td><span class="badge badge-<?= e($custodySeverity) ?>"><?= e(label_case($custodyLabel)) ?></span></td>
                            <td>
                                <div style="display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end;">
                                    <a href="<?= e(url('/dsc/show?id=' . $dsc['id'])) ?>" class="btn btn-secondary btn-sm">View</a>
                                    <?php if (\App\Core\Auth::can('dsc.edit')): ?>
                                        <a href="<?= e(url('/dsc/edit?id=' . $dsc['id'])) ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php
/** @var array $sourceResults */
/** @var string $sourceKey */
/** @var string $sourceLabel */
?>
<?php if (($sourceResults['items'] ?? []) === []): ?>
    <article class="data-card">
        <div class="eyebrow"><?= e($sourceLabel) ?></div>
        <span class="subtle">No results found.</span>
    </article>
<?php else: ?>
    <article class="data-card">
        <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;">
            <div>
                <div class="eyebrow"><?= e($sourceLabel) ?></div>
                <strong><?= e((string) ($sourceResults['total'] ?? 0)) ?> result(s)</strong>
            </div>
            <?php if (!empty($sourceKey)): ?>
                <a href="<?= e(url('/search/advanced?source=' . urlencode($sourceKey) . '&q=' . urlencode((string) ($query ?? '')))) ?>" class="chip">Open Advanced</a>
            <?php endif; ?>
        </div>

        <div style="display:grid;gap:12px;">
            <?php foreach (($sourceResults['items'] ?? []) as $row): ?>
                <div style="padding:14px 16px;border-radius:16px;background:#fff;border:1px solid rgba(15,76,92,0.08);">
                    <?php if ($sourceKey === 'clients'): ?>
                        <strong><?= e($row['legal_name']) ?></strong><br>
                        <span class="subtle"><?= e($row['client_code']) ?> | PAN <?= e($row['pan'] ?: '-') ?> | GSTIN <?= e($row['gstin'] ?: '-') ?></span><br>
                        <span class="subtle">TAN <?= e($row['tan'] ?: '-') ?> | Mobile <?= e($row['mobile'] ?: '-') ?> | CRM <?= e($row['assigned_crm_name'] ?: '-') ?></span><br>
                        <a href="<?= e(url('/clients/show?id=' . $row['id'])) ?>" class="chip" style="margin-top:8px;">Open Client</a>
                    <?php elseif ($sourceKey === 'service_orders'): ?>
                        <strong><?= e($row['so_no']) ?></strong><br>
                        <span class="subtle"><?= e($row['title']) ?> | <?= e($row['client_name']) ?></span><br>
                        <span class="subtle"><?= e($row['service_type_name']) ?> | <?= e($row['company_name']) ?> | Stage <?= e($row['current_stage_code']) ?></span><br>
                        <span class="subtle">Basis <?= e($row['work_basis'] ?: '-') ?><?php if (!empty($row['assessment_year'])): ?> | AY <?= e($row['assessment_year']) ?><?php endif; ?><?php if (!empty($row['period_label'])): ?> | <?= e($row['period_label']) ?><?php endif; ?></span><br>
                        <a href="<?= e(url('/service-orders/show?id=' . $row['id'])) ?>" class="chip" style="margin-top:8px;">Open Service Order</a>
                    <?php elseif ($sourceKey === 'portal_credentials'): ?>
                        <strong><?= e($row['client_name']) ?></strong><br>
                        <span class="subtle"><?= e($row['portal_label']) ?> | PAN <?= e($row['pan'] ?: '-') ?></span><br>
                        <span class="subtle">User ID: <?= e($row['user_identifier'] ?: '-') ?></span><br>
                        <a href="<?= e(url('/clients/credentials?id=' . $row['client_id'])) ?>" class="chip" style="margin-top:8px;">Open Credentials</a>
                    <?php elseif ($sourceKey === 'invoices'): ?>
                        <strong><?= e($row['invoice_no']) ?></strong><br>
                        <span class="subtle"><?= e($row['client_name']) ?> | <?= e($row['so_no']) ?> | <?= e($row['service_type_name']) ?></span><br>
                        <span class="subtle">Date <?= e($row['invoice_date']) ?> | Net <?= e(number_format((float) $row['net_payable'], 2)) ?> | <?= e($row['payment_status']) ?></span><br>
                        <a href="<?= e(url('/billing/show?service_order_id=' . $row['service_order_id'])) ?>" class="chip" style="margin-top:8px;">Open Billing</a>
                    <?php elseif ($sourceKey === 'receipts'): ?>
                        <strong><?= e($row['receipt_no']) ?></strong><br>
                        <span class="subtle"><?= e($row['client_name']) ?><?php if (!empty($row['so_no'])): ?> | <?= e($row['so_no']) ?><?php endif; ?></span><br>
                        <span class="subtle">Date <?= e($row['receipt_date']) ?> | Amount <?= e(number_format((float) $row['receipt_amount'], 2)) ?> | <?= e($row['payment_mode'] ?: '-') ?></span><br>
                        <?php if ((int) ($row['service_order_id'] ?? 0) > 0): ?>
                            <a href="<?= e(url('/billing/show?service_order_id=' . $row['service_order_id'])) ?>" class="chip" style="margin-top:8px;">Open Billing</a>
                        <?php endif; ?>
                    <?php elseif ($sourceKey === 'consultants'): ?>
                        <strong><?= e($row['consultant_name']) ?></strong><br>
                        <span class="subtle"><?= e($row['email'] ?: '-') ?> | <?= e($row['client_name']) ?> | <?= e($row['so_no']) ?></span><br>
                        <span class="subtle">Status <?= e($row['status']) ?> | Assigned <?= e($row['assigned_at']) ?></span><br>
                        <a href="<?= e(url('/consultants/show?service_order_id=' . $row['service_order_id'])) ?>" class="chip" style="margin-top:8px;">Open Consultant Case</a>
                    <?php elseif ($sourceKey === 'documents'): ?>
                        <strong><?= e($row['document_name']) ?></strong><br>
                        <span class="subtle"><?= e($row['client_name']) ?> | <?= e($row['document_category']) ?> | <?= e($row['linked_module']) ?></span><br>
                        <span class="subtle">File <?= e($row['latest_file_name'] ?: '-') ?> | Uploaded <?= e($row['uploaded_at']) ?></span><br>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">
                            <a href="<?= e(url('/documents/show?id=' . $row['id'])) ?>" class="chip">Open Document</a>
                            <a href="<?= e(url('/documents/' . $row['id'] . '/download')) ?>" class="chip chip-strong">Download</a>
                            <?php if ($row['linked_module'] === 'CLIENT'): ?>
                                <a href="<?= e(url('/clients/show?id=' . $row['client_id'])) ?>" class="chip">Open Client</a>
                            <?php elseif ($row['linked_module'] === 'SO'): ?>
                                <a href="<?= e(url('/service-orders/show?id=' . $row['linked_id'])) ?>" class="chip">Open Service Order</a>
                            <?php elseif ($row['linked_module'] === 'CONSULTANT'): ?>
                                <?php if ((int) ($row['consultant_service_order_id'] ?? 0) > 0): ?>
                                    <a href="<?= e(url('/consultants/show?service_order_id=' . ((int) ($row['consultant_service_order_id'] ?? 0)))) ?>" class="chip">Open Consultant</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </article>
<?php endif; ?>

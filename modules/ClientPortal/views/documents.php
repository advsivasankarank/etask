<?php
$uploadedByYou = is_array($uploadedByYou ?? null) ? $uploadedByYou : [];
$generatedForYou = is_array($generatedForYou ?? null) ? $generatedForYou : [];
$identityDocuments = is_array($identityDocuments ?? null) ? $identityDocuments : [];
$requestedFromYou = is_array($requestedFromYou ?? null) ? $requestedFromYou : [];
$recentNotifications = is_array($recentNotifications ?? null) ? $recentNotifications : [];
$documentCount = (int) ($documentCount ?? 0);

$nextActionTitle = 'Your document workspace is up to date';
$nextActionText = 'All currently visible files are available below. You can open service tracking to review progress or download shared documents securely.';
$nextActionLink = url('/service-orders');
$nextActionLinkLabel = 'Open My Services';

if ($requestedFromYou !== []) {
    $nextActionTitle = 'Documents are pending from your side';
    $nextActionText = count($requestedFromYou) . ' active service(s) are waiting for documents or information from you. Open the relevant service to upload the required files.';
    $nextActionLink = url('/service-orders/show?id=' . $requestedFromYou[0]['id']);
    $nextActionLinkLabel = 'Review Pending Request';
} elseif ($generatedForYou !== []) {
    $nextActionTitle = 'New shared files are available';
    $nextActionText = count($generatedForYou) . ' document(s) shared by E Tax Advisors are available for review or download.';
    $nextActionLink = '#shared-documents';
    $nextActionLinkLabel = 'Open Shared Documents';
}

function portalDocumentContext(array $document): string
{
    $linkedModule = strtoupper((string) ($document['linked_module'] ?? ''));

    if ($linkedModule === 'SO') {
        $serviceName = trim((string) ($document['service_type_name'] ?? 'Service'));
        $reference = trim((string) ($document['so_no'] ?? ''));
        return $reference !== '' ? $serviceName . ' | ' . $reference : $serviceName;
    }

    if ($linkedModule === 'PSO') {
        $title = trim((string) ($document['pso_title'] ?? 'Service Request'));
        $reference = trim((string) ($document['pso_no'] ?? ''));
        return $reference !== '' ? $title . ' | ' . $reference : $title;
    }

    if ($linkedModule === 'CLIENT') {
        return 'Profile Document';
    }

    return 'Client Document';
}

function portalDocumentCategoryLabel(string $category): string
{
    return match (strtoupper($category)) {
        'CLIENT_PAN_CARD_IMAGE' => 'PAN Document',
        'CLIENT_AADHAAR_CARD_IMAGE' => 'Aadhaar Document',
        'PSO_SUPPORTING_DOC' => 'Request Document',
        'COMPLIANCE_PROOF' => 'Compliance Proof',
        'SERVICE_ORDER_DOC' => 'Service Document',
        'WORKING_PAPER' => 'Supporting Document',
        default => ucwords(strtolower(str_replace('_', ' ', $category))),
    };
}
?>
<style>
    .portal-doc-shell { display:grid; gap:20px; }
    .portal-doc-hero {
        display:grid; gap:22px; padding:28px; border-radius:28px; color:#f8fbfc;
        background:linear-gradient(145deg, #0f4c5c 0%, #0f766e 56%, #ea8a2f 100%);
        box-shadow:0 24px 44px rgba(15,76,92,0.18);
    }
    .portal-doc-hero-top { display:flex; justify-content:space-between; align-items:flex-start; gap:18px; flex-wrap:wrap; }
    .portal-doc-kicker {
        font-size:0.78rem; letter-spacing:0.14em; text-transform:uppercase; color:rgba(242,247,249,0.84); font-weight:800;
    }
    .portal-doc-hero h2 { margin:10px 0 8px; color:#ffffff; font-size:clamp(1.9rem, 3vw, 2.65rem); line-height:1.06; }
    .portal-doc-subtitle { margin:0; max-width:760px; color:rgba(241,248,250,0.88); line-height:1.65; font-size:1rem; }
    .portal-doc-actions { display:flex; gap:10px; flex-wrap:wrap; }
    .portal-doc-summary { display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:14px; }
    .portal-doc-summary-card {
        padding:16px 18px; border-radius:18px; background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.14);
    }
    .portal-doc-summary-label { font-size:0.75rem; letter-spacing:0.12em; text-transform:uppercase; color:rgba(241,247,250,0.76); font-weight:800; }
    .portal-doc-summary-value { margin-top:8px; color:#ffffff; font-size:1.06rem; line-height:1.45; font-weight:700; }
    .portal-doc-next {
        display:grid; grid-template-columns:minmax(0, 1.15fr) auto; gap:16px; align-items:center; padding:18px 20px;
        border-radius:20px; background:rgba(255,255,255,0.14); border:1px solid rgba(255,255,255,0.14);
    }
    .portal-doc-next-label { font-size:0.76rem; letter-spacing:0.12em; text-transform:uppercase; color:rgba(241,247,250,0.76); font-weight:800; }
    .portal-doc-next-title { margin-top:8px; color:#ffffff; font-size:1.08rem; font-weight:800; }
    .portal-doc-next-text { margin-top:6px; color:rgba(243,248,250,0.86); line-height:1.6; }
    .portal-doc-kpis { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:16px; }
    .portal-doc-kpi {
        display:grid; gap:10px; padding:20px 22px; border-radius:22px;
        background:linear-gradient(180deg, #ffffff 0%, #f8fbfc 100%);
        border:1px solid rgba(15,118,110,0.09); box-shadow:0 16px 28px rgba(15,76,92,0.06);
    }
    .portal-doc-kpi-label { font-size:0.76rem; letter-spacing:0.12em; text-transform:uppercase; color:#71838d; font-weight:800; }
    .portal-doc-kpi-value { color:#0f172a; font-size:1.85rem; line-height:1; font-weight:800; }
    .portal-doc-kpi-detail { color:#607b86; font-size:0.94rem; line-height:1.6; }
    .portal-doc-grid { display:grid; grid-template-columns:minmax(0, 1.15fr) minmax(320px, 0.85fr); gap:18px; }
    .portal-panel {
        padding:22px; border-radius:24px; background:#ffffff;
        border:1px solid rgba(15,118,110,0.08); box-shadow:0 16px 34px rgba(15,76,92,0.08);
    }
    .portal-panel-header { display:flex; justify-content:space-between; align-items:flex-start; gap:14px; flex-wrap:wrap; margin-bottom:16px; }
    .portal-panel-title { margin:0; font-size:1.14rem; color:#17313b; }
    .portal-panel-text { margin:6px 0 0; color:#607b86; line-height:1.65; font-size:0.95rem; }
    .portal-stack { display:grid; gap:12px; }
    .portal-doc-card, .portal-request-card, .portal-update-card {
        padding:16px 18px; border-radius:18px; background:linear-gradient(180deg, #ffffff 0%, #f8fbfc 100%);
        border:1px solid rgba(15,118,110,0.08);
    }
    .portal-doc-card strong, .portal-request-card strong, .portal-update-card strong { display:block; color:#17313b; margin-bottom:8px; }
    .portal-muted { color:#62748a; line-height:1.6; }
    .portal-chip {
        display:inline-flex; align-items:center; gap:8px; padding:6px 10px; border-radius:999px;
        background:#eef8fa; color:#0d7987; font-weight:700; font-size:0.82rem;
    }
    .portal-chip.warning { background:#fff7ed; color:#c2410c; }
    .portal-chip.good { background:#f0fdf4; color:#15803d; }
    .portal-link-row { display:flex; gap:8px; flex-wrap:wrap; margin-top:12px; }
    .portal-empty { padding:18px; border-radius:18px; background:#f8fbfc; border:1px dashed rgba(15,118,110,0.18); color:#607b86; }
    .portal-list-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:14px; }
    .portal-help-list { display:grid; gap:10px; }
    @media (max-width: 1100px) {
        .portal-doc-kpis { grid-template-columns:repeat(2, minmax(0, 1fr)); }
        .portal-doc-grid { grid-template-columns:1fr; }
    }
    @media (max-width: 820px) {
        .portal-doc-next { grid-template-columns:1fr; }
    }
    @media (max-width: 640px) {
        .portal-doc-kpis { grid-template-columns:1fr; }
        .portal-doc-hero, .portal-panel { padding:20px; }
        .portal-doc-actions, .portal-link-row { display:grid; grid-template-columns:1fr; }
        .portal-doc-actions .button, .portal-link-row .button { width:100%; justify-content:center; }
    }
</style>

<section class="portal-doc-shell">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <section class="portal-doc-hero">
        <div class="portal-doc-hero-top">
            <div>
                <div class="portal-doc-kicker">Document Centre</div>
                <h2><?= e($client['legal_name']) ?></h2>
                <p class="portal-doc-subtitle">
                    Review documents you uploaded, files shared by E Tax Advisors, and service requests waiting for supporting documents in one secure workspace.
                </p>
            </div>
            <div class="portal-doc-actions">
                <a href="<?= e(url('/client-portal/account')) ?>" class="button button-secondary">Back to Dashboard</a>
                <a href="<?= e(url('/service-orders')) ?>" class="button">Open My Services</a>
                <a href="<?= e(url('/client-portal/support')) ?>" class="button button-secondary">Support</a>
            </div>
        </div>

        <div class="portal-doc-summary">
            <div class="portal-doc-summary-card">
                <div class="portal-doc-summary-label">Secure Files</div>
                <div class="portal-doc-summary-value"><?= e((string) $documentCount) ?> visible document(s)</div>
            </div>
            <div class="portal-doc-summary-card">
                <div class="portal-doc-summary-label">Uploaded By You</div>
                <div class="portal-doc-summary-value"><?= e((string) count($uploadedByYou)) ?> document(s)</div>
            </div>
            <div class="portal-doc-summary-card">
                <div class="portal-doc-summary-label">Shared With You</div>
                <div class="portal-doc-summary-value"><?= e((string) count($generatedForYou)) ?> document(s)</div>
            </div>
            <div class="portal-doc-summary-card">
                <div class="portal-doc-summary-label">Pending Requests</div>
                <div class="portal-doc-summary-value"><?= e((string) count($requestedFromYou)) ?> service(s)</div>
            </div>
        </div>

        <div class="portal-doc-next">
            <div>
                <div class="portal-doc-next-label">Next Action Required</div>
                <div class="portal-doc-next-title"><?= e($nextActionTitle) ?></div>
                <div class="portal-doc-next-text"><?= e($nextActionText) ?></div>
            </div>
            <a href="<?= e($nextActionLink) ?>" class="button"><?= e($nextActionLinkLabel) ?></a>
        </div>
    </section>

    <section class="portal-doc-kpis">
        <article class="portal-doc-kpi">
            <div class="portal-doc-kpi-label">Pending Uploads</div>
            <div class="portal-doc-kpi-value"><?= e((string) count($requestedFromYou)) ?></div>
            <div class="portal-doc-kpi-detail">Services waiting for supporting documents or information from your side.</div>
        </article>
        <article class="portal-doc-kpi">
            <div class="portal-doc-kpi-label">My Uploads</div>
            <div class="portal-doc-kpi-value"><?= e((string) count($uploadedByYou)) ?></div>
            <div class="portal-doc-kpi-detail">Files submitted by you through service requests and service tracking workspaces.</div>
        </article>
        <article class="portal-doc-kpi">
            <div class="portal-doc-kpi-label">Shared Files</div>
            <div class="portal-doc-kpi-value"><?= e((string) count($generatedForYou)) ?></div>
            <div class="portal-doc-kpi-detail">Documents prepared or shared by the E Tax Advisors team for your review.</div>
        </article>
        <article class="portal-doc-kpi">
            <div class="portal-doc-kpi-label">Identity Records</div>
            <div class="portal-doc-kpi-value"><?= e((string) count($identityDocuments)) ?></div>
            <div class="portal-doc-kpi-detail">PAN and Aadhaar records stored with secure access controls in your portal profile.</div>
        </article>
    </section>

    <section class="portal-doc-grid">
        <div class="portal-panel" id="requested-documents">
            <div class="portal-panel-header">
                <div>
                    <h3 class="portal-panel-title">Requested From You</h3>
                    <p class="portal-panel-text">Service workspaces currently waiting for documents, information, or confirmations from your side.</p>
                </div>
            </div>

            <?php if ($requestedFromYou === []): ?>
                <div class="portal-empty">No active service is currently waiting for documents from you.</div>
            <?php else: ?>
                <div class="portal-stack">
                    <?php foreach ($requestedFromYou as $item): ?>
                        <article class="portal-request-card">
                            <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;">
                                <div>
                                    <strong><?= e($item['service_type_name']) ?></strong>
                                    <div class="portal-muted"><?= e($item['so_no']) ?><?= !empty($item['period_label']) ? ' | ' . e($item['period_label']) : '' ?></div>
                                </div>
                                <span class="portal-chip warning">Action Required</span>
                            </div>
                            <div class="portal-muted" style="margin-top:8px;">
                                Supporting documents are still required before this service can move forward.
                            </div>
                            <div class="portal-muted">
                                Expected completion: <?= e($item['sla_due_at'] ?: '-') ?>
                            </div>
                            <div class="portal-link-row">
                                <a href="<?= e(url('/service-orders/show?id=' . $item['id'])) ?>" class="button">Open Service</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="portal-panel">
            <div class="portal-panel-header">
                <div>
                    <h3 class="portal-panel-title">Document Trust & Help</h3>
                    <p class="portal-panel-text">A quick reference for how documents are shared and where to go when action is needed.</p>
                </div>
            </div>

            <div class="portal-help-list">
                <div class="portal-doc-card">
                    <strong>Secure Access</strong>
                    <div class="portal-muted">Every file in this workspace opens through secure document access links with permission checks and activity logging.</div>
                </div>
                <div class="portal-doc-card">
                    <strong>Where To Upload</strong>
                    <div class="portal-muted">If a service needs additional files, open that service workspace and use the document upload option provided there.</div>
                </div>
                <div class="portal-doc-card">
                    <strong>Identity Records</strong>
                    <div class="portal-muted">Your PAN and Aadhaar records remain protected in your profile and are shown here only through secured access.</div>
                </div>
            </div>

            <?php if ($recentNotifications !== []): ?>
                <div class="portal-panel-header" style="margin-top:18px;">
                    <div>
                        <h3 class="portal-panel-title">Recent Document Updates</h3>
                        <p class="portal-panel-text">Recent reminders or notifications related to documents and pending submissions.</p>
                    </div>
                </div>
                <div class="portal-stack">
                    <?php foreach ($recentNotifications as $notification): ?>
                        <article class="portal-update-card">
                            <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;">
                                <strong><?= e($notification['subject'] ?: 'Document update') ?></strong>
                                <span class="portal-chip"><?= e(strtoupper((string) ($notification['delivery_status'] ?? 'NEW'))) ?></span>
                            </div>
                            <div class="portal-muted"><?= e($notification['message']) ?></div>
                            <div class="portal-muted"><?= e($notification['created_at'] ?: '-') ?></div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="portal-panel">
        <div class="portal-panel-header">
            <div>
                <h3 class="portal-panel-title">Uploaded By You</h3>
                <p class="portal-panel-text">Documents submitted by you across service requests and active services.</p>
            </div>
        </div>

        <?php if ($uploadedByYou === []): ?>
            <div class="portal-empty">No client-uploaded documents are visible in the portal yet.</div>
        <?php else: ?>
            <div class="portal-list-grid">
                <?php foreach ($uploadedByYou as $document): ?>
                    <article class="portal-doc-card">
                        <strong><?= e($document['document_name']) ?></strong>
                        <div class="portal-muted"><?= e(portalDocumentContext($document)) ?></div>
                        <div class="portal-muted">Category: <?= e(portalDocumentCategoryLabel((string) $document['document_category'])) ?></div>
                        <div class="portal-muted">Uploaded: <?= e($document['uploaded_at'] ?: '-') ?></div>
                        <div class="portal-link-row">
                            <a href="<?= e(url('/documents/show?id=' . $document['id'])) ?>" class="button button-secondary">View</a>
                            <a href="<?= e(url('/documents/' . $document['id'] . '/download')) ?>" class="button button-secondary">Download</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="portal-panel" id="shared-documents">
        <div class="portal-panel-header">
            <div>
                <h3 class="portal-panel-title">Generated For You</h3>
                <p class="portal-panel-text">Documents prepared, shared, or made available by the E Tax Advisors team.</p>
            </div>
        </div>

        <?php if ($generatedForYou === []): ?>
            <div class="portal-empty">No shared service documents are available yet.</div>
        <?php else: ?>
            <div class="portal-list-grid">
                <?php foreach ($generatedForYou as $document): ?>
                    <article class="portal-doc-card">
                        <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;">
                            <strong><?= e($document['document_name']) ?></strong>
                            <span class="portal-chip good">Shared</span>
                        </div>
                        <div class="portal-muted"><?= e(portalDocumentContext($document)) ?></div>
                        <div class="portal-muted">Category: <?= e(portalDocumentCategoryLabel((string) $document['document_category'])) ?></div>
                        <div class="portal-muted">Shared: <?= e($document['uploaded_at'] ?: '-') ?></div>
                        <div class="portal-link-row">
                            <a href="<?= e(url('/documents/show?id=' . $document['id'])) ?>" class="button button-secondary">View</a>
                            <a href="<?= e(url('/documents/' . $document['id'] . '/download')) ?>" class="button button-secondary">Download</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="portal-panel">
        <div class="portal-panel-header">
            <div>
                <h3 class="portal-panel-title">Identity Documents</h3>
                <p class="portal-panel-text">Protected profile records available through the same secure document access flow.</p>
            </div>
        </div>

        <?php if ($identityDocuments === []): ?>
            <div class="portal-empty">No identity documents are available in this workspace right now.</div>
        <?php else: ?>
            <div class="portal-list-grid">
                <?php foreach ($identityDocuments as $document): ?>
                    <article class="portal-doc-card">
                        <strong><?= e($document['document_name']) ?></strong>
                        <div class="portal-muted"><?= e(portalDocumentCategoryLabel((string) $document['document_category'])) ?></div>
                        <div class="portal-muted">Uploaded: <?= e($document['uploaded_at'] ?: '-') ?></div>
                        <div class="portal-link-row">
                            <a href="<?= e(url('/documents/show?id=' . $document['id'])) ?>" class="button button-secondary">View</a>
                            <a href="<?= e(url('/documents/' . $document['id'] . '/download')) ?>" class="button button-secondary">Download</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</section>

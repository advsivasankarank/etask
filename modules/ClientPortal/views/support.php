<?php
$supportActions = is_array($supportActions ?? null) ? $supportActions : [];
$faqCategories = is_array($faqCategories ?? null) ? $faqCategories : [];
$supportContact = is_array($supportContact ?? null) ? $supportContact : [];
?>
<style>
    .portal-support-shell { display:grid; gap:20px; }
    .portal-support-hero {
        display:grid; gap:22px; padding:28px; border-radius:28px; color:#f8fbfc;
        background:linear-gradient(145deg, #0f4c5c 0%, #0f766e 56%, #ea8a2f 100%);
        box-shadow:0 24px 44px rgba(15,76,92,0.18);
    }
    .portal-support-hero-top { display:flex; justify-content:space-between; align-items:flex-start; gap:18px; flex-wrap:wrap; }
    .portal-support-kicker {
        font-size:0.78rem; letter-spacing:0.14em; text-transform:uppercase; color:rgba(242,247,249,0.84); font-weight:800;
    }
    .portal-support-hero h2 { margin:10px 0 8px; color:#ffffff; font-size:clamp(1.9rem, 3vw, 2.65rem); line-height:1.06; }
    .portal-support-subtitle { margin:0; max-width:760px; color:rgba(241,248,250,0.88); line-height:1.65; font-size:1rem; }
    .portal-support-actions { display:flex; gap:10px; flex-wrap:wrap; }
    .portal-support-summary { display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:14px; }
    .portal-support-summary-card {
        padding:16px 18px; border-radius:18px; background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.14);
    }
    .portal-support-summary-label { font-size:0.75rem; letter-spacing:0.12em; text-transform:uppercase; color:rgba(241,247,250,0.76); font-weight:800; }
    .portal-support-summary-value { margin-top:8px; color:#ffffff; font-size:1.06rem; line-height:1.45; font-weight:700; }
    .portal-support-grid { display:grid; grid-template-columns:minmax(0, 1.1fr) minmax(320px, 0.9fr); gap:18px; }
    .portal-panel {
        padding:22px; border-radius:24px; background:#ffffff;
        border:1px solid rgba(15,118,110,0.08); box-shadow:0 16px 34px rgba(15,76,92,0.08);
    }
    .portal-panel-header { display:flex; justify-content:space-between; align-items:flex-start; gap:14px; flex-wrap:wrap; margin-bottom:16px; }
    .portal-panel-title { margin:0; font-size:1.14rem; color:#17313b; }
    .portal-panel-text { margin:6px 0 0; color:#607b86; line-height:1.65; font-size:0.95rem; }
    .portal-card-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:14px; }
    .portal-card, .portal-faq-card, .portal-contact-card {
        padding:16px 18px; border-radius:18px; background:linear-gradient(180deg, #ffffff 0%, #f8fbfc 100%);
        border:1px solid rgba(15,118,110,0.08);
    }
    .portal-card strong, .portal-faq-card strong, .portal-contact-card strong { display:block; color:#17313b; margin-bottom:8px; }
    .portal-muted { color:#62748a; line-height:1.6; }
    .portal-stack { display:grid; gap:12px; }
    .portal-trust-note {
        padding:18px; border-radius:18px; background:#f8fbfc; border:1px dashed rgba(15,118,110,0.18); color:#607b86;
    }
    @media (max-width: 980px) {
        .portal-support-grid { grid-template-columns:1fr; }
    }
    @media (max-width: 640px) {
        .portal-support-hero, .portal-panel { padding:20px; }
        .portal-support-actions { display:grid; grid-template-columns:1fr; }
        .portal-support-actions .button { width:100%; justify-content:center; }
    }
</style>

<section class="portal-support-shell">
    <?php if (!empty($success)): ?>
        <div class="flash flash-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div>
    <?php endif; ?>

    <section class="portal-support-hero">
        <div class="portal-support-hero-top">
            <div>
                <div class="portal-support-kicker">Support</div>
                <h2><?= e($client['legal_name']) ?></h2>
                <p class="portal-support-subtitle">
                    Our team is available to assist you. Queries are reviewed during business hours, and service updates will continue to appear in your portal.
                </p>
            </div>
            <div class="portal-support-actions">
                <a href="<?= e(url('/client-portal/account')) ?>" class="button button-secondary">Back to Dashboard</a>
                <a href="<?= e(url('/service-orders')) ?>" class="button button-secondary">Track Services</a>
                <a href="<?= e(url('/client-portal/documents')) ?>" class="button">Open Documents</a>
            </div>
        </div>

        <div class="portal-support-summary">
            <div class="portal-support-summary-card">
                <div class="portal-support-summary-label">Relationship Manager</div>
                <div class="portal-support-summary-value"><?= e($client['assigned_crm_name'] ?: 'E Tax Advisors Team') ?></div>
            </div>
            <div class="portal-support-summary-card">
                <div class="portal-support-summary-label">Primary Contact</div>
                <div class="portal-support-summary-value"><?= e($contact['contact_name'] ?? '-') ?></div>
            </div>
            <div class="portal-support-summary-card">
                <div class="portal-support-summary-label">Phone</div>
                <div class="portal-support-summary-value"><?= e($supportContact['phone'] ?? '-') ?></div>
            </div>
            <div class="portal-support-summary-card">
                <div class="portal-support-summary-label">Office Hours</div>
                <div class="portal-support-summary-value"><?= e($supportContact['office_hours'] ?? '-') ?></div>
            </div>
        </div>
    </section>

    <section class="portal-support-grid">
        <div class="portal-panel">
            <div class="portal-panel-header">
                <div>
                    <h3 class="portal-panel-title">Need Help?</h3>
                    <p class="portal-panel-text">Choose the quickest path based on what you need from the portal today.</p>
                </div>
            </div>

            <div class="portal-card-grid">
                <?php foreach ($supportActions as $action): ?>
                    <article class="portal-card">
                        <strong><?= e($action['title']) ?></strong>
                        <div class="portal-muted"><?= e($action['description']) ?></div>
                        <div class="portal-support-actions" style="margin-top:12px;">
                            <a href="<?= e($action['path']) ?>" class="button button-secondary"><?= e($action['label']) ?></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="portal-trust-note" style="margin-top:18px;">
                Service updates will continue to appear in your portal, and secure document sharing remains available through the same protected access flow.
            </div>
        </div>

        <div class="portal-panel">
            <div class="portal-panel-header">
                <div>
                    <h3 class="portal-panel-title">Contact Information</h3>
                    <p class="portal-panel-text">Use these support details when you need help beyond the self-service actions available in the portal.</p>
                </div>
            </div>

            <div class="portal-stack">
                <div class="portal-contact-card">
                    <strong>Phone</strong>
                    <div class="portal-muted"><?= e($supportContact['phone'] ?? '-') ?></div>
                </div>
                <div class="portal-contact-card">
                    <strong>Email</strong>
                    <div class="portal-muted"><?= e($supportContact['email'] ?? '-') ?></div>
                </div>
                <div class="portal-contact-card">
                    <strong>Office Hours</strong>
                    <div class="portal-muted"><?= e($supportContact['office_hours'] ?? '-') ?></div>
                </div>
                <div class="portal-contact-card">
                    <strong>Office</strong>
                    <div class="portal-muted"><?= e($supportContact['office_name'] ?? '-') ?></div>
                </div>
            </div>
        </div>
    </section>

    <section class="portal-panel">
        <div class="portal-panel-header">
            <div>
                <h3 class="portal-panel-title">Frequently Asked Topics</h3>
                <p class="portal-panel-text">A quick guide to where common client actions are handled inside e-Pani.</p>
            </div>
        </div>

        <div class="portal-card-grid">
            <?php foreach ($faqCategories as $faq): ?>
                <article class="portal-faq-card">
                    <strong><?= e($faq['title']) ?></strong>
                    <div class="portal-muted"><?= e($faq['description']) ?></div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</section>

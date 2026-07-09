<section class="panel">
    <div class="toolbar"><div><div class="eyebrow">Settings Module</div><h3 style="margin:0 0 6px;">Document Categories</h3><div class="subtle">Manage document category definitions.</div></div><a href="<?= e(url('/settings')) ?>" class="button button-secondary">Back</a></div>
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>
    <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin-bottom:16px;">
        <h4 style="margin-top:0;">Document Category Reference</h4>
        <p class="subtle">Document categories are defined in the database and used by the Document Module for classification.</p>
    </div>
    <div style="overflow:auto;">
        <table>
            <thead><tr><th>Category</th><th>Module</th><th>Description</th></tr></thead>
            <tbody>
                <tr><td>SERVICE_ORDER_DOC</td><td>Service Orders</td><td>Documents linked to service orders</td></tr>
                <tr><td>WORKING_PAPER</td><td>Workflow</td><td>Working papers for compliance filings</td></tr>
                <tr><td>ACKNOWLEDGEMENT</td><td>Workflow</td><td>Filing acknowledgement / ARN</td></tr>
                <tr><td>COMPLIANCE_PROOF</td><td>Workflow</td><td>Compliance proof documents</td></tr>
                <tr><td>PSO_SUPPORTING_DOC</td><td>Client Portal</td><td>Supporting documents for PSOs</td></tr>
                <tr><td>CLIENT_PAN_CARD_IMAGE</td><td>Clients</td><td>PAN card images</td></tr>
                <tr><td>CLIENT_AADHAAR_CARD_IMAGE</td><td>Clients</td><td>Aadhaar card images</td></tr>
            </tbody>
        </table>
    </div>
</section>

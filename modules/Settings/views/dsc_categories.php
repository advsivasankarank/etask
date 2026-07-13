<section class="panel">
    <div class="toolbar"><div><div class="eyebrow">Settings Reference</div><h3 style="margin:0 0 6px;">DSC Category Reference</h3><div class="subtle">Read-only DSC classifications used by the custody register.</div></div><a href="<?= e(url('/settings')) ?>" class="button button-secondary">Back</a></div>
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>
    <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin-bottom:16px;">
        <h4 style="margin-top:0;">DSC Category Reference</h4>
        <p class="subtle">DSC categories are used for classifying Digital Signature Certificates in the DSC Module.</p>
    </div>
    <div style="overflow:auto;">
        <table>
            <thead><tr><th>Category</th><th>Usage</th></tr></thead>
            <tbody>
                <tr><td>Class 2</td><td>Standard DSC for most e-filing portals</td></tr>
                <tr><td>Class 3</td><td>Higher security DSC for sensitive filings</td></tr>
                <tr><td>DGFT</td><td>Directorate General of Foreign Trade DSC</td></tr>
                <tr><td>Banking</td><td>Banking and financial portal DSC</td></tr>
                <tr><td>Other</td><td>Miscellaneous DSC types</td></tr>
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <div class="toolbar"><div><div class="eyebrow">Settings Module</div><h3 style="margin:0 0 6px;">Role Defaults</h3><div class="subtle">Role default access profiles and configurations.</div></div><a href="<?= e(url('/settings')) ?>" class="button button-secondary">Back</a></div>
    <?php if (!empty($success)): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="flash" style="background:#fef3f2;color:#b42318;border:1px solid #fecdca;"><?= e($error) ?></div><?php endif; ?>

    <div class="panel" style="box-shadow:none;background:linear-gradient(180deg,#fff,#f6faf7);margin-bottom:16px;">
        <h4 style="margin-top:0;">Role Access Summary</h4>
        <p class="subtle">Role permissions are managed through the Users > Roles & Permissions module. This page shows a read-only overview of the role-based access structure.</p>
    </div>

    <div style="overflow:auto;">
        <table>
            <thead><tr><th>Role</th><th>Dashboard</th><th>Clients</th><th>Service Orders</th><th>Documents</th><th>DSC</th><th>Workforce</th><th>Accounts</th><th>Reports</th><th>Settings</th></tr></thead>
            <tbody>
                <tr><td><strong>SUPER_ADMIN</strong></td><td>Full</td><td>Full</td><td>Full</td><td>Full</td><td>Full</td><td>Full</td><td>Full</td><td>Full</td><td>Full</td></tr>
                <tr><td><strong>ADMIN</strong></td><td>Full</td><td>Full</td><td>Full</td><td>Full</td><td>Full</td><td>Full</td><td>Full</td><td>Full</td><td>Full</td></tr>
                <tr><td><strong>CRM</strong></td><td>Yes</td><td>Full</td><td>Full</td><td>Limited</td><td>Limited</td><td>Limited</td><td>View</td><td>Yes</td><td>View</td></tr>
                <tr><td><strong>ASSISTANT_CRM</strong></td><td>Yes</td><td>Limited</td><td>Limited</td><td>Limited</td><td>Limited</td><td>Limited</td><td>No</td><td>Limited</td><td>No</td></tr>
                <tr><td><strong>BACKEND_STAFF</strong></td><td>Yes</td><td>Limited</td><td>Limited</td><td>Limited</td><td>Limited</td><td>Limited</td><td>No</td><td>Limited</td><td>No</td></tr>
                <tr><td><strong>DEO</strong></td><td>Yes</td><td>Limited</td><td>Limited</td><td>Limited</td><td>No</td><td>Limited</td><td>No</td><td>Limited</td><td>No</td></tr>
                <tr><td><strong>ACCOUNTS</strong></td><td>Yes</td><td>No</td><td>Limited</td><td>Limited</td><td>No</td><td>Limited</td><td>Full</td><td>Yes</td><td>No</td></tr>
                <tr><td><strong>CONSULTANT</strong></td><td>Yes</td><td>No</td><td>Limited</td><td>Limited</td><td>No</td><td>Limited</td><td>Limited</td><td>Limited</td><td>No</td></tr>
                <tr><td><strong>CLIENT</strong></td><td>Portal</td><td>Portal</td><td>Portal</td><td>Portal</td><td>No</td><td>No</td><td>No</td><td>No</td><td>No</td></tr>
            </tbody>
        </table>
    </div>
</section>

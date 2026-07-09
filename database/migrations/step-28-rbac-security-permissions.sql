-- Step 28: Phase 1 RBAC & Security Correction Permissions
-- Adds missing/future-ready permissions for Document, Workforce, Settings, Accounts, and DSC modules.
-- Idempotent: safe to run multiple times.

USE etaxadv_etask;

-- =============================================================================
-- A. Document Module Permissions
-- =============================================================================

INSERT INTO permissions (code, module_code, action_code, label, description, is_active, created_at, updated_at)
VALUES
('documents.view', 'DOCUMENTS', 'VIEW', 'View Documents', 'View documents in the document workspace', 1, NOW(), NOW()),
('documents.upload', 'DOCUMENTS', 'UPLOAD', 'Upload Documents', 'Upload documents to the system', 1, NOW(), NOW()),
('documents.request', 'DOCUMENTS', 'REQUEST', 'Request Documents', 'Request documents from clients or staff', 1, NOW(), NOW()),
('documents.verify', 'DOCUMENTS', 'VERIFY', 'Verify Documents', 'Verify uploaded documents for accuracy', 1, NOW(), NOW()),
('documents.replace', 'DOCUMENTS', 'REPLACE', 'Replace Documents', 'Replace existing documents with updated versions', 1, NOW(), NOW()),
('documents.return', 'DOCUMENTS', 'RETURN', 'Return Documents', 'Return documents to clients or previous custody', 1, NOW(), NOW()),
('documents.archive', 'DOCUMENTS', 'ARCHIVE', 'Archive Documents', 'Archive completed or obsolete documents', 1, NOW(), NOW()),
('documents.movement.view', 'DOCUMENTS', 'MOVEMENT_VIEW', 'View Document Movement', 'View document movement and transfer history', 1, NOW(), NOW()),
('documents.movement.manage', 'DOCUMENTS', 'MOVEMENT_MANAGE', 'Manage Document Movement', 'Manage document transfers and movement', 1, NOW(), NOW()),
('documents.access_log.view', 'DOCUMENTS', 'ACCESS_LOG_VIEW', 'View Document Access Logs', 'View document access audit logs', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    label = VALUES(label),
    description = VALUES(description),
    is_active = VALUES(is_active),
    updated_at = NOW();

-- Document permission role assignments
-- SUPER_ADMIN: all document permissions
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'documents.view', 'documents.upload', 'documents.request', 'documents.verify',
    'documents.replace', 'documents.return', 'documents.archive',
    'documents.movement.view', 'documents.movement.manage', 'documents.access_log.view'
)
WHERE r.code = 'SUPER_ADMIN'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- ADMIN: all document permissions
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'documents.view', 'documents.upload', 'documents.request', 'documents.verify',
    'documents.replace', 'documents.return', 'documents.archive',
    'documents.movement.view', 'documents.movement.manage', 'documents.access_log.view'
)
WHERE r.code = 'ADMIN'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- CRM: view, upload, request, verify, replace, movement.view, access_log.view
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'documents.view', 'documents.upload', 'documents.request', 'documents.verify',
    'documents.replace', 'documents.movement.view', 'documents.access_log.view'
)
WHERE r.code = 'CRM'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- ASSISTANT_CRM: view, upload, request, movement.view
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'documents.view', 'documents.upload', 'documents.request', 'documents.movement.view'
)
WHERE r.code = 'ASSISTANT_CRM'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- BACKEND_STAFF: view, upload, verify, replace, movement.view
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'documents.view', 'documents.upload', 'documents.verify',
    'documents.replace', 'documents.movement.view'
)
WHERE r.code = 'BACKEND_STAFF'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- DEO: view, upload
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'documents.view', 'documents.upload'
)
WHERE r.code = 'DEO'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- ACCOUNTS: view, upload, access_log.view
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'documents.view', 'documents.upload', 'documents.access_log.view'
)
WHERE r.code = 'ACCOUNTS'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- CONSULTANT: view, upload (if assigned/shared access)
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'documents.view', 'documents.upload'
)
WHERE r.code = 'CONSULTANT'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- CLIENT: no internal document permissions (portal uses portal.self_access)

-- =============================================================================
-- B. Workforce Module Permissions
-- =============================================================================

INSERT INTO permissions (code, module_code, action_code, label, description, is_active, created_at, updated_at)
VALUES
('workforce.view', 'WORKFORCE', 'VIEW', 'View Workforce Module', 'Access the workforce module dashboard', 1, NOW(), NOW()),
('workforce.staff.view', 'WORKFORCE', 'STAFF_VIEW', 'View Staff Register', 'View staff register and profiles', 1, NOW(), NOW()),
('workforce.staff.manage', 'WORKFORCE', 'STAFF_MANAGE', 'Manage Staff', 'Create, edit, and manage staff accounts', 1, NOW(), NOW()),
('workforce.consultants.view', 'WORKFORCE', 'CONSULTANTS_VIEW', 'View Consultants', 'View consultants in workforce context', 1, NOW(), NOW()),
('workforce.consultants.manage', 'WORKFORCE', 'CONSULTANTS_MANAGE', 'Manage Consultants', 'Manage consultant assignments and details', 1, NOW(), NOW()),
('workforce.attendance.view', 'WORKFORCE', 'ATTENDANCE_VIEW', 'View Attendance', 'View staff attendance records', 1, NOW(), NOW()),
('workforce.attendance.manage', 'WORKFORCE', 'ATTENDANCE_MANAGE', 'Manage Attendance', 'Manage attendance settings and overrides', 1, NOW(), NOW()),
('workforce.daily_reports.view', 'WORKFORCE', 'DAILY_REPORTS_VIEW', 'View Daily Work Reports', 'View daily work reports from staff', 1, NOW(), NOW()),
('workforce.daily_reports.review', 'WORKFORCE', 'DAILY_REPORTS_REVIEW', 'Review Daily Work Reports', 'Review and remark on daily work reports', 1, NOW(), NOW()),
('workforce.productivity.view', 'WORKFORCE', 'PRODUCTIVITY_VIEW', 'View Workforce Productivity', 'View productivity metrics and analytics', 1, NOW(), NOW()),
('workforce.login_activity.view', 'WORKFORCE', 'LOGIN_ACTIVITY_VIEW', 'View Login Activity', 'View staff login activity and sessions', 1, NOW(), NOW()),
('workforce.user_accounts.manage', 'WORKFORCE', 'USER_ACCOUNTS_MANAGE', 'Manage User Accounts', 'Manage user accounts within workforce module', 1, NOW(), NOW()),
('workforce.permissions.manage', 'WORKFORCE', 'PERMISSIONS_MANAGE', 'Manage Permissions', 'Manage role and permission assignments', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    label = VALUES(label),
    description = VALUES(description),
    is_active = VALUES(is_active),
    updated_at = NOW();

-- Workforce permission role assignments
-- SUPER_ADMIN: all workforce permissions
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'workforce.view', 'workforce.staff.view', 'workforce.staff.manage',
    'workforce.consultants.view', 'workforce.consultants.manage',
    'workforce.attendance.view', 'workforce.attendance.manage',
    'workforce.daily_reports.view', 'workforce.daily_reports.review',
    'workforce.productivity.view', 'workforce.login_activity.view',
    'workforce.user_accounts.manage', 'workforce.permissions.manage'
)
WHERE r.code = 'SUPER_ADMIN'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- ADMIN: all workforce permissions except optionally workforce.permissions.manage
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'workforce.view', 'workforce.staff.view', 'workforce.staff.manage',
    'workforce.consultants.view', 'workforce.consultants.manage',
    'workforce.attendance.view', 'workforce.attendance.manage',
    'workforce.daily_reports.view', 'workforce.daily_reports.review',
    'workforce.productivity.view', 'workforce.login_activity.view',
    'workforce.user_accounts.manage'
)
WHERE r.code = 'ADMIN'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- CRM: workforce.view, consultants.view, attendance.view, daily_reports.view, daily_reports.review, productivity.view
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'workforce.view', 'workforce.consultants.view',
    'workforce.attendance.view', 'workforce.daily_reports.view',
    'workforce.daily_reports.review', 'workforce.productivity.view'
)
WHERE r.code = 'CRM'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- ASSISTANT_CRM: workforce.view, attendance.view, daily_reports.view
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'workforce.view', 'workforce.attendance.view', 'workforce.daily_reports.view'
)
WHERE r.code = 'ASSISTANT_CRM'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- BACKEND_STAFF: workforce.view, attendance.view, daily_reports.view
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'workforce.view', 'workforce.attendance.view', 'workforce.daily_reports.view'
)
WHERE r.code = 'BACKEND_STAFF'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- DEO: workforce.view, attendance.view, daily_reports.view
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'workforce.view', 'workforce.attendance.view', 'workforce.daily_reports.view'
)
WHERE r.code = 'DEO'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- ACCOUNTS: workforce.view, attendance.view, daily_reports.view, productivity.view
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'workforce.view', 'workforce.attendance.view',
    'workforce.daily_reports.view', 'workforce.productivity.view'
)
WHERE r.code = 'ACCOUNTS'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- CONSULTANT: workforce.view, consultants.view, attendance.view, daily_reports.view
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'workforce.view', 'workforce.consultants.view',
    'workforce.attendance.view', 'workforce.daily_reports.view'
)
WHERE r.code = 'CONSULTANT'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- CLIENT: no workforce permissions

-- =============================================================================
-- C. Settings Module Permissions
-- =============================================================================

INSERT INTO permissions (code, module_code, action_code, label, description, is_active, created_at, updated_at)
VALUES
('settings.view', 'SETTINGS', 'VIEW', 'View Settings', 'Access the settings module', 1, NOW(), NOW()),
('settings.company.manage', 'SETTINGS', 'COMPANY_MANAGE', 'Manage Company Settings', 'Manage company profile and configuration', 1, NOW(), NOW()),
('settings.service_types.manage', 'SETTINGS', 'SERVICE_TYPES_MANAGE', 'Manage Service Types', 'Manage service type definitions', 1, NOW(), NOW()),
('settings.workflow.manage', 'SETTINGS', 'WORKFLOW_MANAGE', 'Manage Workflow Settings', 'Manage workflow definitions and stages', 1, NOW(), NOW()),
('settings.reminder_templates.manage', 'SETTINGS', 'REMINDER_TEMPLATES_MANAGE', 'Manage Reminder Templates', 'Manage reminder templates and escalation rules', 1, NOW(), NOW()),
('settings.numbering.manage', 'SETTINGS', 'NUMBERING_MANAGE', 'Manage Numbering Settings', 'Manage document and reference numbering', 1, NOW(), NOW()),
('settings.document_categories.manage', 'SETTINGS', 'DOCUMENT_CATEGORIES_MANAGE', 'Manage Document Categories', 'Manage document category definitions', 1, NOW(), NOW()),
('settings.dsc_categories.manage', 'SETTINGS', 'DSC_CATEGORIES_MANAGE', 'Manage DSC Categories', 'Manage DSC category definitions', 1, NOW(), NOW()),
('settings.security.manage', 'SETTINGS', 'SECURITY_MANAGE', 'Manage Security Settings', 'Manage security and access control settings', 1, NOW(), NOW()),
('settings.backup.manage', 'SETTINGS', 'BACKUP_MANAGE', 'Manage Backup and Maintenance', 'Manage database backups and system maintenance', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    label = VALUES(label),
    description = VALUES(description),
    is_active = VALUES(is_active),
    updated_at = NOW();

-- Settings permission role assignments
-- SUPER_ADMIN: all settings permissions
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'settings.view', 'settings.company.manage', 'settings.service_types.manage',
    'settings.workflow.manage', 'settings.reminder_templates.manage',
    'settings.numbering.manage', 'settings.document_categories.manage',
    'settings.dsc_categories.manage', 'settings.security.manage', 'settings.backup.manage'
)
WHERE r.code = 'SUPER_ADMIN'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- ADMIN: settings.view, company.manage, service_types.manage, workflow.manage, reminder_templates.manage, numbering.manage, document_categories.manage, dsc_categories.manage
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'settings.view', 'settings.company.manage', 'settings.service_types.manage',
    'settings.workflow.manage', 'settings.reminder_templates.manage',
    'settings.numbering.manage', 'settings.document_categories.manage',
    'settings.dsc_categories.manage'
)
WHERE r.code = 'ADMIN'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- CRM: settings.view only if needed (optional)
-- ASSISTANT_CRM: none
-- BACKEND_STAFF: none
-- DEO: none
-- ACCOUNTS: none
-- CONSULTANT: none
-- CLIENT: none

-- =============================================================================
-- D. Accounts Collection Permissions
-- =============================================================================

INSERT INTO permissions (code, module_code, action_code, label, description, is_active, created_at, updated_at)
VALUES
('accounts.view', 'ACCOUNTS', 'VIEW', 'View Accounts Module', 'Access the accounts module dashboard', 1, NOW(), NOW()),
('accounts.collections.view', 'ACCOUNTS', 'COLLECTIONS_VIEW', 'View Collections', 'View payment collections', 1, NOW(), NOW()),
('accounts.collections.manage', 'ACCOUNTS', 'COLLECTIONS_MANAGE', 'Manage Collections', 'Manage payment collections and reconciliations', 1, NOW(), NOW()),
('accounts.outstanding.view', 'ACCOUNTS', 'OUTSTANDING_VIEW', 'View Outstanding', 'View outstanding payments and dues', 1, NOW(), NOW()),
('accounts.ageing.view', 'ACCOUNTS', 'AGEING_VIEW', 'View Ageing', 'View ageing analysis of receivables', 1, NOW(), NOW()),
('accounts.unbilled.view', 'ACCOUNTS', 'UNBILLED_VIEW', 'View Unbilled Completed Work', 'View completed work not yet billed', 1, NOW(), NOW()),
('accounts.consultant_payables.view', 'ACCOUNTS', 'CONSULTANT_PAYABLES_VIEW', 'View Consultant Payables', 'View outstanding consultant payables', 1, NOW(), NOW()),
('accounts.consultant_payables.manage', 'ACCOUNTS', 'CONSULTANT_PAYABLES_MANAGE', 'Manage Consultant Payables', 'Manage consultant payment processing', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    label = VALUES(label),
    description = VALUES(description),
    is_active = VALUES(is_active),
    updated_at = NOW();

-- Accounts permission role assignments
-- SUPER_ADMIN: all accounts permissions
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'accounts.view', 'accounts.collections.view', 'accounts.collections.manage',
    'accounts.outstanding.view', 'accounts.ageing.view', 'accounts.unbilled.view',
    'accounts.consultant_payables.view', 'accounts.consultant_payables.manage'
)
WHERE r.code = 'SUPER_ADMIN'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- ADMIN: all accounts permissions
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'accounts.view', 'accounts.collections.view', 'accounts.collections.manage',
    'accounts.outstanding.view', 'accounts.ageing.view', 'accounts.unbilled.view',
    'accounts.consultant_payables.view', 'accounts.consultant_payables.manage'
)
WHERE r.code = 'ADMIN'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- ACCOUNTS: all accounts permissions
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'accounts.view', 'accounts.collections.view', 'accounts.collections.manage',
    'accounts.outstanding.view', 'accounts.ageing.view', 'accounts.unbilled.view',
    'accounts.consultant_payables.view', 'accounts.consultant_payables.manage'
)
WHERE r.code = 'ACCOUNTS'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- CRM: accounts.outstanding.view only (for client follow-up)
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code = 'accounts.outstanding.view'
WHERE r.code = 'CRM'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- CONSULTANT: accounts.consultant_payables.view only (if own-scope logic exists)
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code = 'accounts.consultant_payables.view'
WHERE r.code = 'CONSULTANT'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- ASSISTANT_CRM: none
-- BACKEND_STAFF: none
-- DEO: none
-- CLIENT: none

-- =============================================================================
-- E. DSC Module Permissions (Future-Ready Seeds)
-- =============================================================================

INSERT INTO permissions (code, module_code, action_code, label, description, is_active, created_at, updated_at)
VALUES
('dsc.view', 'DSC', 'VIEW', 'View DSC Register', 'View digital signature certificate register', 1, NOW(), NOW()),
('dsc.create', 'DSC', 'CREATE', 'Create DSC', 'Create new DSC records', 1, NOW(), NOW()),
('dsc.edit', 'DSC', 'EDIT', 'Edit DSC', 'Edit DSC details and metadata', 1, NOW(), NOW()),
('dsc.custody.view', 'DSC', 'CUSTODY_VIEW', 'View DSC Custody', 'View DSC custody and holder information', 1, NOW(), NOW()),
('dsc.custody.manage', 'DSC', 'CUSTODY_MANAGE', 'Manage DSC Custody', 'Manage DSC custody transfers and assignments', 1, NOW(), NOW()),
('dsc.movement.view', 'DSC', 'MOVEMENT_VIEW', 'View DSC Movement', 'View DSC movement and transfer history', 1, NOW(), NOW()),
('dsc.movement.manage', 'DSC', 'MOVEMENT_MANAGE', 'Manage DSC Movement', 'Manage DSC physical and digital movement', 1, NOW(), NOW()),
('dsc.usage.view', 'DSC', 'USAGE_VIEW', 'View DSC Usage', 'View DSC usage records', 1, NOW(), NOW()),
('dsc.usage.log', 'DSC', 'USAGE_LOG', 'Log DSC Usage', 'Log DSC usage for signing operations', 1, NOW(), NOW()),
('dsc.renewal.view', 'DSC', 'RENEWAL_VIEW', 'View DSC Renewal', 'View DSC renewal status and history', 1, NOW(), NOW()),
('dsc.renewal.manage', 'DSC', 'RENEWAL_MANAGE', 'Manage DSC Renewal', 'Manage DSC renewal processes', 1, NOW(), NOW()),
('dsc.return.manage', 'DSC', 'RETURN_MANAGE', 'Manage DSC Return', 'Manage DSC return and decommissioning', 1, NOW(), NOW()),
('dsc.reports.view', 'DSC', 'REPORTS_VIEW', 'View DSC Reports', 'View DSC reports and analytics', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    label = VALUES(label),
    description = VALUES(description),
    is_active = VALUES(is_active),
    updated_at = NOW();

-- DSC permission role assignments
-- SUPER_ADMIN: all DSC permissions
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'dsc.view', 'dsc.create', 'dsc.edit',
    'dsc.custody.view', 'dsc.custody.manage',
    'dsc.movement.view', 'dsc.movement.manage',
    'dsc.usage.view', 'dsc.usage.log',
    'dsc.renewal.view', 'dsc.renewal.manage',
    'dsc.return.manage', 'dsc.reports.view'
)
WHERE r.code = 'SUPER_ADMIN'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- ADMIN: all DSC permissions
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'dsc.view', 'dsc.create', 'dsc.edit',
    'dsc.custody.view', 'dsc.custody.manage',
    'dsc.movement.view', 'dsc.movement.manage',
    'dsc.usage.view', 'dsc.usage.log',
    'dsc.renewal.view', 'dsc.renewal.manage',
    'dsc.return.manage', 'dsc.reports.view'
)
WHERE r.code = 'ADMIN'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- CRM: dsc.view, dsc.custody.view, dsc.movement.view, dsc.usage.view, dsc.usage.log, dsc.renewal.view, dsc.reports.view
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'dsc.view', 'dsc.custody.view', 'dsc.movement.view',
    'dsc.usage.view', 'dsc.usage.log', 'dsc.renewal.view', 'dsc.reports.view'
)
WHERE r.code = 'CRM'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- ASSISTANT_CRM: dsc.view, dsc.usage.log, dsc.renewal.view
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'dsc.view', 'dsc.usage.log', 'dsc.renewal.view'
)
WHERE r.code = 'ASSISTANT_CRM'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- BACKEND_STAFF: dsc.view, dsc.usage.log
INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'dsc.view', 'dsc.usage.log'
)
WHERE r.code = 'BACKEND_STAFF'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

-- DEO: none (default)
-- ACCOUNTS: none
-- CONSULTANT: none
-- CLIENT: none

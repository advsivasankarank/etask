USE etaxadv_etask;

ALTER TABLE permissions
    ADD COLUMN IF NOT EXISTS code VARCHAR(120) NULL AFTER id,
    ADD COLUMN IF NOT EXISTS description VARCHAR(255) NULL AFTER label,
    ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER description,
    ADD COLUMN IF NOT EXISTS updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

UPDATE permissions
SET code = LOWER(CONCAT(module_code, '.', action_code))
WHERE code IS NULL OR code = '';

CREATE TABLE IF NOT EXISTS role_permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    is_granted TINYINT(1) NOT NULL DEFAULT 1,
    assigned_by BIGINT UNSIGNED NULL,
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_role_permissions (role_id, permission_id),
    CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id),
    CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id),
    CONSTRAINT fk_role_permissions_assigned_by FOREIGN KEY (assigned_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    is_granted TINYINT(1) NOT NULL DEFAULT 1,
    assigned_by BIGINT UNSIGNED NULL,
    notes VARCHAR(255) NULL,
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_permissions (user_id, permission_id),
    CONSTRAINT fk_user_permissions_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_user_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id),
    CONSTRAINT fk_user_permissions_assigned_by FOREIGN KEY (assigned_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO permissions (code, module_code, action_code, label, description, is_active, created_at, updated_at)
VALUES
('dashboard.admin', 'DASHBOARD', 'ADMIN_HOME', 'Admin Dashboard', 'Access admin dashboard persona', 1, NOW(), NOW()),
('dashboard.crm', 'DASHBOARD', 'CRM_HOME', 'CRM Dashboard', 'Access CRM dashboard persona', 1, NOW(), NOW()),
('dashboard.accounts', 'DASHBOARD', 'ACCOUNTS_HOME', 'Accounts Dashboard', 'Access accounts dashboard persona', 1, NOW(), NOW()),
('dashboard.consultant', 'DASHBOARD', 'CONSULTANT_HOME', 'Consultant Dashboard', 'Access consultant dashboard persona', 1, NOW(), NOW()),
('dashboard.client', 'DASHBOARD', 'CLIENT_HOME', 'Client Dashboard', 'Access client dashboard persona', 1, NOW(), NOW()),
('users.manage.portal', 'USERS', 'MANAGE_PORTAL', 'Manage Portal Users', 'Create and maintain client portal users', 1, NOW(), NOW()),
('users.manage.internal', 'USERS', 'MANAGE_INTERNAL', 'Manage Internal Users', 'Create and maintain internal users', 1, NOW(), NOW()),
('clients.view', 'CLIENTS', 'VIEW', 'View Clients', 'View client register and details', 1, NOW(), NOW()),
('clients.create', 'CLIENTS', 'CREATE', 'Create Clients', 'Create client records', 1, NOW(), NOW()),
('clients.edit', 'CLIENTS', 'EDIT', 'Edit Clients', 'Edit client records', 1, NOW(), NOW()),
('clients.archive', 'CLIENTS', 'ARCHIVE', 'Archive Clients', 'Archive client records', 1, NOW(), NOW()),
('clients.credentials.manage', 'CLIENTS', 'CREDENTIALS_MANAGE', 'Manage Client Credentials', 'Manage client portal credentials', 1, NOW(), NOW()),
('portal.self_access', 'CLIENT_PORTAL', 'SELF_ACCESS', 'Portal Self Access', 'Portal-only self-service access', 1, NOW(), NOW()),
('portal.pso.create', 'CLIENT_PORTAL', 'PSO_CREATE', 'Create PSO', 'Create client pre-service orders', 1, NOW(), NOW()),
('portal.pso.review', 'CLIENT_PORTAL', 'PSO_REVIEW', 'Review PSO', 'Review or recommend client PSOs', 1, NOW(), NOW()),
('portal.pso.approve', 'CLIENT_PORTAL', 'PSO_APPROVE', 'Approve PSO', 'Approve PSOs and create service orders', 1, NOW(), NOW()),
('portal.pso.reject', 'CLIENT_PORTAL', 'PSO_REJECT', 'Reject PSO', 'Reject PSOs', 1, NOW(), NOW()),
('billing.view', 'BILLING', 'VIEW', 'View Billing', 'View billing register and workspaces', 1, NOW(), NOW()),
('billing.disbursements.manage', 'BILLING', 'DISBURSEMENTS_MANAGE', 'Manage Disbursements', 'Create disbursements', 1, NOW(), NOW()),
('billing.invoices.manage', 'BILLING', 'INVOICES_MANAGE', 'Manage Invoices', 'Create invoices', 1, NOW(), NOW()),
('billing.payments.manage', 'BILLING', 'PAYMENTS_MANAGE', 'Manage Payments', 'Record payments and receipts', 1, NOW(), NOW()),
('consultants.view', 'CONSULTANTS', 'VIEW', 'View Consultants Workspace', 'View consultant workspaces', 1, NOW(), NOW()),
('consultants.assign', 'CONSULTANTS', 'ASSIGN', 'Assign Consultants', 'Assign consultants to service orders', 1, NOW(), NOW()),
('consultants.deliverables.upload', 'CONSULTANTS', 'DELIVERABLES_UPLOAD', 'Upload Consultant Deliverables', 'Upload consultant deliverables', 1, NOW(), NOW()),
('consultants.deliverables.review', 'CONSULTANTS', 'DELIVERABLES_REVIEW', 'Review Consultant Deliverables', 'Review consultant deliverables', 1, NOW(), NOW()),
('consultants.bills.create', 'CONSULTANTS', 'BILLS_CREATE', 'Create Consultant Bills', 'Create consultant bills', 1, NOW(), NOW()),
('consultants.bills.review', 'CONSULTANTS', 'BILLS_REVIEW', 'Review Consultant Bills', 'Review consultant bills', 1, NOW(), NOW()),
('consultants.payments.record', 'CONSULTANTS', 'PAYMENTS_RECORD', 'Record Consultant Payments', 'Record consultant payments', 1, NOW(), NOW()),
('service_orders.view', 'SERVICE_ORDERS', 'VIEW', 'View Service Orders', 'View service order register and details', 1, NOW(), NOW()),
('service_orders.create', 'SERVICE_ORDERS', 'CREATE', 'Create Service Orders', 'Create service orders', 1, NOW(), NOW()),
('workflow.advance', 'WORKFLOW', 'ADVANCE', 'Advance Workflow', 'Advance workflow milestones', 1, NOW(), NOW()),
('workflow.payment.record', 'WORKFLOW', 'PAYMENT_RECORD', 'Record Workflow Payment', 'Record workflow tax payment', 1, NOW(), NOW()),
('workflow.acknowledgement.capture', 'WORKFLOW', 'ACK_CAPTURE', 'Capture Acknowledgement', 'Capture filing acknowledgement', 1, NOW(), NOW()),
('workflow.everification.complete', 'WORKFLOW', 'EVERIFICATION_COMPLETE', 'Complete E-Verification', 'Mark e-verification complete', 1, NOW(), NOW()),
('workflow.close.procedural', 'WORKFLOW', 'CLOSE_PROCEDURAL', 'Procedural Closure', 'Complete procedural closure', 1, NOW(), NOW()),
('workflow.close.accounting', 'WORKFLOW', 'CLOSE_ACCOUNTING', 'Accounting Closure', 'Complete accounting closure', 1, NOW(), NOW()),
('workflow.close.final', 'WORKFLOW', 'CLOSE_FINAL', 'Final Closure', 'Complete final closure', 1, NOW(), NOW()),
('workflow.followup.log', 'WORKFLOW', 'FOLLOWUP_LOG', 'Log Follow Up', 'Log workflow follow-up', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    code = VALUES(code),
    label = VALUES(label),
    description = VALUES(description),
    is_active = VALUES(is_active),
    updated_at = NOW();

INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'dashboard.admin','dashboard.crm','dashboard.accounts','dashboard.consultant','dashboard.client',
    'users.manage.portal','users.manage.internal',
    'clients.view','clients.create','clients.edit','clients.archive','clients.credentials.manage',
    'portal.self_access','portal.pso.create','portal.pso.review','portal.pso.approve','portal.pso.reject',
    'billing.view','billing.disbursements.manage','billing.invoices.manage','billing.payments.manage',
    'consultants.view','consultants.assign','consultants.deliverables.upload','consultants.deliverables.review',
    'consultants.bills.create','consultants.bills.review','consultants.payments.record',
    'service_orders.view','service_orders.create',
    'workflow.advance','workflow.payment.record','workflow.acknowledgement.capture',
    'workflow.everification.complete','workflow.close.procedural','workflow.close.accounting',
    'workflow.close.final','workflow.followup.log'
)
WHERE r.code = 'SUPER_ADMIN'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'dashboard.admin','users.manage.portal','users.manage.internal',
    'clients.view','clients.create','clients.edit','clients.archive','clients.credentials.manage',
    'portal.pso.review','portal.pso.approve','portal.pso.reject',
    'billing.view','billing.disbursements.manage','billing.invoices.manage','billing.payments.manage',
    'consultants.view','consultants.assign','consultants.deliverables.upload','consultants.deliverables.review',
    'consultants.bills.create','consultants.bills.review','consultants.payments.record',
    'service_orders.view','service_orders.create',
    'workflow.advance','workflow.payment.record','workflow.acknowledgement.capture',
    'workflow.everification.complete','workflow.close.procedural','workflow.close.accounting',
    'workflow.close.final','workflow.followup.log'
)
WHERE r.code = 'ADMIN'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'dashboard.crm','users.manage.portal',
    'clients.view','clients.create','clients.edit','clients.credentials.manage',
    'portal.pso.review','portal.pso.approve',
    'billing.disbursements.manage',
    'consultants.view','consultants.assign','consultants.deliverables.upload','consultants.deliverables.review','consultants.bills.create',
    'service_orders.view','service_orders.create',
    'workflow.advance','workflow.payment.record','workflow.acknowledgement.capture',
    'workflow.everification.complete','workflow.close.procedural','workflow.followup.log'
)
WHERE r.code = 'CRM'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'dashboard.crm',
    'clients.view',
    'billing.disbursements.manage',
    'service_orders.view','service_orders.create',
    'workflow.advance','workflow.payment.record','workflow.acknowledgement.capture',
    'workflow.everification.complete','workflow.followup.log'
)
WHERE r.code = 'ASSISTANT_CRM'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'consultants.view','consultants.deliverables.review',
    'service_orders.view','service_orders.create',
    'workflow.advance','workflow.payment.record','workflow.acknowledgement.capture',
    'workflow.everification.complete','workflow.close.procedural'
)
WHERE r.code = 'BACKEND_STAFF'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'service_orders.view','service_orders.create',
    'workflow.advance','workflow.payment.record','workflow.acknowledgement.capture'
)
WHERE r.code = 'DEO'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'dashboard.accounts',
    'billing.view','billing.disbursements.manage','billing.invoices.manage','billing.payments.manage',
    'consultants.bills.review','consultants.payments.record',
    'workflow.payment.record','workflow.close.accounting','workflow.close.final'
)
WHERE r.code = 'ACCOUNTS'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'dashboard.consultant',
    'consultants.view','consultants.deliverables.upload','consultants.bills.create'
)
WHERE r.code = 'CONSULTANT'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;

INSERT INTO role_permissions (role_id, permission_id, is_granted, assigned_at, created_at, updated_at)
SELECT r.id, p.id, 1, NOW(), NOW(), NOW()
FROM roles r
INNER JOIN permissions p ON p.code IN (
    'dashboard.client','portal.self_access','portal.pso.create','service_orders.view'
)
WHERE r.code = 'CLIENT'
ON DUPLICATE KEY UPDATE is_granted = VALUES(is_granted), updated_at = CURRENT_TIMESTAMP;
